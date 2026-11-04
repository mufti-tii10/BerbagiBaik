@extends('layouts.app')
@section('title', $fund->title . ' - BerbagiBaik')
@section('content')
@php
    use App\Models\FundRequest;
    $pct = $fund->percent($collected);
    $label = ['pending' => 'Menunggu verifikasi', 'published' => 'Tayang', 'rejected' => 'Ditolak', 'closed' => 'Ditutup', 'completed' => 'Tercapai'];
@endphp

<p><a href="{{ route('funds.index') }}">&larr; Kembali ke Donasi Dana</a></p>

@if ($fund->status !== 'published')
    <div class="alert alert-error">
        Pratinjau: kebutuhan ini berstatus <strong>{{ $label[$fund->status] ?? $fund->status }}</strong> dan belum tampil di halaman publik.
    </div>
@endif

<div class="detail">
    <div>
        @if ($fund->photo_url)
            <img src="{{ asset('storage/' . $fund->photo_url) }}" alt="{{ $fund->title }}">
        @else
            <div class="ph ph-big">Donasi Dana</div>
        @endif
        <div class="card" style="margin-top:18px">
            <h3 style="margin-top:0">Cerita kebutuhan</h3>
            <p style="margin:0">{!! nl2br(e($fund->description)) !!}</p>
        </div>
    </div>

    <div class="card">
        <h2 style="margin:0 0 6px">{{ $fund->title }}</h2>
        <p class="meta">Penerima: <strong>{{ $fund->receiver->name }}</strong></p>

        <p class="money">{{ FundRequest::rupiah($collected) }}</p>
        <p class="meta" style="margin-top:0">terkumpul dari target {{ FundRequest::rupiah($fund->target_amount) }}</p>
        <div class="progress"><div class="progress-bar" style="width: {{ $pct }}%"></div></div>
        <div class="fund-amounts" style="margin-top:6px">
            <span>{{ $pct }}% tercapai</span>
            <span>{{ $donorCount }} donatur</span>
        </div>
        <p class="meta">Batas waktu {{ $fund->deadline->format('d M Y') }} ({{ $fund->daysLeft() }} hari lagi)</p>

        <div class="action-box">
            @if ($fund->status !== 'published')
                <p class="subtitle" style="margin:0">Donasi belum dapat dilakukan.</p>
            @elseif (! auth()->check())
                <p>Ingin membantu? Masuk atau daftar sebagai donatur untuk berdonasi.</p>
                <div class="btn-row">
                    <a class="btn" href="{{ route('login') }}">Masuk</a>
                    <a class="btn btn-outline" href="{{ route('register') }}">Daftar</a>
                </div>
            @elseif (auth()->user()->isDonor())
                <button class="btn btn-block" type="button" disabled style="margin-top:0">Donasi Sekarang</button>
                <span class="tag">segera</span>
                <p class="hint">Fitur donasi (nominal dan bukti transfer) sedang disiapkan.</p>
            @else
                <p class="subtitle" style="margin:0">Hanya akun donatur yang dapat berdonasi.</p>
            @endif
        </div>

        @if ($canPreview)
            <div class="info-box">
                <strong>Rekening tujuan</strong> (hanya terlihat pemilik dan admin)<br>
                {{ $fund->bank_name }} &middot; {{ $fund->account_number }}<br>
                a.n. {{ $fund->account_name }}<br>
                <a href="{{ route('funds.document', $fund) }}" target="_blank" rel="noopener">Lihat dokumen pendukung</a>
            </div>
        @endif

        <p class="hint">BerbagiBaik tidak menampung dana. Transfer dilakukan langsung ke rekening penerima. Waspadai penipuan dan laporkan kebutuhan yang mencurigakan.</p>

        <h3 style="margin-bottom:6px">Donatur terkonfirmasi</h3>
        @forelse ($recent as $d)
            <p class="meta" style="margin:0 0 4px">
                <strong>{{ $d->is_anonymous ? 'Anonim' : $d->donor->name }}</strong> menyumbang {{ FundRequest::rupiah($d->amount) }}
                &middot; {{ $d->confirmed_at?->format('d M Y') }}
            </p>
        @empty
            <p class="meta" style="margin:0">Belum ada donasi terkonfirmasi.</p>
        @endforelse
    </div>
</div>
@endsection
