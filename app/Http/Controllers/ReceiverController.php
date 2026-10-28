<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\DonationRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiverController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $counts = $user->donationRequests()
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $active = ($counts['pending'] ?? 0) + ($counts['awaiting_handover'] ?? 0);
        $done = ($counts['received'] ?? 0) + ($counts['completed'] ?? 0);

        return view('dashboard.receiver', compact('user', 'counts', 'active', 'done'));
    }

    public function requests(Request $request)
    {
        $groups = [
            'active' => ['pending', 'awaiting_handover'],
            'done' => ['received', 'completed'],
            'closed' => ['rejected', 'cancelled', 'expired'],
        ];

        $tab = $request->query('tab', 'active');
        if ($tab !== 'all' && ! isset($groups[$tab])) {
            $tab = 'active';
        }

        $requests = $request->user()->donationRequests()
            ->with(['item.category', 'item.donor'])
            ->when($tab !== 'all', fn ($q) => $q->whereIn('status', $groups[$tab]))
            ->latest()
            ->limit(100)
            ->get();

        $all = $request->user()->donationRequests()
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $counts = collect($groups)->map(fn ($statuses) => collect($statuses)->sum(fn ($s) => $all[$s] ?? 0));

        return view('receiver.requests', compact('requests', 'tab', 'counts'));
    }

    public function store(Request $request, Item $item)
    {
        $user = $request->user();

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:500'],
        ], [
            'reason.required' => 'Alasan permintaan wajib diisi.',
            'reason.min' => 'Alasan terlalu singkat, tulis minimal 10 karakter.',
            'reason.max' => 'Alasan maksimal 500 karakter.',
        ]);

        $error = DB::transaction(function () use ($user, $item, $data) {
            // Kunci baris barang agar dua pengajuan serentak tidak saling bentrok
            $locked = Item::whereKey($item->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'published') {
                return 'Barang ini belum atau tidak lagi tersedia.';
            }
            if ($locked->donor_id === $user->id) {
                return 'Anda tidak dapat mengajukan barang milik sendiri.';
            }
            if ($locked->requests()->where('status', 'awaiting_handover')->exists()) {
                return 'Barang ini sedang dalam proses serah terima.';
            }
            if ($locked->requests()->where('receiver_id', $user->id)->active()->exists()) {
                return 'Anda sudah mengajukan barang ini.';
            }
            if ($user->donationRequests()->active()->count() >= DonationRequest::MAX_ACTIVE) {
                return 'Batas ' . DonationRequest::MAX_ACTIVE . ' pengajuan aktif tercapai. Batalkan atau selesaikan pengajuan lain terlebih dahulu.';
            }

            DonationRequest::create([
                'item_id' => $locked->id,
                'receiver_id' => $user->id,
                'reason' => $data['reason'],
                'status' => 'pending',
            ]);

            AppNotification::create([
                'user_id' => $locked->donor_id,
                'message' => 'Ada pengajuan baru untuk "' . $locked->name . '" dari ' . $user->name . '. Buka Pengajuan Masuk untuk menyetujui atau menolak.',
            ]);

            return null;
        });

        if ($error) {
            return back()->withInput()->with('error', $error);
        }

        return redirect()->route('receiver.requests.index')
            ->with('success', 'Pengajuan terkirim. Tunggu keputusan donatur, Anda akan mendapat notifikasi.');
    }

    public function cancel(Request $request, DonationRequest $donationRequest)
    {
        abort_unless($donationRequest->receiver_id === $request->user()->id, 403);
        abort_unless(
            in_array($donationRequest->status, DonationRequest::ACTIVE_STATUSES, true),
            403,
            'Pengajuan ini sudah tidak dapat dibatalkan.'
        );

        $item = $donationRequest->item;

        // Kode konfirmasi (bila sudah dibuat) ikut dibatalkan, barang kembali tampil di katalog
        $donationRequest->update([
            'status' => 'cancelled',
            'confirmation_code' => null,
            'code_expires_at' => null,
        ]);

        AppNotification::create([
            'user_id' => $item->donor_id,
            'message' => $request->user()->name . ' membatalkan pengajuan untuk "' . $item->name . '".',
        ]);

        return back()->with('success', 'Pengajuan dibatalkan.');
    }
}
