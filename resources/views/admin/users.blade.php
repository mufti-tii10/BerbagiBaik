@extends('layouts.app')
@section('title', 'Pengguna - Admin')
@section('content')
@include('admin._nav', ['active' => 'users'])
@php
    $roleLabel = ['donor' => 'Donatur', 'receiver' => 'Penerima', 'admin' => 'Admin'];
@endphp

<div class="page-head">
    <div>
        <h2>Pengguna</h2>
        <p class="subtitle">Kelola akun donatur dan penerima.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar">
    <input name="q" value="{{ $q }}" placeholder="Cari nama, email, atau nomor WA">
    <select name="role">
        <option value="all" @selected($role === 'all')>Semua peran</option>
        <option value="donor" @selected($role === 'donor')>Donatur</option>
        <option value="receiver" @selected($role === 'receiver')>Penerima</option>
        <option value="admin" @selected($role === 'admin')>Admin</option>
    </select>
    <button class="btn" type="submit">Cari</button>
</form>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>WhatsApp</th>
                <th>Peran</th>
                <th>Aktivitas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone_wa ?? '-' }}</td>
                    <td><span class="badge-soft">{{ $roleLabel[$user->role] ?? $user->role }}</span></td>
                    <td>
                        @if ($user->role === 'donor') {{ $user->items_count }} barang
                        @elseif ($user->role === 'receiver') {{ $user->donation_requests_count }} pengajuan
                        @else - @endif
                    </td>
                    <td>
                        @if ($user->is_blocked)
                            <span class="status status-rejected">Diblokir</span>
                        @else
                            <span class="status status-published">Aktif</span>
                        @endif
                    </td>
                    <td>
                        @if ($user->role !== 'admin')
                            @if ($user->is_blocked)
                                <form method="POST" action="{{ route('admin.users.unblock', $user) }}">
                                    @csrf
                                    <button class="btn btn-success btn-small-dark" type="submit">Buka blokir</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.users.block', $user) }}" onsubmit="return confirm('Blokir akun ini? Pengguna akan langsung keluar dari sistem.')">
                                    @csrf
                                    <button class="btn btn-danger btn-small-dark" type="submit">Blokir</button>
                                </form>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada pengguna yang cocok.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
