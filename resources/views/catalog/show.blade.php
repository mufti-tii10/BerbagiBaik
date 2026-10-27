@extends('layouts.app')
@section('title', $item->name . ' - BerbagiBaik')
@section('content')
@php
    $statusLabel = ['pending' => 'Menunggu verifikasi', 'published' => 'Dipublikasikan', 'rejected' => 'Ditolak', 'completed' => 'Selesai'];
@endphp

<p><a href="{{ route('catalog.index') }}">&larr; Kembali ke daftar barang</a></p>

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
            @if ($reserved)
                <p><strong>Barang ini sedang dalam proses serah terima.</strong> Silakan lihat barang lainnya.</p>
            @elseif ($item->status !== 'published')
                <p class="subtitle" style="margin:0">Barang belum dapat diajukan.</p>
            @elseif (! auth()->check())
                <p>Ingin mendapatkan barang ini? Masuk atau daftar sebagai penerima untuk mengajukan permintaan.</p>
                <div class="btn-row">
                    <a class="btn" href="{{ route('login') }}">Masuk</a>
                    <a class="btn btn-outline" href="{{ route('register') }}">Daftar</a>
                </div>
            @elseif (auth()->user()->isReceiver())
                <button class="btn" type="button" disabled>Ajukan Permintaan</button>
                <span class="tag">segera</span>
                <p class="hint">Fitur pengajuan permintaan sedang disiapkan.</p>
            @else
                <p class="subtitle" style="margin:0">Hanya akun penerima yang dapat mengajukan permintaan.</p>
            @endif
        </div>

        <p class="hint">Kontak donatur baru tersedia setelah pengajuan Anda disetujui.</p>
    </div>
</div>
@endsection
