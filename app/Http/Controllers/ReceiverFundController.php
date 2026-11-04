<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\FundRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ReceiverFundController extends Controller
{
    public function index(Request $request)
    {
        $funds = $request->user()->fundRequests()
            ->withSum(['donations as collected_sum' => fn ($d) => $d->where('status', 'confirmed')], 'amount')
            ->latest()
            ->get();

        $activeCount = $funds->whereIn('status', ['pending', 'published'])->count();

        return view('receiver.funds.index', compact('funds', 'activeCount'));
    }

    public function create(Request $request)
    {
        $activeCount = $this->activeCount($request);

        return view('receiver.funds.create', ['banks' => FundRequest::BANKS, 'activeCount' => $activeCount]);
    }

    public function store(Request $request)
    {
        if ($this->activeCount($request) >= FundRequest::MAX_ACTIVE) {
            return redirect()->route('receiver.funds.index')
                ->with('error', 'Batas ' . FundRequest::MAX_ACTIVE . ' kebutuhan dana aktif tercapai. Tutup salah satu terlebih dahulu.');
        }

        $data = $this->validated($request, true);

        $data['proof_doc_url'] = $request->file('proof_doc')->store('fund-docs', 'local');
        $data['photo_url'] = $request->hasFile('photo') ? $request->file('photo')->store('funds', 'public') : null;
        unset($data['proof_doc'], $data['photo']);
        $data['receiver_id'] = $request->user()->id;
        $data['status'] = 'pending';

        $fund = FundRequest::create($data);

        User::where('role', 'admin')->get()->each(fn ($admin) => AppNotification::create([
            'user_id' => $admin->id,
            'message' => 'Ada kebutuhan dana baru menunggu verifikasi: "' . $fund->title . '" dari ' . $request->user()->name . '.',
        ]));

        return redirect()->route('receiver.funds.index')
            ->with('success', 'Kebutuhan dana terkirim dan menunggu verifikasi admin.');
    }

    public function edit(Request $request, FundRequest $fund)
    {
        $this->authorizeEdit($request, $fund);

        return view('receiver.funds.edit', ['fund' => $fund, 'banks' => FundRequest::BANKS]);
    }

    public function update(Request $request, FundRequest $fund)
    {
        $this->authorizeEdit($request, $fund);

        $data = $this->validated($request, false);

        if ($request->hasFile('proof_doc')) {
            Storage::disk('local')->delete($fund->proof_doc_url);
            $data['proof_doc_url'] = $request->file('proof_doc')->store('fund-docs', 'local');
        }
        if ($request->hasFile('photo')) {
            if ($fund->photo_url) {
                Storage::disk('public')->delete($fund->photo_url);
            }
            $data['photo_url'] = $request->file('photo')->store('funds', 'public');
        }
        unset($data['proof_doc'], $data['photo']);

        // Setelah diubah, kebutuhan diverifikasi ulang oleh admin
        $data['status'] = 'pending';
        $data['admin_note'] = null;
        $fund->update($data);

        return redirect()->route('receiver.funds.index')
            ->with('success', 'Kebutuhan dana diperbarui dan dikirim ulang untuk verifikasi admin.');
    }

    public function close(Request $request, FundRequest $fund)
    {
        abort_unless($fund->receiver_id === $request->user()->id, 403);
        abort_unless($fund->status === 'published', 403, 'Hanya kebutuhan yang sedang tayang yang dapat ditutup.');

        $fund->update(['status' => 'closed']);

        return back()->with('success', 'Kebutuhan dana ditutup.');
    }

    public function destroy(Request $request, FundRequest $fund)
    {
        abort_unless($fund->receiver_id === $request->user()->id, 403);
        abort_unless(in_array($fund->status, ['pending', 'rejected'], true), 403, 'Kebutuhan yang sudah tayang tidak dapat dihapus.');
        abort_if($fund->donations()->exists(), 403, 'Kebutuhan yang sudah menerima donasi tidak dapat dihapus.');

        Storage::disk('local')->delete($fund->proof_doc_url);
        if ($fund->photo_url) {
            Storage::disk('public')->delete($fund->photo_url);
        }
        $fund->delete();

        return back()->with('success', 'Kebutuhan dana dihapus.');
    }

    private function activeCount(Request $request): int
    {
        return $request->user()->fundRequests()->whereIn('status', ['pending', 'published'])->count();
    }

    private function authorizeEdit(Request $request, FundRequest $fund): void
    {
        abort_unless($fund->receiver_id === $request->user()->id, 403);
        // Setelah tayang, data (terutama rekening) dikunci agar tidak bisa diubah sepihak
        abort_unless(in_array($fund->status, ['pending', 'rejected'], true), 403, 'Kebutuhan yang sudah tayang tidak dapat diubah. Tutup dan buat kebutuhan baru bila perlu.');
    }

    private function validated(Request $request, bool $docRequired): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'min:30', 'max:2000'],
            'target_amount' => ['required', 'integer', 'min:100000', 'max:100000000'],
            'deadline' => ['required', 'date', 'after:today', 'before_or_equal:' . now()->addDays(90)->toDateString()],
            'bank_name' => ['required', Rule::in(FundRequest::BANKS)],
            'account_name' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'regex:/^[0-9]{5,30}$/'],
            'proof_doc' => [$docRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'title.required' => 'Judul wajib diisi.',
            'description.required' => 'Ceritakan kebutuhan Anda.',
            'description.min' => 'Cerita terlalu singkat, tulis minimal 30 karakter.',
            'target_amount.required' => 'Target nominal wajib diisi.',
            'target_amount.integer' => 'Target nominal harus berupa angka.',
            'target_amount.min' => 'Target minimal Rp100.000.',
            'target_amount.max' => 'Target maksimal Rp100.000.000.',
            'deadline.required' => 'Batas waktu wajib diisi.',
            'deadline.after' => 'Batas waktu harus setelah hari ini.',
            'deadline.before_or_equal' => 'Batas waktu maksimal 90 hari dari sekarang.',
            'bank_name.required' => 'Pilih bank atau e-wallet.',
            'account_name.required' => 'Nama pemilik rekening wajib diisi.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_number.regex' => 'Nomor rekening hanya angka (5 sampai 30 digit).',
            'proof_doc.required' => 'Dokumen pendukung wajib diunggah.',
            'proof_doc.image' => 'Dokumen harus berupa gambar (foto atau hasil pindai).',
            'proof_doc.mimes' => 'Format dokumen harus JPG, PNG, atau WEBP.',
            'proof_doc.max' => 'Ukuran dokumen maksimal 2 MB.',
            'photo.image' => 'Foto harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);
    }
}
