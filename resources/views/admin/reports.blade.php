@extends('layouts.app')
@section('title', 'Laporan - Admin')
@section('content')
@include('admin._nav', ['active' => 'reports'])
@php
    $tabs = ['open' => 'Belum ditangani', 'handled' => 'Selesai', 'all' => 'Semua'];
@endphp

<div class="page-head">
    <div>
        <h2>Laporan</h2>
        <p class="subtitle">Tindak lanjuti laporan barang atau akun mencurigakan.</p>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="tabs">
    @foreach ($tabs as $key => $label)
        <a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.reports.index', ['status' => $key]) }}">{{ $label }}</a>
    @endforeach
</div>

@forelse ($reports as $report)
    <div class="card report-card">
        <div class="item-info">
            <p class="meta">
                Dilaporkan oleh <strong>{{ $report->reporter->name }}</strong>
                &middot; {{ $report->created_at->format('d M Y H:i') }}
            </p>
            <p class="desc"><strong>Alasan:</strong> {{ $report->reason }}</p>
            @if ($report->item)
                <p class="meta">
                    Barang: <strong>{{ $report->item->name }}</strong>
                    (donatur: {{ $report->item->donor->name }}, status: {{ $report->item->status }})
                </p>
            @else
                <p class="meta">Barang sudah dihapus.</p>
            @endif
            <span class="status {{ $report->status === 'open' ? 'status-pending' : 'status-published' }}">
                {{ $report->status === 'open' ? 'Belum ditangani' : 'Selesai' }}
            </span>
        </div>

        @if ($report->status === 'open')
            <div class="item-actions admin-actions">
                @if ($report->item && ! in_array($report->item->status, ['rejected', 'completed'], true))
                    <form method="POST" action="{{ route('admin.reports.takedown', $report) }}" onsubmit="return confirm('Turunkan barang ini dari publik?')">
                        @csrf
                        <button class="btn btn-danger btn-small-dark" type="submit">Turunkan barang</button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.reports.handle', $report) }}">
                    @csrf
                    <button class="btn btn-success btn-small-dark" type="submit">Tandai selesai</button>
                </form>
            </div>
        @endif
    </div>
@empty
    <div class="card placeholder">
        <h3>Tidak ada laporan</h3>
        <p>Belum ada laporan dengan status ini.</p>
    </div>
@endforelse
@endsection
