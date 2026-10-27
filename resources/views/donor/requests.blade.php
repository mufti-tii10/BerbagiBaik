@extends('layouts.app')
@section('title', 'Pengajuan Masuk - BerbagiBaik')
@section('content')
@include('donor._nav', ['active' => 'requests'])
@php
    $tabs = ['pending' => 'Menunggu keputusan', 'awaiting_handover' => 'Menunggu serah terima', 'rejected' => 'Ditolak', 'all' => 'Semua'];
    $label = ['pending' => 'Menunggu keputusan', 'awaiting_handover' => 'Menunggu serah terima', 'received' => 'Diterima', 'completed' => 'Selesai', 'rejected' => 'Ditolak', 'expired' => 'Kedaluwarsa'];
    $css = ['pending' => 'status-pending', 'awaiting_handover' => 'status-completed', 'received' => 'status-published', 'completed' => 'status-published', 'rejected' => 'status-rejected', 'expired' => 'status-rejected'];
@endphp

<div class="page-head">
    <div>
        <h2>Pengajuan Masuk</h2>
        <p class="subtitle">Permintaan penerima atas barang Anda.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="tabs">
    @foreach ($tabs as $key => $text)
        <a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route('donor.requests.index', ['status' => $key]) }}">
            {{ $text }}@if ($key !== 'all') <span class="count">{{ $counts[$key] ?? 0 }}</span>@endif
        </a>
    @endforeach
</div>

@forelse ($requests as $r)
    @php
        $waText = 'Halo ' . $r->receiver->name . ', pengajuan Anda untuk "' . $r->item->name . '" di BerbagiBaik sudah saya setujui. Kapan kita bisa bertemu untuk serah terima?';
    @endphp
    <div class="card item-row">
        <img class="thumb" src="{{ asset('storage/' . $r->item->photo_url) }}" alt="{{ $r->item->name }}">
        <div class="item-info">
            <h3>{{ $r->item->name }}</h3>
            <p class="meta">Diajukan oleh <strong>{{ $r->receiver->name }}</strong> &middot; {{ $r->created_at->format('d M Y H:i') }}</p>
            @if ($r->reason)
                <p class="desc"><strong>Alasan:</strong> {{ $r->reason }}</p>
            @endif
            <span class="status {{ $css[$r->status] ?? 'status-pending' }}">{{ $label[$r->status] ?? $r->status }}</span>

            @if ($r->status === 'awaiting_handover')
                <p class="info-box">
                    Atur serah terima dengan penerima:
                    <a href="https://wa.me/{{ $r->receiver->phone_wa }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener">
                        hubungi via WhatsApp ({{ $r->receiver->phone_wa }})
                    </a>.<br>
                    <small>Setelah barang diserahkan, penerima akan mengonfirmasi penerimaan lewat WhatsApp.
                    @if ($r->code_expires_at) Batas konfirmasi: {{ $r->code_expires_at->format('d M Y') }}. @endif</small>
                </p>
            @endif
        </div>

        @if ($r->status === 'pending')
            <div class="item-actions admin-actions">
                <form method="POST" action="{{ route('donor.requests.approve', $r) }}" onsubmit="return confirm('Setujui pengajuan ini? Pengajuan lain untuk barang ini akan otomatis ditolak.')">
                    @csrf
                    <button class="btn btn-success btn-small-dark" type="submit">Setujui</button>
                </form>
                <form method="POST" action="{{ route('donor.requests.reject', $r) }}" onsubmit="return confirm('Tolak pengajuan ini?')">
                    @csrf
                    <button class="btn btn-danger btn-small-dark" type="submit">Tolak</button>
                </form>
            </div>
        @endif
    </div>
@empty
    <div class="card placeholder">
        <h3>Tidak ada pengajuan</h3>
        <p>Belum ada pengajuan dengan status ini.</p>
    </div>
@endforelse
@endsection
