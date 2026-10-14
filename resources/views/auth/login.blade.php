@extends('layouts.app')
@section('title', 'Masuk - BerbagiBaik')
@section('content')
<div class="card auth-wrap">
    <h2>Masuk</h2>
    <p class="subtitle">Selamat datang kembali di BerbagiBaik.</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <x-password-input name="password" label="Sandi" autocomplete="current-password" />

        <label class="check"><input type="checkbox" name="remember" value="1"> Ingat saya</label>

        <button class="btn btn-block" type="submit">Masuk</button>
    </form>
    <p class="alt-link">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
</div>
@endsection
