@extends('layouts.app')
@section('title', 'Pantau Pengajuan - Admin')
@section('content')
@include('admin._nav', ['active' => 'requests'])
@php
    $tabs = ['all' => 'Semua', 'pending' => 'Menunggu donatur', 'awaiting_handover' => 'Menunggu serah terima', 'done' => 'Selesai', 'closed' => 'Ditutup'];
    $label = ['pending' => 'Menunggu keputusan donatur', 'awaiting_handover' => 'Disetujui, menunggu serah terima', 'received' => 'Diterima', 'completed' => 'Selesai', 'rejected' => 'Ditolak donatur', 'cancelled' => 'Dibatalkan penerima', 'expired' => 'Kedaluwarsa'];
    $css = ['pending' => 'status-pending', 'awaiting_handover' => 'status-completed', 'received' => 'status-published', 'completed' => 'status-published', 'rejected' => 'status-rejected', 'cancelled' => 'status-cancelled', 'expired' => 'status-rejected'];
@endphp

<div class="page-head">
    <div>
        <h2>Pantau Pengajuan</h2>
        <p class="subtitle">Barang yang diminta penerima, beserta donatur dan statusnya. Halaman ini hanya untuk memantau.</p>
    </div>
</div>

<form method="GET" action="{{ route('admin.requests.index') }}" class="filter-bar">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <input name="q" value="{{ $q }}" placeholder="Cari nama barang, donatur, atau penerima">
    <button class="btn" type="submit">Cari</button>
</form>

<div class="tabs">
    @foreach ($tabs as $key => $text)
        <a class="tab {{ $tab === $key ? 'active' : '' }}" href="{{ route('admin.requests.index', ['tab' => $key, 'q' => $q]) }}">
            {{ $text }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
        </a>
    @endforeach
</div>

@forelse ($requests as $r)
    @php
        $donor = $r->item->donor;
        $overdue = $r->status === 'awaiting_handover' && $r->code_expires_at && $r->code_expires_at->isPast();
    @endphp
    <div class="card item-row">
        <a href="{{ route('catalog.show', $r->item) }}" target="_blank" rel="noopener">
            <img class="thumb" src="{{ asset('storage/' . $r->item->photo_url) }}" alt="{{ $r->item->name }}">
        </a>
        <div class="item-info">
            <h3>{{ $r->item->name }}</h3>
            <p class="meta">{{ $r->item->category->name }} &middot; {{ $r->item->location }}</p>

            <p class="meta">
                <strong>Donatur:</strong> {{ $donor->name }}
                @if ($donor->isTrustedDonor()) <span class="badge badge-star">&#9733; Terpercaya</span> @endif
                &middot; WA {{ $donor->phone_wa }}
            </p>
            <p class="meta">
                <strong>Penerima:</strong> {{ $r->receiver->name }} &middot; WA {{ $r->receiver->phone_wa }}
            </p>

            @if ($r->reason)
                <p class="desc"><strong>Alasan:</strong> {{ $r->reason }}</p>
            @endif

            <span class="status {{ $css[$r->status] ?? 'status-pending' }}">{{ $label[$r->status] ?? $r->status }}</span>

            <p class="meta" style="margin-top:8px">
                Diajukan {{ $r->created_at->format('d M Y H:i') }}
                @if ($r->confirmed_at) &middot; Dikonfirmasi {{ $r->confirmed_at->format('d M Y H:i') }} @endif
                @if ($r->status === 'awaiting_handover' && $r->code_expires_at) &middot; Batas konfirmasi {{ $r->code_expires_at->format('d M Y') }} @endif
            </p>

            @if ($overdue)
                <p class="note-box">Sudah melewati batas konfirmasi. Penerima belum mengonfirmasi penerimaan barang.</p>
            @endif
        </div>
    </div>
@empty
    <div class="card placeholder">
        <h3>Tidak ada pengajuan</h3>
        <p>Belum ada pengajuan yang cocok dengan filter ini.</p>
    </div>
@endforelse
@endsection
