@extends('layouts.app')
@section('title', 'Donasi Dana Saya - BerbagiBaik')
@section('content')
@include('donor._nav', ['active' => 'donations'])
@php
    use App\Models\FundRequest;
    $label = ['pending' => 'Menunggu konfirmasi penerima', 'confirmed' => 'Terkonfirmasi', 'rejected' => 'Ditolak penerima', 'expired' => 'Kedaluwarsa'];
    $css = ['pending' => 'status-pending', 'confirmed' => 'status-published', 'rejected' => 'status-rejected', 'expired' => 'status-cancelled'];
@endphp

<div class="page-head">
    <div>
        <h2>Donasi Dana Saya</h2>
        <p class="subtitle">Total donasi terkonfirmasi: <strong>{{ FundRequest::rupiah($confirmedTotal) }}</strong></p>
    </div>
    <a class="btn btn-accent" href="{{ route('funds.index') }}">Lihat Kebutuhan Dana</a>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

@forelse ($donations as $d)
    <div class="card item-row">
        <div class="item-info">
            <h3><a href="{{ route('funds.show', $d->fundRequest) }}" style="text-decoration:none;color:inherit">{{ $d->fundRequest->title }}</a></h3>
            <p class="meta">Penerima: {{ $d->fundRequest->receiver->name }} &middot; {{ $d->created_at->format('d M Y H:i') }}</p>
            <p class="money" style="margin:4px 0 8px; font-size:1.3rem">{{ FundRequest::rupiah($d->amount) }}</p>
            <span class="status {{ $css[$d->status] ?? 'status-pending' }}">{{ $label[$d->status] ?? $d->status }}</span>
            @if ($d->is_anonymous) <span class="tag">anonim</span> @endif
            @if ($d->status === 'rejected' && $d->receiver_note)
                <p class="note-box">Alasan penerima: {{ $d->receiver_note }}<br><small>Jika Anda sudah mentransfer, admin dapat membantu meninjau.</small></p>
            @endif
            <p class="meta" style="margin-top:8px"><a href="{{ route('donations.proof', $d) }}" target="_blank" rel="noopener">Lihat bukti transfer</a></p>
        </div>
    </div>
@empty
    <div class="card placeholder">
        <h3>Belum ada donasi dana</h3>
        <p>Donasi Anda akan tampil di sini. <a href="{{ route('funds.index') }}">Lihat kebutuhan dana</a> untuk mulai berdonasi.</p>
    </div>
@endforelse
@endsection
