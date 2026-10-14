@extends('layouts.app')
@section('title', 'Daftar - BerbagiBaik')
@section('content')
<div class="card auth-wrap">
    <h2>Buat Akun</h2>
    <p class="subtitle">Daftar sebagai donatur atau penerima.</p>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="field">
            <label for="name">Nama</label>
            <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="phone_wa">Nomor WhatsApp</label>
            <input id="phone_wa" name="phone_wa" placeholder="081234567890" value="{{ old('phone_wa') }}" inputmode="tel" required>
            @error('phone_wa') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="role">Daftar sebagai</label>
            <select id="role" name="role" required>
                <option value="">-- pilih --</option>
                <option value="donor" @selected(old('role') === 'donor')>Donatur (memberi barang)</option>
                <option value="receiver" @selected(old('role') === 'receiver')>Penerima (membutuhkan barang)</option>
            </select>
            @error('role') <div class="error">{{ $message }}</div> @enderror
        </div>

        <x-password-input name="password" label="Sandi (minimal 8 karakter)" autocomplete="new-password" />
        <x-password-input name="password_confirmation" label="Ulangi sandi" autocomplete="new-password" />

        <button class="btn btn-block" type="submit">Daftar</button>
    </form>
    <p class="alt-link">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
</div>
@endsection
