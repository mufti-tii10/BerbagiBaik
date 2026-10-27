@extends('layouts.app')
@section('title', 'Cari Barang - BerbagiBaik')
@section('content')
<div class="page-head">
    <div>
        <h2>Cari Barang</h2>
        <p class="subtitle">Barang layak pakai yang sudah diverifikasi admin dan siap didonasikan.</p>
    </div>
</div>

<form method="GET" action="{{ route('catalog.index') }}" class="card filters">
    <div class="field">
        <label for="q">Kata kunci</label>
        <input id="q" name="q" value="{{ $filters['q'] }}" placeholder="Contoh: jaket, buku, kursi">
    </div>

    <div class="field">
        <label for="category">Kategori</label>
        <select id="category" name="category">
            <option value="">Semua kategori</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected($filters['category'] === (string) $cat->id)>{{ $cat->name }} ({{ $cat->available_count }})</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="location">Lokasi</label>
        <input id="location" name="location" list="location-list" value="{{ $filters['location'] }}" placeholder="Kota / kecamatan">
        <datalist id="location-list">
            @foreach ($locations as $loc)
                <option value="{{ $loc }}">
            @endforeach
        </datalist>
    </div>

    <div class="field">
        <label for="condition">Kondisi</label>
        <select id="condition" name="condition">
            <option value="">Semua kondisi</option>
            @foreach ($conditions as $c)
                <option value="{{ $c }}" @selected($filters['condition'] === $c)>{{ $c }}</option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="sort">Urutkan</label>
        <select id="sort" name="sort">
            <option value="newest" @selected($filters['sort'] === 'newest')>Terbaru</option>
            <option value="oldest" @selected($filters['sort'] === 'oldest')>Terlama</option>
            <option value="name" @selected($filters['sort'] === 'name')>Nama A-Z</option>
        </select>
    </div>

    <div class="btn-row">
        <button class="btn" type="submit">Cari</button>
        <a class="btn btn-outline" href="{{ route('catalog.index') }}">Reset</a>
    </div>
</form>

<p class="subtitle">{{ $items->total() }} barang ditemukan</p>

@if ($items->count())
    <div class="catalog-grid">
        @foreach ($items as $item)
            <a class="card item-card" href="{{ route('catalog.show', $item) }}">
                <img src="{{ asset('storage/' . $item->photo_url) }}" alt="{{ $item->name }}" loading="lazy">
                <div class="body">
                    <span class="badge-soft">{{ $item->category->name }}</span>
                    <h3>{{ $item->name }}</h3>
                    <p class="meta">{{ $item->condition }} &middot; {{ $item->location }}</p>
                    <p class="meta">
                        Donatur: {{ $item->donor->name }}
                        @if ($item->donor->isTrustedDonor()) <span class="badge badge-star">&#9733; Terpercaya</span> @endif
                    </p>
                </div>
            </a>
        @endforeach
    </div>

    @if ($items->lastPage() > 1)
        <div class="pager">
            @if ($items->onFirstPage())
                <span class="disabled">&laquo; Sebelumnya</span>
            @else
                <a href="{{ $items->previousPageUrl() }}">&laquo; Sebelumnya</a>
            @endif
            <span>Halaman {{ $items->currentPage() }} dari {{ $items->lastPage() }}</span>
            @if ($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}">Berikutnya &raquo;</a>
            @else
                <span class="disabled">Berikutnya &raquo;</span>
            @endif
        </div>
    @endif
@else
    <div class="card placeholder">
        <h3>Tidak ada barang yang cocok</h3>
        <p>Coba ubah kata kunci atau filter, atau <a href="{{ route('catalog.index') }}">tampilkan semua barang</a>.</p>
    </div>
@endif
@endsection
