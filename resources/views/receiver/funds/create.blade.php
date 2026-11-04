@extends('layouts.app')
@section('title', 'Ajukan Kebutuhan Dana - BerbagiBaik')
@section('content')
<div class="card auth-wrap" style="max-width:640px">
    <h2>Ajukan Kebutuhan Dana</h2>
    <p class="subtitle">Admin akan memverifikasi sebelum tayang. Aktif {{ $activeCount }} dari {{ \App\Models\FundRequest::MAX_ACTIVE }} kebutuhan.</p>
    @if (session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif
    <form method="POST" action="{{ route('receiver.funds.store') }}" enctype="multipart/form-data">
        @include('receiver.funds._form', ['fund' => null])
        <button class="btn btn-block" type="submit">Kirim untuk Verifikasi</button>
    </form>
    <p class="alt-link"><a href="{{ route('receiver.funds.index') }}">&larr; Kembali</a></p>
</div>
@endsection
