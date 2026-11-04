@extends('layouts.app')
@section('title', $fund->title . ' - BerbagiBaik')
@section('content')
@php
    use App\Models\FundRequest;
    $pct = $fund->percent($collected);
    $label = ['pending' => 'Menunggu verifikasi', 'published' => 'Tayang', 'rejected' => 'Ditolak', 'closed' => 'Ditutup', 'completed' => 'Tercapai'];
    $user = auth()->user();
    $isOwner = $user && $user->id === $fund->receiver_id;
    $expired = now()->toDateString() > $fund->deadline->toDateString();
    $canSeeBank = $user && ($user->isAdmin() || $isOwner);
@endphp

<p><a href="{{ route('funds.index') }}">&larr; Kembali ke Donasi Dana</a></p>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if (session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif

@if (in_array($fund->status, ['pending', 'rejected'], true))
    <div class="alert alert-error">
        Pratinjau: kebutuhan ini berstatus <strong>{{ $label[$fund->status] ?? $fund->status }}</strong> dan belum tampil di halaman publik.
    </div>
@elseif ($fund->status === 'completed')
    <div class="alert alert-success">Target dana tercapai. Terima kasih kepada seluruh donatur.</div>
@elseif ($fund->status === 'closed')
    <div class="alert alert-error">Kebutuhan dana ini sudah ditutup oleh penerima.</div>
@endif

<div class="detail">
    <div>
        @if ($fund->photo_url)
            <img src="{{ asset('storage/' . $fund->photo_url) }}" alt="{{ $fund->title }}">
        @else
            <div class="ph ph-big">Donasi Dana</div>
        @endif
        <div class="card" style="margin-top:18px">
            <h3 style="margin-top:0">Cerita kebutuhan</h3>
            <p style="margin:0">{!! nl2br(e($fund->description)) !!}</p>
        </div>
    </div>

    <div class="card">
        <h2 style="margin:0 0 6px">{{ $fund->title }}</h2>
        <p class="meta">Penerima: <strong>{{ $fund->receiver->name }}</strong></p>

        <p class="money">{{ FundRequest::rupiah($collected) }}</p>
        <p class="meta" style="margin-top:0">terkumpul dari target {{ FundRequest::rupiah($fund->target_amount) }}</p>
        <div class="progress"><div class="progress-bar" style="width: {{ $pct }}%"></div></div>
        <div class="fund-amounts" style="margin-top:6px">
            <span>{{ $pct }}% tercapai</span>
            <span>{{ $donorCount }} donatur</span>
        </div>
        <p class="meta">Batas waktu {{ $fund->deadline->format('d M Y') }}@if (! $expired) ({{ $fund->daysLeft() }} hari lagi)@else (sudah berakhir)@endif</p>

        <div class="action-box">
            @if ($fund->status === 'completed')
                <p style="margin:0"><strong>Target tercapai.</strong> Donasi baru tidak diterima lagi.</p>
            @elseif ($fund->status === 'closed')
                <p style="margin:0"><strong>Kebutuhan ditutup.</strong></p>
            @elseif ($fund->status !== 'published')
                <p class="subtitle" style="margin:0">Donasi belum dapat dilakukan.</p>
            @elseif ($expired)
                <p style="margin:0"><strong>Batas waktu telah berakhir.</strong> Donasi baru tidak diterima.</p>
            @elseif (! $user)
                <p>Ingin membantu? Masuk atau daftar sebagai donatur untuk berdonasi.</p>
                <div class="btn-row">
                    <a class="btn" href="{{ route('login') }}">Masuk</a>
                    <a class="btn btn-outline" href="{{ route('register') }}">Daftar</a>
                </div>
            @elseif ($user->isDonor())
                <div class="bank-box">
                    <strong>1. Transfer ke rekening penerima</strong><br>
                    {{ $fund->bank_name }} &middot; <span class="bank-num">{{ $fund->account_number }}</span><br>
                    a.n. {{ $fund->account_name }}
                </div>
                <p class="meta" style="margin:10px 0 0"><strong>2. Isi nominal dan unggah bukti transfer</strong></p>
                <form method="POST" action="{{ route('donor.donations.store', $fund) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="field">
                        <label for="amount">Nominal donasi (Rp)</label>
                        <input id="amount" type="number" name="amount" min="10000" step="1000" value="{{ old('amount') }}" placeholder="50000" required>
                        @error('amount') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <div class="field">
                        <label for="proof">Bukti transfer</label>
                        <input id="proof" type="file" name="proof" accept="image/png,image/jpeg,image/webp" data-preview="proof-preview" required>
                        <small class="hint">Foto atau tangkapan layar bukti transfer. JPG, PNG, atau WEBP, maksimal 2 MB.</small>
                        @error('proof') <div class="error">{{ $message }}</div> @enderror
                        <img id="proof-preview" class="preview" alt="Pratinjau bukti" style="display:none">
                    </div>
                    <div class="field">
                        <label for="message">Pesan untuk penerima (opsional)</label>
                        <textarea id="message" name="message" rows="2" maxlength="300">{{ old('message') }}</textarea>
                        @error('message') <div class="error">{{ $message }}</div> @enderror
                    </div>
                    <label class="check"><input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous'))> Tampilkan sebagai anonim di daftar publik</label>
                    <small class="hint">Penerima dan admin tetap dapat melihat identitas Anda (nama dan nomor WhatsApp) untuk keperluan konfirmasi.</small>
                    <button class="btn btn-block" type="submit">Kirim Donasi</button>
                </form>
            @else
                <p class="subtitle" style="margin:0">Hanya akun donatur yang dapat berdonasi.</p>
            @endif
        </div>

        @if ($canSeeBank)
            <div class="info-box">
                <strong>Rekening tujuan</strong> (hanya terlihat pemilik dan admin)<br>
                {{ $fund->bank_name }} &middot; {{ $fund->account_number }}<br>
                a.n. {{ $fund->account_name }}<br>
                <a href="{{ route('funds.document', $fund) }}" target="_blank" rel="noopener">Lihat dokumen pendukung</a>
            </div>
        @endif

        <p class="hint">BerbagiBaik tidak menampung dana. Transfer dilakukan langsung ke rekening penerima. Pastikan nama pemilik rekening sesuai, dan laporkan kebutuhan yang mencurigakan.</p>

        <h3 style="margin-bottom:6px">Donatur terkonfirmasi</h3>
        @forelse ($recent as $d)
            <p class="meta" style="margin:0 0 4px">
                <strong>{{ $d->is_anonymous ? 'Anonim' : $d->donor->name }}</strong> menyumbang {{ FundRequest::rupiah($d->amount) }}
                &middot; {{ $d->confirmed_at?->format('d M Y') }}
            </p>
            @if ($d->message)
                <p class="meta" style="margin:0 0 8px; font-style:italic">"{{ $d->message }}"</p>
            @endif
        @empty
            <p class="meta" style="margin:0">Belum ada donasi terkonfirmasi.</p>
        @endforelse

        @if ($user && ! $user->isAdmin() && ! $isOwner && in_array($fund->status, ['published', 'completed', 'closed'], true))
            <details class="reject-box" style="margin-top:18px">
                <summary class="link-btn">Laporkan kebutuhan ini</summary>
                <form method="POST" action="{{ route('funds.report', $fund) }}" style="width:100%">
                    @csrf
                    <textarea name="reason" rows="3" placeholder="Jelaskan alasan Anda (minimal 10 karakter)" required></textarea>
                    @error('reason') <div class="error">{{ $message }}</div> @enderror
                    <button class="btn btn-danger btn-small-dark" type="submit">Kirim laporan</button>
                </form>
            </details>
        @endif
    </div>
</div>
@endsection
