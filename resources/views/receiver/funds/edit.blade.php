@extends('layouts.app')
@section('title', 'Edit Kebutuhan Dana - BerbagiBaik')
@section('content')
<div class="card auth-wrap" style="max-width:640px">
    <h2>Edit Kebutuhan Dana</h2>
    <p class="subtitle">Setelah diubah, kebutuhan akan diverifikasi ulang oleh admin.</p>
    <form method="POST" action="{{ route('receiver.funds.update', $fund) }}" enctype="multipart/form-data">
        @method('PUT')
        @include('receiver.funds._form', ['fund' => $fund])
        <button class="btn btn-block" type="submit">Simpan Perubahan</button>
    </form>
    <p class="alt-link"><a href="{{ route('receiver.funds.index') }}">&larr; Kembali</a></p>
</div>
@endsection
