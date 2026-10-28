@extends('layouts.app')
@section('title', 'Pengajuan Saya - BerbagiBaik')
@section('content')
@include('receiver._nav', ['active' => 'requests'])
@php
    $tabs = ['active' => 'Berjalan', 'done' => 'Selesai', 'closed' => 'Ditutup', 'all' => 'Semua'];
    $label = ['pending' => 'Menunggu keputusan', 'awaiting_handover' => 'Disetujui, menunggu serah terima', 'received' => 'Diterima', 'completed' => 'Selesai', 'rejected' => 'Ditolak donatur', 'cancelled' => 'Dibatalkan', 'expired' => 'Kedaluwarsa'];
    $css = ['pending' => 'status-pending', 'awaiting_handover' => 'status-completed', 'received' => 'status-published', 'completed' => 'status-published', 'rejected' => 'status-rejected', 'cancelled' => 'status-cancelled', 'expired' => 'status-rejected'];
@endphp

<div class="page-head">
    <div>
        <h2>Pengajuan Saya</h2>
        <p class="subtitle">Pantau status permintaan barang Anda.</p>
    </div>
    <a class="btn btn-accent" href="{{ route('catalog.index') }}">Cari Barang</a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="tabs">
    @foreach ($tabs as $key => $text)
        <a class="tab {{ $tab === $key ? 'active' : '' }}" href="{{ route('receiver.requests.index', ['tab' => $key]) }}">
            {{ $text }}@if ($key !== 'all') <span class="count">{{ $counts[$key] ?? 0 }}</span>@endif
        </a>
    @endforeach
</div>

@forelse ($requests as $r)
    @php
        $donor = $r->item->donor;
        $waText = 'Halo ' . $donor->name . ', saya ' . auth()->user()->name . '. Pengajuan saya untuk "' . $r->item->name . '" di BerbagiBaik sudah disetujui. Kapan kita bisa bertemu untuk serah terima?';
    @endphp
    <div class="card item-row">
        <a href="{{ route('catalog.show', $r->item) }}">
            <img class="thumb" src="{{ asset('storage/' . $r->item->photo_url) }}" alt="{{ $r->item->name }}">
        </a>
        <div class="item-info">
            <h3><a href="{{ route('catalog.show', $r->item) }}" style="text-decoration:none;color:inherit">{{ $r->item->name }}</a></h3>
            <p class="meta">{{ $r->item->category->name }} &middot; {{ $r->item->location }} &middot; Donatur: {{ $donor->name }}</p>
            <p class="meta">Diajukan {{ $r->created_at->format('d M Y H:i') }}</p>
            @if ($r->reason)
                <p class="desc"><strong>Alasan Anda:</strong> {{ $r->reason }}</p>
            @endif
            <span class="status {{ $css[$r->status] ?? 'status-pending' }}">{{ $label[$r->status] ?? $r->status }}</span>

            @if ($r->status === 'awaiting_handover')
                <p class="info-box">
                    Pengajuan Anda disetujui. Atur serah terima dengan donatur:
                    <a href="https://wa.me/{{ $donor->phone_wa }}?text={{ rawurlencode($waText) }}" target="_blank" rel="noopener">hubungi via WhatsApp ({{ $donor->phone_wa }})</a>.<br>
                    <small>Setelah barang Anda terima, Anda akan diminta mengonfirmasi penerimaan lewat WhatsApp.
                    @if ($r->code_expires_at) Batas konfirmasi: {{ $r->code_expires_at->format('d M Y') }}. @endif</small>
                </p>
            @endif
        </div>

        @if (in_array($r->status, \App\Models\DonationRequest::ACTIVE_STATUSES, true))
            <div class="item-actions admin-actions">
                <form method="POST" action="{{ route('receiver.requests.cancel', $r) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                    @csrf
                    <button class="btn btn-danger btn-small-dark" type="submit">Batalkan</button>
                </form>
            </div>
        @endif
    </div>
@empty
    <div class="card placeholder">
        <h3>Belum ada pengajuan</h3>
        <p>Pengajuan Anda akan tampil di sini. <a href="{{ route('catalog.index') }}">Cari barang</a> untuk mulai mengajukan.</p>
    </div>
@endforelse
@endsection
