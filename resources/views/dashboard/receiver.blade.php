@extends('layouts.app')
@section('title', 'Dashboard Penerima')
@section('content')
<div class="dash-head" style="margin-bottom:18px">
    <h2>Halo, {{ auth()->user()->name }}</h2>
    <p>Dashboard Penerima</p>
</div>
<div class="grid">
    <div class="card placeholder"><h3>Cari barang <span class="tag">segera</span></h3><p>Filter berdasarkan kategori dan lokasi.</p></div>
    <div class="card placeholder"><h3>Pengajuan saya <span class="tag">segera</span></h3><p>Pantau status permintaan Anda.</p></div>
    <div class="card placeholder"><h3>Konfirmasi penerimaan <span class="tag">segera</span></h3><p>Masukkan kode dari WhatsApp.</p></div>
</div>
@endsection
