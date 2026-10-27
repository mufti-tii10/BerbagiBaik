<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\DonationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonorController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $itemCounts = $user->items()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $requestCounts = $this->ownRequests($user->id)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('dashboard.donor', compact('user', 'itemCounts', 'requestCounts'));
    }

    // ---------- Pengajuan masuk ----------

    public function requests(Request $request)
    {
        $status = $request->query('status', 'pending');
        if (! in_array($status, ['pending', 'awaiting_handover', 'rejected', 'all'], true)) {
            $status = 'pending';
        }

        $requests = $this->ownRequests($request->user()->id)
            ->with(['item.category', 'receiver'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->limit(100)
            ->get();

        $counts = $this->ownRequests($request->user()->id)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('donor.requests', compact('requests', 'status', 'counts'));
    }

    public function approve(Request $request, DonationRequest $donationRequest)
    {
        $item = $this->authorizeRequest($request, $donationRequest);

        abort_unless($donationRequest->status === 'pending', 403, 'Pengajuan ini sudah diproses.');
        abort_unless($item->status === 'published', 403, 'Barang belum dipublikasikan.');
        abort_if($item->requests()->where('status', 'awaiting_handover')->exists(), 403, 'Barang ini sudah disetujui untuk penerima lain.');

        DB::transaction(function () use ($donationRequest, $item) {
            // Kode konfirmasi 6 digit, berlaku 3 hari. Pengirimannya lewat WhatsApp dibuat di Tahap 8.
            do {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            } while (DonationRequest::where('confirmation_code', $code)->where('status', 'awaiting_handover')->exists());

            $donationRequest->update([
                'status' => 'awaiting_handover',
                'confirmation_code' => $code,
                'code_expires_at' => now()->addDays(3),
            ]);

            AppNotification::create([
                'user_id' => $donationRequest->receiver_id,
                'message' => 'Pengajuan Anda untuk "' . $item->name . '" disetujui donatur. Silakan atur serah terima barang.',
            ]);

            // Satu barang hanya untuk satu penerima: pengajuan lain otomatis ditolak
            $others = $item->requests()->where('status', 'pending')->where('id', '!=', $donationRequest->id)->get();
            foreach ($others as $other) {
                $other->update(['status' => 'rejected']);
                AppNotification::create([
                    'user_id' => $other->receiver_id,
                    'message' => 'Barang "' . $item->name . '" sudah diberikan kepada penerima lain.',
                ]);
            }
        });

        return back()->with('success', 'Pengajuan disetujui. Kontak penerima kini tersedia untuk mengatur serah terima.');
    }

    public function reject(Request $request, DonationRequest $donationRequest)
    {
        $item = $this->authorizeRequest($request, $donationRequest);

        abort_unless($donationRequest->status === 'pending', 403, 'Pengajuan ini sudah diproses.');

        $donationRequest->update(['status' => 'rejected']);

        AppNotification::create([
            'user_id' => $donationRequest->receiver_id,
            'message' => 'Pengajuan Anda untuk "' . $item->name . '" ditolak donatur.',
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }

    // ---------- Riwayat ----------

    public function history(Request $request)
    {
        $requests = $this->ownRequests($request->user()->id)
            ->whereIn('status', ['received', 'completed'])
            ->with(['item.category', 'receiver'])
            ->latest('updated_at')
            ->get();

        return view('donor.history', compact('requests'));
    }

    // ---------- Pembantu ----------

    private function ownRequests(int $donorId)
    {
        return DonationRequest::whereHas('item', fn ($q) => $q->where('donor_id', $donorId));
    }

    private function authorizeRequest(Request $request, DonationRequest $donationRequest)
    {
        $item = $donationRequest->item;
        abort_unless($item && $item->donor_id === $request->user()->id, 403);

        return $item;
    }
}
