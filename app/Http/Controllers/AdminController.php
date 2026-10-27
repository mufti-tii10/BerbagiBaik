<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Item;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('dashboard.admin', [
            'pendingItems' => Item::where('status', 'pending')->count(),
            'publishedItems' => Item::where('status', 'published')->count(),
            'donors' => User::where('role', 'donor')->count(),
            'receivers' => User::where('role', 'receiver')->count(),
            'openReports' => Report::where('status', 'open')->count(),
        ]);
    }

    // ---------- Verifikasi barang ----------

    public function items(Request $request)
    {
        $status = $request->query('status', 'pending');
        if (! in_array($status, ['pending', 'published', 'rejected', 'completed', 'all'], true)) {
            $status = 'pending';
        }

        $items = Item::with(['donor', 'category'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->limit(100)
            ->get();

        $counts = Item::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.items', compact('items', 'status', 'counts'));
    }

    public function approveItem(Item $item)
    {
        abort_if($item->status === 'completed', 403, 'Barang yang sudah selesai tidak dapat diubah.');

        $item->update(['status' => 'published', 'admin_note' => null]);
        AppNotification::create(['user_id' => $item->donor_id, 'message' => 'Barang "' . $item->name . '" disetujui dan sudah dipublikasikan.']);

        return back()->with('success', 'Barang "' . $item->name . '" dipublikasikan.');
    }

    public function rejectItem(Request $request, Item $item)
    {
        abort_if($item->status === 'completed', 403, 'Barang yang sudah selesai tidak dapat diubah.');

        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ], [
            'admin_note.required' => 'Alasan penolakan wajib diisi.',
            'admin_note.max' => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        $item->update(['status' => 'rejected', 'admin_note' => $data['admin_note']]);
        AppNotification::create(['user_id' => $item->donor_id, 'message' => 'Barang "' . $item->name . '" ditolak admin. Alasan: ' . $data['admin_note']]);

        return back()->with('success', 'Barang "' . $item->name . '" ditolak.');
    }

    // ---------- Pengguna ----------

    public function users(Request $request)
    {
        $role = $request->query('role', 'all');
        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when(in_array($role, ['donor', 'receiver', 'admin'], true), fn ($query) => $query->where('role', $role))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone_wa', 'like', "%{$q}%");
                });
            })
            ->withCount(['items', 'donationRequests'])
            ->latest()
            ->limit(200)
            ->get();

        return view('admin.users', compact('users', 'role', 'q'));
    }

    public function blockUser(User $user)
    {
        abort_if($user->isAdmin(), 403, 'Akun admin tidak dapat diblokir.');

        $user->is_blocked = true;
        $user->save();

        // Keluarkan paksa dari semua sesi yang sedang aktif
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return back()->with('success', 'Akun ' . $user->name . ' diblokir.');
    }

    public function unblockUser(User $user)
    {
        $user->is_blocked = false;
        $user->save();

        return back()->with('success', 'Blokir akun ' . $user->name . ' dibuka.');
    }

    // ---------- Laporan ----------

    public function reports(Request $request)
    {
        $status = $request->query('status', 'open');
        if (! in_array($status, ['open', 'handled', 'all'], true)) {
            $status = 'open';
        }

        $reports = Report::with(['reporter', 'item.donor'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->limit(100)
            ->get();

        return view('admin.reports', compact('reports', 'status'));
    }

    public function handleReport(Report $report)
    {
        $report->update(['status' => 'handled']);

        return back()->with('success', 'Laporan ditandai selesai.');
    }

    public function takedownReport(Report $report)
    {
        $item = $report->item;
        abort_unless($item, 404, 'Barang sudah tidak ada.');
        abort_if($item->status === 'completed', 403, 'Barang yang sudah selesai tidak dapat diturunkan.');

        $item->update([
            'status' => 'rejected',
            'admin_note' => 'Diturunkan admin berdasarkan laporan: ' . Str::limit($report->reason, 300),
        ]);
        $report->update(['status' => 'handled']);
        AppNotification::create(['user_id' => $item->donor_id, 'message' => 'Barang "' . $item->name . '" diturunkan admin berdasarkan laporan pengguna.']);

        return back()->with('success', 'Barang diturunkan dan laporan ditandai selesai.');
    }
}
