@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
@include('admin._nav', ['active' => 'dashboard'])

<div class="dash-head" style="margin-bottom:18px">
    <h2>Halo, {{ auth()->user()->name }}</h2>
    <p>Dashboard Admin</p>
</div>

<div class="grid">
    <a class="card stat" href="{{ route('admin.items.index', ['status' => 'pending']) }}">
        <span class="stat-num">{{ $pendingItems }}</span>
        <span>Barang menunggu verifikasi</span>
    </a>
    <a class="card stat" href="{{ route('admin.items.index', ['status' => 'published']) }}">
        <span class="stat-num">{{ $publishedItems }}</span>
        <span>Barang dipublikasikan</span>
    </a>
    <a class="card stat" href="{{ route('admin.users.index', ['role' => 'donor']) }}">
        <span class="stat-num">{{ $donors }}</span>
        <span>Donatur</span>
    </a>
    <a class="card stat" href="{{ route('admin.users.index', ['role' => 'receiver']) }}">
        <span class="stat-num">{{ $receivers }}</span>
        <span>Penerima</span>
    </a>
    <a class="card stat" href="{{ route('admin.reports.index') }}">
        <span class="stat-num">{{ $openReports }}</span>
        <span>Laporan belum ditangani</span>
    </a>
    <a class="card stat" href="{{ route('admin.requests.index', ['tab' => 'awaiting_handover']) }}">
        <span class="stat-num">{{ $activeRequests }}</span>
        <span>Pengajuan berjalan</span>
    </a>
</div>
@endsection
