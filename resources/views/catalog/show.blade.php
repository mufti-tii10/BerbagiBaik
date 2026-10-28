@extends('layouts.app')
@section('title', $item->name . ' - BerbagiBaik')
@section('content')
@php
    $statusLabel = ['pending' => 'Menunggu verifikasi', 'published' => 'Dipublikasikan', 'rejected' => 'Ditolak', 'completed' => 'Selesai'];
    $reqLabel = ['pending' => 'Menunggu keputusan donatur', 'awaiting_handover' => 'Disetujui, menunggu serah terima'];
    $max = \App\Models\DonationRequest::MAX_ACTIVE;
    $isReceiver = auth()->check() && auth()->user()->isReceiver();
@endphp

<p><a href="{{ route('catalog.index') }}">&larr; Kembali ke daftar barang</a></p>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

@if ($item->status !== 'published')
    <div class="alert alert-error">
        Pratinjau: barang ini berstatus <strong>{{ $statusLabel[$item->status] ?? $item->status }}</strong> dan belum tampil di halaman publik.
    </div>
@endif

<div class="detail">
    <div>
        <img src="{{ asset('storage/' . $item->photo_url) }}" alt="{{ $item->name }}">
    </div>

    <div class="card">
        <span class="badge-soft">{{ $item->category->name }}</span>
        <h2 style="margin:10px 0 4px">{{ $item->name }}</h2>
        <p class="meta">Diunggah {{ $item->created_at->format('d M Y') }}</p>

        <dl class="spec">
            <dt>Kondisi</dt><dd>{{ $item->condition }}</dd>
            <dt>Lokasi</dt><dd>{{ $item->location }}</dd>
            <dt>Donatur</dt>
            <dd>
                {{ $item->donor->name }}
                @if ($item->donor->isTrustedDonor()) <span class="badge badge-star">&#9733; Terpercaya</span> @endif
            </dd>
        </dl>

        @if ($item->description)
            <h3 style="margin-bottom:4px">Deskripsi</h3>
            <p style="margin-top:0">{!! nl2br(e($item->description)) !!}</p>
        @endif

        <div class="action-box">
            @if ($isReceiver && $myRequest)
                <p><strong>Anda sudah mengajukan barang ini.</strong></p>
                <p class="meta">Status: {{ $reqLabel[$myRequest->status] ?? $myRequest->status }}</p>
                <a class="btn btn-outline" href="{{ route('receiver.requests.index') }}">Lihat Pengajuan Saya</a>
            @elseif ($reserved)
                <p><strong>Barang ini sedang dalam proses serah terima.</strong> Silakan lihat barang lainnya.</p>
            @elseif ($item->status !== 'published')
                <p class="subtitle" style="margin:0">Barang belum dapat diajukan.</p>
            @elseif (! auth()->check())
                <p>Ingin mendapatkan barang ini? Masuk atau daftar sebagai penerima untuk mengajukan permintaan.</p>
                <div class="btn-row">
                    <a class="btn" href="{{ route('login') }}">Masuk</a>
                    <a class="btn btn-outline" href="{{ route('register') }}">Daftar</a>
                </div>
            @elseif ($isReceiver)
                @if ($activeCount >= $max)
                    <p><strong>Batas {{ $max }} pengajuan aktif tercapai.</strong></p>
                    <p class="meta">Batalkan atau selesaikan pengajuan lain untuk mengajukan barang ini.</p>
                    <a class="btn btn-outline" href="{{ route('receiver.requests.index') }}">Lihat Pengajuan Saya</a>
                @else
                    <form method="POST" action="{{ route('receiver.requests.store', $item) }}">
                        @csrf
                        <div class="field" style="margin-top:0">
                            <label for="reason">Alasan permintaan</label>
                            <textarea id="reason" name="reason" rows="4" required
                                placeholder="Ceritakan singkat mengapa Anda membutuhkan barang ini">{{ old('reason') }}</textarea>
                            @error('reason') <div class="error">{{ $message }}</div> @enderror
                        </div>
                        <button class="btn btn-block" type="submit">Ajukan Permintaan</button>
                        <p class="hint">Pengajuan aktif Anda: {{ $activeCount }} dari {{ $max }}.</p>
                    </form>
                @endif
            @else
                <p class="subtitle" style="margin:0">Hanya akun penerima yang dapat mengajukan permintaan.</p>
            @endif
        </div>

        <p class="hint">Kontak donatur baru tersedia setelah pengajuan Anda disetujui.</p>
    </div>
</div>
@endsection
