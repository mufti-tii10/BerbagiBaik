@extends('layouts.app')
@section('title', 'Pantau Donasi - Admin')
@section('content')
@include('admin._nav', ['active' => 'donations'])
@php
    use App\Models\FundRequest;
    $tabs = ['all' => 'Semua', 'pending' => 'Menunggu penerima', 'confirmed' => 'Terkonfirmasi', 'rejected' => 'Ditolak penerima'];
    $label = ['pending' => 'Menunggu konfirmasi penerima', 'confirmed' => 'Terkonfirmasi', 'rejected' => 'Ditolak penerima', 'expired' => 'Kedaluwarsa'];
    $css = ['pending' => 'status-pending', 'confirmed' => 'status-published', 'rejected' => 'status-rejected', 'expired' => 'status-cancelled'];
@endphp

<div class="page-head">
    <div>
        <h2>Pantau Donasi</h2>
        <p class="subtitle">Seluruh donasi uang. Halaman ini hanya untuk memantau. Perhatikan donasi yang ditolak penerima.</p>
    </div>
</div>

<div class="tabs">
    @foreach ($tabs as $key => $text)
        <a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.donations.index', ['status' => $key]) }}">
            {{ $text }} <span class="count">{{ $counts[$key] ?? 0 }}</span>
        </a>
    @endforeach
</div>

@forelse ($donations as $d)
    <div class="card item-row">
        <div class="item-info">
            <h3><a href="{{ route('funds.show', $d->fundRequest) }}" target="_blank" rel="noopener" style="text-decoration:none;color:inherit">{{ $d->fundRequest->title }}</a></h3>
            <p class="money" style="margin:2px 0 6px; font-size:1.3rem">{{ FundRequest::rupiah($d->amount) }}</p>
            <p class="meta"><strong>Donatur:</strong> {{ $d->donor->name }} &middot; WA {{ $d->donor->phone_wa }}@if ($d->is_anonymous) <span class="tag">anonim di publik</span>@endif</p>
            <p class="meta"><strong>Penerima:</strong> {{ $d->fundRequest->receiver->name }} &middot; WA {{ $d->fundRequest->receiver->phone_wa }}</p>
            <p class="meta">Dikirim {{ $d->created_at->format('d M Y H:i') }}@if ($d->confirmed_at) &middot; dikonfirmasi {{ $d->confirmed_at->format('d M Y H:i') }}@endif</p>
            <span class="status {{ $css[$d->status] ?? 'status-pending' }}">{{ $label[$d->status] ?? $d->status }}</span>
            @if ($d->status === 'rejected')
                <p class="note-box">Ditolak penerima. Alasan: {{ $d->receiver_note }}</p>
            @endif
            <p class="meta" style="margin-top:8px"><a href="{{ route('donations.proof', $d) }}" target="_blank" rel="noopener">Lihat bukti transfer</a></p>
        </div>
    </div>
@empty
    <div class="card placeholder">
        <h3>Tidak ada donasi</h3>
        <p>Belum ada donasi dengan status ini.</p>
    </div>
@endforelse
@endsection
