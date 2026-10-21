@extends('layouts.app')
@section('title', 'Verifikasi Barang - Admin')
@section('content')
@include('admin._nav', ['active' => 'items'])
@php
    $tabs = ['pending' => 'Menunggu', 'published' => 'Dipublikasikan', 'rejected' => 'Ditolak', 'completed' => 'Selesai', 'all' => 'Semua'];
    $statusLabel = ['pending' => 'Menunggu verifikasi', 'published' => 'Dipublikasikan', 'rejected' => 'Ditolak', 'completed' => 'Selesai'];
@endphp

<div class="page-head">
    <div>
        <h2>Verifikasi Barang</h2>
        <p class="subtitle">Periksa foto, kategori, dan deskripsi sebelum barang dipublikasikan.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-error">{{ $errors->first() }}</div>
@endif

<div class="tabs">
    @foreach ($tabs as $key => $label)
        <a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.items.index', ['status' => $key]) }}">
            {{ $label }}@if ($key !== 'all') <span class="count">{{ $counts[$key] ?? 0 }}</span>@endif
        </a>
    @endforeach
</div>

@forelse ($items as $item)
    <div class="card item-row">
        <a href="{{ asset('storage/' . $item->photo_url) }}" target="_blank" rel="noopener">
            <img class="thumb" src="{{ asset('storage/' . $item->photo_url) }}" alt="{{ $item->name }}">
        </a>
        <div class="item-info">
            <h3>{{ $item->name }}</h3>
            <p class="meta">{{ $item->category->name }} &middot; {{ $item->condition }} &middot; {{ $item->location }}</p>
            <p class="meta">Donatur: {{ $item->donor->name }} &middot; WA {{ $item->donor->phone_wa }}</p>
            @if ($item->description)
                <p class="desc">{{ \Illuminate\Support\Str::limit($item->description, 160) }}</p>
            @endif
            <span class="status status-{{ $item->status }}">{{ $statusLabel[$item->status] ?? $item->status }}</span>
            @if ($item->admin_note)
                <p class="note-box">Catatan admin: {{ $item->admin_note }}</p>
            @endif
        </div>

        @if ($item->status !== 'completed')
            <div class="item-actions admin-actions">
                @if ($item->status !== 'published')
                    <form method="POST" action="{{ route('admin.items.approve', $item) }}">
                        @csrf
                        <button class="btn btn-success btn-small-dark" type="submit">Setujui</button>
                    </form>
                @endif
                @if ($item->status !== 'rejected')
                    <details class="reject-box">
                        <summary class="btn btn-danger btn-small-dark">Tolak</summary>
                        <form method="POST" action="{{ route('admin.items.reject', $item) }}">
                            @csrf
                            <textarea name="admin_note" rows="3" placeholder="Alasan penolakan atau saran perbaikan" required></textarea>
                            <button class="btn btn-danger btn-small-dark" type="submit">Kirim penolakan</button>
                        </form>
                    </details>
                @endif
            </div>
        @endif
    </div>
@empty
    <div class="card placeholder">
        <h3>Tidak ada barang</h3>
        <p>Belum ada barang dengan status ini.</p>
    </div>
@endforelse
@endsection
