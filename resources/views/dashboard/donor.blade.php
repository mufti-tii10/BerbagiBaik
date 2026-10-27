@extends('layouts.app')
@section('title', 'Dashboard Donatur')
@section('content')
@include('donor._nav', ['active' => 'dashboard'])
@php
    $pct = min(100, (int) round(($user->verified_count / 3) * 100));
    $done = ($requestCounts['received'] ?? 0) + ($requestCounts['completed'] ?? 0);
@endphp

<div class="dash-head" style="margin-bottom:18px">
    <h2>Halo, {{ $user->name }}</h2>
    <p>Dashboard Donatur</p>
</div>

<div class="grid">
    <a class="card stat" href="{{ route('donor.items.index') }}">
        <span class="stat-num">{{ $itemCounts['pending'] ?? 0 }}</span>
        <span>Barang menunggu verifikasi</span>
    </a>
    <a class="card stat" href="{{ route('donor.items.index') }}">
        <span class="stat-num">{{ $itemCounts['published'] ?? 0 }}</span>
        <span>Barang dipublikasikan</span>
    </a>
    <a class="card stat" href="{{ route('donor.requests.index', ['status' => 'pending']) }}">
        <span class="stat-num">{{ $requestCounts['pending'] ?? 0 }}</span>
        <span>Pengajuan menunggu keputusan Anda</span>
    </a>
    <a class="card stat" href="{{ route('donor.requests.index', ['status' => 'awaiting_handover']) }}">
        <span class="stat-num">{{ $requestCounts['awaiting_handover'] ?? 0 }}</span>
        <span>Menunggu serah terima</span>
    </a>
    <a class="card stat" href="{{ route('donor.history') }}">
        <span class="stat-num">{{ $done }}</span>
        <span>Donasi selesai</span>
    </a>
</div>

<div class="card" style="margin-top:18px">
    <h3 style="margin-top:0">Lencana Donatur Terpercaya</h3>
    @if ($user->isTrustedDonor())
        <p><span class="badge badge-star">&#9733; Terpercaya</span> Terima kasih, donasi Anda sudah terverifikasi {{ $user->verified_count }} kali.</p>
    @else
        <p class="subtitle">Selesaikan 3 donasi yang terverifikasi untuk mendapatkan lencana. Sekarang: {{ $user->verified_count }} dari 3.</p>
    @endif
    <div class="progress"><div class="progress-bar" style="width: {{ $pct }}%"></div></div>
</div>

<div class="btn-row" style="margin-top:18px">
    <a class="btn btn-accent" href="{{ route('donor.items.create') }}">+ Unggah Barang</a>
    <a class="btn btn-outline" href="{{ route('donor.items.index') }}">Lihat Barang Saya</a>
</div>
@endsection
