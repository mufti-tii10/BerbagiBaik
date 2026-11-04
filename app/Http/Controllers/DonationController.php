<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Donation;
use App\Models\FundRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DonationController extends Controller
{
    public const MIN_AMOUNT = 10000;
    public const MAX_PENDING = 3;

    // ---------- Donatur ----------

    public function store(Request $request, FundRequest $fund)
    {
        $user = $request->user();

        if ($fund->status !== 'published' || now()->toDateString() > $fund->deadline->toDateString()) {
            return back()->with('error', 'Kebutuhan dana ini tidak lagi menerima donasi.');
        }

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:' . self::MIN_AMOUNT, 'max:100000000'],
            'message' => ['nullable', 'string', 'max:300'],
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'amount.required' => 'Nominal donasi wajib diisi.',
            'amount.integer' => 'Nominal harus berupa angka.',
            'amount.min' => 'Donasi minimal ' . FundRequest::rupiah(self::MIN_AMOUNT) . '.',
            'amount.max' => 'Nominal terlalu besar.',
            'message.max' => 'Pesan maksimal 300 karakter.',
            'proof.required' => 'Bukti transfer wajib diunggah.',
            'proof.image' => 'Bukti transfer harus berupa gambar.',
            'proof.mimes' => 'Format bukti harus JPG, PNG, atau WEBP.',
            'proof.max' => 'Ukuran bukti maksimal 2 MB.',
            'proof.uploaded' => 'Bukti gagal diunggah. Pastikan ukurannya tidak lebih dari 2 MB.',
        ]);

        $pending = $fund->donations()->where('donor_id', $user->id)->where('status', 'pending')->count();
        if ($pending >= self::MAX_PENDING) {
            return back()->withInput()->with('error', 'Anda masih memiliki ' . self::MAX_PENDING . ' donasi yang menunggu konfirmasi penerima untuk kebutuhan ini. Tunggu hingga dikonfirmasi.');
        }

        $path = $request->file('proof')->store('donation-proofs', 'local');

        Donation::create([
            'fund_request_id' => $fund->id,
            'donor_id' => $user->id,
            'amount' => (int) $data['amount'],
            'proof_photo_url' => $path,
            'message' => $data['message'] ?? null,
            'is_anonymous' => $request->boolean('is_anonymous'),
            'status' => 'pending',
        ]);

        AppNotification::create([
            'user_id' => $fund->receiver_id,
            'message' => 'Donasi baru ' . FundRequest::rupiah($data['amount']) . ' untuk "' . $fund->title . '" menunggu konfirmasi Anda. Periksa rekening Anda, lalu konfirmasi di Donasi Masuk.',
        ]);

        return redirect()->route('donor.donations.index')
            ->with('success', 'Donasi tercatat dan menunggu konfirmasi penerima. Terima kasih atas kebaikan Anda.');
    }

    public function donorIndex(Request $request)
    {
        $donations = $request->user()->donations()->with('fundRequest.receiver')->latest()->get();

        $confirmedTotal = $donations->where('status', 'confirmed')->sum('amount');

        return view('donor.donations', compact('donations', 'confirmedTotal'));
    }

    // ---------- Bukti transfer (privat) ----------

    public function proof(Request $request, Donation $donation)
    {
        $user = $request->user();
        $allowed = $user->isAdmin()
            || $user->id === $donation->donor_id
            || $user->id === $donation->fundRequest->receiver_id;

        abort_unless($allowed, 403);
        abort_unless(Storage::disk('local')->exists($donation->proof_photo_url), 404);

        return Storage::disk('local')->response($donation->proof_photo_url);
    }

    // ---------- Penerima ----------

    public function receiverIndex(Request $request)
    {
        $user = $request->user();
        $tab = (string) $request->query('tab', 'pending');
        if (! in_array($tab, ['pending', 'confirmed', 'rejected', 'all'], true)) {
            $tab = 'pending';
        }

        $base = fn () => Donation::whereHas('fundRequest', fn ($q) => $q->where('receiver_id', $user->id));

        $donations = $base()->with(['fundRequest', 'donor'])
            ->when($tab !== 'all', fn ($q) => $q->where('status', $tab))
            ->latest()
            ->limit(100)
            ->get();

        $counts = $base()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts['all'] = $counts->sum();

        return view('receiver.donations', compact('donations', 'tab', 'counts'));
    }

    public function confirm(Request $request, Donation $donation)
    {
        $this->authorizeReceiver($request, $donation);

        DB::transaction(function () use ($donation) {
            $d = Donation::whereKey($donation->id)->lockForUpdate()->first();
            abort_unless($d->status === 'pending', 403, 'Donasi ini sudah diproses.');

            $fund = FundRequest::whereKey($d->fund_request_id)->lockForUpdate()->first();

            $d->update(['status' => 'confirmed', 'confirmed_at' => now()]);
            $d->donor->increment('verified_count');

            AppNotification::create([
                'user_id' => $d->donor_id,
                'message' => 'Donasi Anda ' . FundRequest::rupiah($d->amount) . ' untuk "' . $fund->title . '" sudah dikonfirmasi penerima. Terima kasih!',
            ]);

            $collected = (int) $fund->donations()->where('status', 'confirmed')->sum('amount');
            if ($fund->status === 'published' && $collected >= $fund->target_amount) {
                $fund->update(['status' => 'completed']);
                AppNotification::create([
                    'user_id' => $fund->receiver_id,
                    'message' => 'Target dana "' . $fund->title . '" tercapai. Terima kasih kepada seluruh donatur.',
                ]);
            }
        });

        return back()->with('success', 'Donasi dikonfirmasi dan ditambahkan ke progres.');
    }

    public function reject(Request $request, Donation $donation)
    {
        $this->authorizeReceiver($request, $donation);

        $data = $request->validate([
            'receiver_note' => ['required', 'string', 'max:300'],
        ], [
            'receiver_note.required' => 'Alasan penolakan wajib diisi.',
            'receiver_note.max' => 'Alasan maksimal 300 karakter.',
        ]);

        DB::transaction(function () use ($donation, $data) {
            $d = Donation::whereKey($donation->id)->lockForUpdate()->first();
            abort_unless($d->status === 'pending', 403, 'Donasi ini sudah diproses.');

            $fund = $d->fundRequest;
            $d->update(['status' => 'rejected', 'receiver_note' => $data['receiver_note']]);

            AppNotification::create([
                'user_id' => $d->donor_id,
                'message' => 'Donasi Anda ' . FundRequest::rupiah($d->amount) . ' untuk "' . $fund->title . '" ditolak penerima. Alasan: ' . $data['receiver_note'] . '. Jika Anda sudah mentransfer, hubungi admin.',
            ]);

            // Penolakan ditinjau admin agar tidak ada sengketa yang terlewat
            User::where('role', 'admin')->get()->each(fn ($admin) => AppNotification::create([
                'user_id' => $admin->id,
                'message' => 'Donasi ' . FundRequest::rupiah($d->amount) . ' untuk "' . $fund->title . '" ditolak penerima dan perlu ditinjau.',
            ]));
        });

        return back()->with('success', 'Donasi ditolak. Admin akan meninjaunya bila diperlukan.');
    }

    // ---------- Admin (hanya melihat) ----------

    public function adminIndex(Request $request)
    {
        $status = (string) $request->query('status', 'all');
        if (! in_array($status, ['pending', 'confirmed', 'rejected', 'all'], true)) {
            $status = 'all';
        }

        $donations = Donation::with(['fundRequest.receiver', 'donor'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->limit(200)
            ->get();

        $counts = Donation::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts['all'] = $counts->sum();

        return view('admin.donations', compact('donations', 'status', 'counts'));
    }

    private function authorizeReceiver(Request $request, Donation $donation): void
    {
        abort_unless($donation->fundRequest->receiver_id === $request->user()->id, 403);
    }
}
