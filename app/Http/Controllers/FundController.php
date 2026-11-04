<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FundController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $sort = (string) $request->query('sort', 'newest');
        if (! in_array($sort, ['newest', 'deadline'], true)) {
            $sort = 'newest';
        }

        $funds = FundRequest::open()
            ->with('receiver')
            ->withSum(['donations as collected_sum' => fn ($d) => $d->where('status', 'confirmed')], 'amount')
            ->when($q !== '', function ($query) use ($q) {
                $like = '%' . addcslashes($q, '%_\\') . '%';
                $query->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('description', 'like', $like));
            })
            ->when($sort === 'newest', fn ($query) => $query->latest('id'))
            ->when($sort === 'deadline', fn ($query) => $query->orderBy('deadline'))
            ->paginate(9)
            ->withQueryString();

        return view('dana.index', compact('funds', 'q', 'sort'));
    }

    public function show(Request $request, FundRequest $fund)
    {
        $user = $request->user();
        $canPreview = $user && ($user->isAdmin() || $user->id === $fund->receiver_id);

        // Kebutuhan yang belum disetujui hanya boleh dilihat pemilik dan admin
        abort_unless($fund->status === 'published' || $canPreview, 404);

        $fund->load('receiver');
        $collected = $fund->collectedAmount();
        $donorCount = $fund->donations()->where('status', 'confirmed')->count();
        $recent = $fund->donations()->where('status', 'confirmed')->with('donor')->latest()->limit(10)->get();

        return view('dana.show', compact('fund', 'canPreview', 'collected', 'donorCount', 'recent'));
    }

    // Dokumen pendukung bersifat privat: hanya pemilik dan admin
    public function document(Request $request, FundRequest $fund)
    {
        $user = $request->user();
        abort_unless($user->isAdmin() || $user->id === $fund->receiver_id, 403);
        abort_unless(Storage::disk('local')->exists($fund->proof_doc_url), 404);

        return Storage::disk('local')->response($fund->proof_doc_url);
    }
}
