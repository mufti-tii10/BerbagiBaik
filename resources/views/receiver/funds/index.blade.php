@extends('layouts.app')
@section('title', 'Kebutuhan Dana Saya - BerbagiBaik')
@section('content')
@include('receiver._nav', ['active' => 'funds'])
@php
    use App\Models\FundRequest;
    $label = ['pending' => 'Menunggu verifikasi', 'published' => 'Tayang', 'rejected' => 'Ditolak', 'closed' => 'Ditutup', 'completed' => 'Tercapai'];
    $css = ['pending' => 'status-pending', 'published' => 'status-published', 'rejected' => 'status-rejected', 'closed' => 'status-cancelled', 'completed' => 'status-completed'];
@endphp

<div class="page-head">
    <div>
        <h2>Kebutuhan Dana Saya</h2>
        <p class="subtitle">Aktif {{ $activeCount }} dari {{ FundRequest::MAX_ACTIVE }} kebutuhan. Dana ditransfer langsung ke rekening Anda.</p>
    </div>
    <a class="btn btn-accent" href="{{ route('receiver.funds.create') }}">+ Ajukan Kebutuhan Dana</a>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if (session('error')) <div class="alert alert-error">{{ session('error') }}</div> @endif

@forelse ($funds as $f)
    @php $collected = (int) ($f->collected_sum ?? 0); $pct = $f->percent($collected); @endphp
    <div class="card item-row">
        @if ($f->photo_url)
            <img class="thumb" src="{{ asset('storage/' . $f->photo_url) }}" alt="{{ $f->title }}">
        @else
            <div class="thumb ph">Dana</div>
        @endif
        <div class="item-info">
            <h3><a href="{{ route('funds.show', $f) }}" style="text-decoration:none;color:inherit">{{ $f->title }}</a></h3>
            <p class="meta">Target {{ FundRequest::rupiah($f->target_amount) }} &middot; terkumpul {{ FundRequest::rupiah($collected) }} ({{ $pct }}%) &middot; batas {{ $f->deadline->format('d M Y') }}</p>
            <p class="meta">{{ $f->bank_name }} &middot; {{ $f->account_number }} a.n. {{ $f->account_name }}</p>
            <span class="status {{ $css[$f->status] ?? 'status-pending' }}">{{ $label[$f->status] ?? $f->status }}</span>
            @if ($f->status === 'rejected' && $f->admin_note)
                <p class="note-box">Catatan admin: {{ $f->admin_note }}<br><small>Perbaiki lewat tombol Edit untuk diverifikasi ulang.</small></p>
            @endif
        </div>
        <div class="item-actions admin-actions">
            <a class="btn btn-outline btn-small-dark" href="{{ route('funds.show', $f) }}">Lihat</a>
            @if (in_array($f->status, ['pending', 'rejected'], true))
                <a class="btn btn-outline btn-small-dark" href="{{ route('receiver.funds.edit', $f) }}">Edit</a>
                <form method="POST" action="{{ route('receiver.funds.destroy', $f) }}" onsubmit="return confirm('Hapus kebutuhan dana ini?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger btn-small-dark" type="submit">Hapus</button>
                </form>
            @elseif ($f->status === 'published')
                <form method="POST" action="{{ route('receiver.funds.close', $f) }}" onsubmit="return confirm('Tutup kebutuhan dana ini? Donasi baru tidak dapat diterima lagi.')">
                    @csrf
                    <button class="btn btn-danger btn-small-dark" type="submit">Tutup</button>
                </form>
            @endif
        </div>
    </div>
@empty
    <div class="card placeholder">
        <h3>Belum ada kebutuhan dana</h3>
        <p>Klik "Ajukan Kebutuhan Dana" untuk membuat yang pertama. Admin akan memverifikasinya sebelum tayang.</p>
    </div>
@endforelse
@endsection
