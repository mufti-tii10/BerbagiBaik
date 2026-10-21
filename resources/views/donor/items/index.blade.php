@extends('layouts.app')
@section('title', 'Barang Saya - BerbagiBaik')
@section('content')
@php
    $statusLabel = ['pending' => 'Menunggu verifikasi', 'published' => 'Dipublikasikan', 'rejected' => 'Ditolak', 'completed' => 'Selesai'];
@endphp

<div class="page-head">
    <div>
        <h2>Barang Saya</h2>
        <p class="subtitle">Kelola barang yang ingin Anda donasikan.</p>
    </div>
    <a class="btn btn-accent" href="{{ route('donor.items.create') }}">+ Unggah Barang</a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@forelse ($items as $item)
    <div class="card item-row">
        <img class="thumb" src="{{ asset('storage/' . $item->photo_url) }}" alt="{{ $item->name }}">
        <div class="item-info">
            <h3>{{ $item->name }}</h3>
            <p class="meta">{{ $item->category->name }} &middot; {{ $item->condition }} &middot; {{ $item->location }}</p>
            <span class="status status-{{ $item->status }}">{{ $statusLabel[$item->status] ?? $item->status }}</span>
            @if ($item->status === 'rejected' && $item->admin_note)
                <p class="note-box">Catatan admin: {{ $item->admin_note }}<br><small>Perbaiki barang lewat tombol Edit untuk diverifikasi ulang.</small></p>
            @endif
        </div>
        <div class="item-actions">
            @if ($item->status !== 'completed')
                <a class="btn btn-outline btn-small-dark" href="{{ route('donor.items.edit', $item) }}">Edit</a>
                <form method="POST" action="{{ route('donor.items.destroy', $item) }}" onsubmit="return confirm('Hapus barang ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-small-dark" type="submit">Hapus</button>
                </form>
            @endif
        </div>
    </div>
@empty
    <div class="card placeholder">
        <h3>Belum ada barang</h3>
        <p>Klik "Unggah Barang" untuk menambahkan barang pertama Anda.</p>
    </div>
@endforelse
@endsection
