@extends('layouts.app')
@section('title', 'Profil - BerbagiBaik')
@section('content')
@php $roleLabel = ['donor' => 'Donatur', 'receiver' => 'Penerima', 'admin' => 'Admin']; @endphp

<div class="card auth-wrap" style="max-width:600px">
    <h2>Profil Saya</h2>
    <p class="subtitle">
        <span class="badge-soft">{{ $roleLabel[$user->role] ?? $user->role }}</span>
        @if ($user->isTrustedDonor()) <span class="badge badge-star">&#9733; Donatur Terpercaya</span> @endif
    </p>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')

        <div class="field">
            <label for="email">Email</label>
            <input id="email" value="{{ $user->email }}" disabled>
            <small class="hint">Email tidak dapat diubah.</small>
        </div>

        <div class="field">
            <label for="name">Nama</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required>
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="phone_wa">Nomor WhatsApp</label>
            <input id="phone_wa" name="phone_wa" value="{{ old('phone_wa', $user->phone_wa) }}" placeholder="081234567890" inputmode="tel" {{ $user->isAdmin() ? '' : 'required' }}>
            <small class="hint">Dipakai untuk verifikasi penerimaan barang. Tidak ditampilkan ke publik.</small>
            @error('phone_wa') <div class="error">{{ $message }}</div> @enderror
        </div>

        <h3 style="margin-top:26px">Ganti sandi (opsional)</h3>
        <x-password-input name="current_password" label="Sandi lama" autocomplete="current-password" :required="false" />
        <x-password-input name="password" label="Sandi baru (minimal 8 karakter)" autocomplete="new-password" :required="false" />
        <x-password-input name="password_confirmation" label="Ulangi sandi baru" autocomplete="new-password" :required="false" />

        <button class="btn btn-block" type="submit">Simpan Perubahan</button>
    </form>
</div>
@endsection
