@extends('layouts.app')
@section('title', 'Donasi Dana - BerbagiBaik')
@section('content')
@php use App\Models\FundRequest; @endphp
<div class="page-head">
    <div>
        <h2>Donasi Dana</h2>
        <p class="subtitle">Kebutuhan dana yang sudah diverifikasi admin. BerbagiBaik tidak menampung dana: transfer dilakukan langsung ke penerima.</p>
    </div>
</div>

<form method="GET" action="{{ route('funds.index') }}" class="filter-bar">
    <input name="q" value="{{ $q }}" placeholder="Cari judul atau cerita kebutuhan">
    <select name="sort">
        <option value="newest" @selected($sort === 'newest')>Terbaru</option>
        <option value="deadline" @selected($sort === 'deadline')>Segera berakhir</option>
    </select>
    <button class="btn" type="submit">Cari</button>
</form>

<p class="subtitle">{{ $funds->total() }} kebutuhan dana ditemukan</p>

@if ($funds->count())
    <div class="catalog-grid">
        @foreach ($funds as $fund)
            @php
                $collected = (int) ($fund->collected_sum ?? 0);
                $pct = $fund->percent($collected);
            @endphp
            <a class="card item-card" href="{{ route('funds.show', $fund) }}">
                @if ($fund->photo_url)
                    <img src="{{ asset('storage/' . $fund->photo_url) }}" alt="{{ $fund->title }}" loading="lazy">
                @else
                    <div class="ph">Donasi Dana</div>
                @endif
                <div class="body">
                    <h3>{{ $fund->title }}</h3>
                    <p class="meta">Penerima: {{ $fund->receiver->name }}</p>
                    <div class="progress"><div class="progress-bar" style="width: {{ $pct }}%"></div></div>
                    <div class="fund-amounts">
                        <span><strong>{{ FundRequest::rupiah($collected) }}</strong> terkumpul</span>
                        <span>{{ $pct }}%</span>
                    </div>
                    <p class="meta">Target {{ FundRequest::rupiah($fund->target_amount) }} &middot; {{ $fund->daysLeft() }} hari lagi</p>
                </div>
            </a>
        @endforeach
    </div>

    @if ($funds->lastPage() > 1)
        <div class="pager">
            @if ($funds->onFirstPage()) <span class="disabled">&laquo; Sebelumnya</span>
            @else <a href="{{ $funds->previousPageUrl() }}">&laquo; Sebelumnya</a> @endif
            <span>Halaman {{ $funds->currentPage() }} dari {{ $funds->lastPage() }}</span>
            @if ($funds->hasMorePages()) <a href="{{ $funds->nextPageUrl() }}">Berikutnya &raquo;</a>
            @else <span class="disabled">Berikutnya &raquo;</span> @endif
        </div>
    @endif
@else
    <div class="card placeholder">
        <h3>Belum ada kebutuhan dana</h3>
        <p>Kebutuhan dana yang sudah disetujui admin akan tampil di sini.</p>
    </div>
@endif
@endsection
