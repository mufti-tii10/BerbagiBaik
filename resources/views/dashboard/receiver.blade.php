@extends('layouts.app')
@section('title', 'Dashboard Penerima')
@section('content')
@include('receiver._nav', ['active' => 'dashboard'])
@php
    $max = \App\Models\DonationRequest::MAX_ACTIVE;
    $pct = min(100, (int) round(($active / $max) * 100));
@endphp

<div class="dash-head" style="margin-bottom:18px">
    <h2>Halo, {{ $user->name }}</h2>
    <p>Dashboard Penerima</p>
</div>

<div class="grid">
    <a class="card stat" href="{{ route('receiver.requests.index', ['tab' => 'active']) }}">
        <span class="stat-num">{{ $counts['pending'] ?? 0 }}</span>
        <span>Menunggu keputusan donatur</span>
    </a>
    <a class="card stat" href="{{ route('receiver.requests.index', ['tab' => 'active']) }}">
        <span class="stat-num">{{ $counts['awaiting_handover'] ?? 0 }}</span>
        <span>Disetujui, menunggu serah terima</span>
    </a>
    <a class="card stat" href="{{ route('receiver.requests.index', ['tab' => 'done']) }}">
        <span class="stat-num">{{ $done }}</span>
        <span>Barang diterima</span>
    </a>
</div>

<div class="card" style="margin-top:18px">
    <h3 style="margin-top:0">Kuota pengajuan aktif</h3>
    <p class="subtitle">Anda memakai {{ $active }} dari {{ $max }} pengajuan aktif.
        @if ($active >= $max) Batalkan atau selesaikan pengajuan lain untuk mengajukan barang baru. @endif
    </p>
    <div class="progress"><div class="progress-bar" style="width: {{ $pct }}%"></div></div>
</div>

<div class="btn-row" style="margin-top:18px">
    <a class="btn btn-accent" href="{{ route('catalog.index') }}">Cari Barang</a>
    <a class="btn btn-outline" href="{{ route('receiver.requests.index') }}">Pengajuan Saya</a>
</div>
@endsection
