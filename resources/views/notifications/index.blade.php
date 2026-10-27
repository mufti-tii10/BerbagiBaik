@extends('layouts.app')
@section('title', 'Notifikasi - BerbagiBaik')
@section('content')
<div class="page-head">
    <div>
        <h2>Notifikasi</h2>
        <p class="subtitle">Kabar terbaru tentang barang dan pengajuan Anda.</p>
    </div>
</div>

<div class="card notif-list">
    @forelse ($notifications as $n)
        <div class="notif {{ in_array($n->id, $unreadIds, true) ? 'notif-new' : '' }}">
            <p>{{ $n->message }}</p>
            <small>{{ $n->created_at->format('d M Y H:i') }}@if (in_array($n->id, $unreadIds, true)) &middot; <strong>baru</strong>@endif</small>
        </div>
    @empty
        <p class="subtitle" style="margin:0">Belum ada notifikasi.</p>
    @endforelse
</div>
@endsection
