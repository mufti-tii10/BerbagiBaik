@csrf
<div class="field">
    <label for="title">Judul kebutuhan</label>
    <input id="title" name="title" value="{{ old('title', $fund?->title) }}" placeholder="Contoh: Biaya berobat ibu" required>
    @error('title') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="description">Cerita kebutuhan</label>
    <textarea id="description" name="description" rows="6" placeholder="Jelaskan siapa Anda, untuk apa dana dibutuhkan, dan rencana penggunaannya." required>{{ old('description', $fund?->description) }}</textarea>
    @error('description') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="target_amount">Target nominal (Rp)</label>
    <input id="target_amount" type="number" name="target_amount" min="100000" max="100000000" step="1000" value="{{ old('target_amount', $fund?->target_amount) }}" placeholder="500000" required>
    <small class="hint">Minimal Rp100.000, maksimal Rp100.000.000.</small>
    @error('target_amount') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="deadline">Batas waktu</label>
    <input id="deadline" type="date" name="deadline" value="{{ old('deadline', $fund?->deadline?->format('Y-m-d')) }}" min="{{ now()->addDay()->toDateString() }}" max="{{ now()->addDays(90)->toDateString() }}" required>
    <small class="hint">Maksimal 90 hari dari sekarang.</small>
    @error('deadline') <div class="error">{{ $message }}</div> @enderror
</div>

<h3 style="margin:24px 0 0">Rekening tujuan</h3>
<div class="info-box" style="margin-top:8px">
    Nama pemilik rekening <strong>harus sama dengan nama Anda</strong>. Admin akan mencocokkannya saat verifikasi, dan nomor rekening tidak ditampilkan di halaman publik.
</div>

<div class="field">
    <label for="bank_name">Bank atau e-wallet</label>
    <select id="bank_name" name="bank_name" required>
        <option value="">-- pilih --</option>
        @foreach ($banks as $b)
            <option value="{{ $b }}" @selected(old('bank_name', $fund?->bank_name) === $b)>{{ $b }}</option>
        @endforeach
    </select>
    @error('bank_name') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="account_name">Nama pemilik rekening</label>
    <input id="account_name" name="account_name" value="{{ old('account_name', $fund?->account_name) }}" required>
    @error('account_name') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="account_number">Nomor rekening</label>
    <input id="account_number" name="account_number" inputmode="numeric" value="{{ old('account_number', $fund?->account_number) }}" placeholder="Hanya angka" required>
    @error('account_number') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="proof_doc">Dokumen pendukung {{ $fund ? '(kosongkan jika tidak diganti)' : '' }}</label>
    <input id="proof_doc" type="file" name="proof_doc" accept="image/png,image/jpeg,image/webp" {{ $fund ? '' : 'required' }}>
    <small class="hint">Foto atau hasil pindai surat keterangan, tagihan, atau bukti kebutuhan. Hanya dilihat admin, tidak tampil publik. JPG, PNG, atau WEBP, maksimal 2 MB.</small>
    @error('proof_doc') <div class="error">{{ $message }}</div> @enderror
</div>

<div class="field">
    <label for="photo">Foto ilustrasi (opsional)</label>
    <input id="photo" type="file" name="photo" accept="image/png,image/jpeg,image/webp" data-preview="preview">
    <small class="hint">Tampil di halaman publik. Jangan gunakan foto yang memuat data pribadi sensitif.</small>
    @error('photo') <div class="error">{{ $message }}</div> @enderror
    <img id="preview" class="preview" alt="Pratinjau foto"
         @if ($fund && $fund->photo_url) src="{{ asset('storage/' . $fund->photo_url) }}" style="display:block" @else style="display:none" @endif>
</div>
