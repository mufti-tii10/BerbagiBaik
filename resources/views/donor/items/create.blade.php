@extends('layouts.app')
@section('title', 'Unggah Barang - BerbagiBaik')
@section('content')
<div class="card auth-wrap" style="max-width:600px">
    <h2>Unggah Barang</h2>
    <p class="subtitle">Barang akan diperiksa admin sebelum tampil ke publik.</p>
    <form method="POST" action="{{ route('donor.items.store') }}" enctype="multipart/form-data">
        @include('donor.items._form', ['item' => null])
        <button class="btn btn-block" type="submit">Kirim untuk Verifikasi</button>
    </form>
    <p class="alt-link"><a href="{{ route('donor.items.index') }}">&larr; Kembali</a></p>
</div>
@endsection
