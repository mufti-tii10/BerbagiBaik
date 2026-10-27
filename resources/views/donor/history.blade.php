@extends('layouts.app')
@section('title', 'Riwayat Donasi - BerbagiBaik')
@section('content')
@include('donor._nav', ['active' => 'history'])

<div class="page-head">
    <div>
        <h2>Riwayat Donasi</h2>
        <p class="subtitle">Donasi Anda yang sudah diterima penerima.</p>
    </div>
</div>

@forelse ($requests as $r)
    <div class="card item-row">
        <img class="thumb" src="{{ asset('storage/' . $r->item->photo_url) }}" alt="{{ $r->item->name }}">
        <div class="item-info">
            <h3>{{ $r->item->name }}</h3>
            <p class="meta">{{ $r->item->category->name }} &middot; diberikan kepada <strong>{{ $r->receiver->name }}</strong></p>
            <p class="meta">
                @if ($r->confirmed_at) Dikonfirmasi {{ $r->confirmed_at->format('d M Y H:i') }}
                @else Diperbarui {{ $r->updated_at->format('d M Y H:i') }} @endif
            </p>
            <span class="status status-published">{{ $r->status === 'completed' ? 'Selesai' : 'Diterima' }}</span>
        </div>
    </div>
@empty
    <div class="card placeholder">
        <h3>Belum ada riwayat</h3>
        <p>Donasi yang sudah dikonfirmasi penerima akan tampil di sini.</p>
    </div>
@endforelse
@endsection
