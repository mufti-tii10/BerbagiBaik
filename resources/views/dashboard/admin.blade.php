@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<div class="dash-head" style="margin-bottom:18px">
    <h2>Halo, {{ auth()->user()->name }}</h2>
    <p>Dashboard Admin</p>
</div>
<div class="grid">
    <div class="card placeholder"><h3>Verifikasi barang <span class="tag">segera</span></h3><p>Setujui atau tolak barang yang diunggah.</p></div>
    <div class="card placeholder"><h3>Laporan <span class="tag">segera</span></h3><p>Tindak lanjuti laporan pengguna.</p></div>
    <div class="card placeholder"><h3>Pengguna <span class="tag">segera</span></h3><p>Kelola data pengguna.</p></div>
</div>
@endsection
