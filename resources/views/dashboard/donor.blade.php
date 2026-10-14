@extends('layouts.app')
@section('title', 'Dashboard Donatur')
@section('content')
<div class="dash-head" style="margin-bottom:18px">
    <h2>Halo, {{ auth()->user()->name }}</h2>
    <p>Dashboard Donatur</p>
</div>
<div class="grid">
    <div class="card">
        <h3>Barang saya</h3>
        <p class="subtitle">Unggah dan kelola barang donasi.</p>
        <a class="btn" href="{{ route('donor.items.create') }}">+ Unggah Barang</a>
        <a class="btn btn-outline" href="{{ route('donor.items.index') }}">Lihat Barang</a>
    </div>
    <div class="card placeholder"><h3>Pengajuan masuk <span class="tag">segera</span></h3><p>Setujui atau tolak permintaan penerima.</p></div>
    <div class="card placeholder"><h3>Riwayat donasi <span class="tag">segera</span></h3><p>Lihat donasi yang sudah terverifikasi.</p></div>
</div>
@endsection
