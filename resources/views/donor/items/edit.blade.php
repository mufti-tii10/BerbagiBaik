@extends('layouts.app')
@section('title', 'Edit Barang - BerbagiBaik')
@section('content')
<div class="card auth-wrap" style="max-width:600px">
    <h2>Edit Barang</h2>
    <p class="subtitle">Setelah diubah, barang akan diverifikasi ulang oleh admin.</p>
    <form method="POST" action="{{ route('donor.items.update', $item) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('donor.items._form', ['item' => $item])
        <button class="btn btn-block" type="submit">Simpan Perubahan</button>
    </form>
    <p class="alt-link"><a href="{{ route('donor.items.index') }}">&larr; Kembali</a></p>
</div>
@endsection
