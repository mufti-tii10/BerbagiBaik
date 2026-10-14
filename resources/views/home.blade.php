@extends('layouts.app')
@section('content')
<section class="hero">
    <h1>Barang yang tak terpakai bisa jadi manfaat bagi orang lain</h1>
    <p>BerbagiBaik mempertemukan donatur barang layak pakai dengan penerima yang membutuhkan. Barang diverifikasi admin, dan penerimaan dikonfirmasi lewat WhatsApp.</p>
    <div class="actions">
        @guest
            <a class="btn btn-accent" href="{{ route('register') }}">Mulai Berbagi</a>
            <a class="btn btn-outline" href="{{ route('login') }}">Masuk</a>
        @else
            <a class="btn btn-accent" href="{{ route('dashboard') }}">Ke Dashboard</a>
        @endguest
    </div>
</section>

<div class="grid">
    <div class="card feature">
        <div class="icon-circle">&#10003;</div>
        <h3>Barang terverifikasi</h3>
        <p>Setiap barang diperiksa admin sebelum tampil ke publik.</p>
    </div>
    <div class="card feature">
        <div class="icon-circle">&#9906;</div>
        <h3>Mudah dicari</h3>
        <p>Cari berdasarkan kategori dan lokasi agar tepat sasaran.</p>
    </div>
    <div class="card feature">
        <div class="icon-circle">&#9993;</div>
        <h3>Konfirmasi WhatsApp</h3>
        <p>Penerimaan dibuktikan dengan kode unik yang dikirim lewat WhatsApp.</p>
    </div>
</div>

<h2 class="section-title">Cara kerja</h2>
<ol class="steps">
    <li>Donatur mengunggah barang beserta foto, kategori, dan kondisinya.</li>
    <li>Admin memverifikasi kelayakan barang sebelum dipublikasikan.</li>
    <li>Penerima mencari barang dan mengajukan permintaan.</li>
    <li>Donatur menyetujui, lalu kode konfirmasi dikirim lewat WhatsApp.</li>
    <li>Setelah barang diserahkan, penerima mengonfirmasi dan donasi dinyatakan selesai.</li>
</ol>
@endsection
