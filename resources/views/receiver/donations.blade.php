@extends('layouts.app')
@section('title', 'Donasi Masuk - BerbagiBaik')
@section('content')
@include('receiver._nav', ['active' => 'donations'])
@php
    use App\Models\FundRequest;
    $tabs = ['pending' => 'Menunggu konfirmasi', 'confirmed' => 'Terkonfirmasi', 'rejected' => 'Ditolak', 'all' => 'Semua'];
    $label = ['pending' => 'Menunggu konfirmasi Anda', 'confirmed' => 'Terkonfirmasi', 'rejected' => 'Ditolak', 'expired' => 'Kedaluwarsa'];
    $css = ['pending' => 'status-pending', 'confirmed' => 'status-published', 'rejected' => 'status-rejected', 'expired' => 'status-cancelled'];
@endphp

<div class="page-head">
    <div>
        <h2>Donasi Masuk</h2>
        <p class="subtitle">Periksa rekening Anda. Konfirmasi hanya setelah uang benar-benar masuk.</p>
    </div>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if ($errors->any()) <div class="alert alert-error">{{ $errors->first() }}</div> @endif

<div class="tabs">
    @foreach ($tabs as $key => $text)
        <a class="tab {{ $tab === $key ? 'active' : '' }}" href="{{ route('receiver.donations.index', ['tab' => $key]) }}">
            {{ $text }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
        </a>
    @endforeach
</div>

@forelse ($donations as $d)
    @php
        $waText = 'Halo ' . $d->donor->name . ', terima kasih atas donasi Anda untuk "' . $d->fundRequest->title . '" melalui BerbagiBaik.';
    @endphp
    <div class="card item-row">
        <div class="item-info">
            <h3>{{ $d->fundRequest->title }}</h3>
            <p class="money" style="margin:2px 0 6px; font-size:1.4rem">{{ FundRequest::rupiah($d->amount) }}</p>
            <p class="meta">
                Dari <strong>{{ $d->donor->name }}</strong>@if ($d->is_anonymous) <span class="tag">minta anonim di daftar publik</span>@endif
                &middot; {{ $d->created_at->format('d M Y H:i') }}
            </p>
            @if ($d->message) <p class="desc"><strong>Pesan:</strong> {{ $d->message }}</p> @endif
            <span class="status {{ $css[$d->status] ?? 'status-pending' }}">{{ $label[$d->status] ?? $d->status }}</span>
            @if ($d->status === 'rejected' && $d->receiver_note) <p class="note-box">Alasan Anda: {{ $d->receiver_note }}</p> @endif
            <p class="meta" style="margin-top:8px">
                <a href="{{ route('donations.proof', $d) }}" target="_blank" rel="noopener">Lihat bukti transfer</a>
                &middot; <a href="https://wa.me/{{ $d->donor->phone_wa }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener">Hubungi donatur via WhatsApp</a>
            </p>
        </div>

        @if ($d->status === 'pending')
            <div class="item-actions admin-actions">
                <form method="POST" action="{{ route('receiver.donations.confirm', $d) }}" onsubmit="return confirm('Pastikan uang {{ FundRequest::rupiah($d->amount) }} sudah masuk ke rekening Anda. Konfirmasi donasi ini?')">
                    @csrf
                    <button class="btn btn-success btn-small-dark" type="submit">Konfirmasi uang masuk</button>
                </form>
                <details class="reject-box">
                    <summary class="btn btn-danger btn-small-dark">Tolak</summary>
                    <form method="POST" action="{{ route('receiver.donations.reject', $d) }}">
                        @csrf
                        <textarea name="receiver_note" rows="3" placeholder="Alasan, misalnya uang belum masuk atau nominal tidak sesuai" required></textarea>
                        <button class="btn btn-danger btn-small-dark" type="submit">Kirim penolakan</button>
                    </form>
                </details>
            </div>
        @endif
    </div>
@empty
    <div class="card placeholder">
        <h3>Tidak ada donasi</h3>
        <p>Belum ada donasi dengan status ini.</p>
    </div>
@endforelse
@endsection
