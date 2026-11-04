@extends('layouts.app')
@section('title', 'Verifikasi Dana - Admin')
@section('content')
@include('admin._nav', ['active' => 'funds'])
@php
    use App\Models\FundRequest;
    $tabs = ['pending' => 'Menunggu', 'published' => 'Tayang', 'rejected' => 'Ditolak', 'closed' => 'Ditutup', 'all' => 'Semua'];
    $label = ['pending' => 'Menunggu verifikasi', 'published' => 'Tayang', 'rejected' => 'Ditolak', 'closed' => 'Ditutup', 'completed' => 'Tercapai'];
    $css = ['pending' => 'status-pending', 'published' => 'status-published', 'rejected' => 'status-rejected', 'closed' => 'status-cancelled', 'completed' => 'status-completed'];
@endphp

<div class="page-head">
    <div>
        <h2>Verifikasi Kebutuhan Dana</h2>
        <p class="subtitle">Periksa dokumen pendukung dan cocokkan nama pemilik rekening dengan nama penerima sebelum menyetujui.</p>
    </div>
</div>

@if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if ($errors->any()) <div class="alert alert-error">{{ $errors->first() }}</div> @endif

<div class="tabs">
    @foreach ($tabs as $key => $text)
        <a class="tab {{ $status === $key ? 'active' : '' }}" href="{{ route('admin.funds.index', ['status' => $key]) }}">
            {{ $text }}@if ($key !== 'all') <span class="count">{{ $counts[$key] ?? 0 }}</span>@endif
        </a>
    @endforeach
</div>

@forelse ($funds as $f)
    @php
        $collected = (int) ($f->collected_sum ?? 0);
        $sameName = mb_strtolower(trim($f->account_name)) === mb_strtolower(trim($f->receiver->name));
    @endphp
    <div class="card item-row">
        <div class="item-info">
            <h3>{{ $f->title }}</h3>
            <p class="meta">Penerima: <strong>{{ $f->receiver->name }}</strong> &middot; WA {{ $f->receiver->phone_wa }}</p>
            <p class="meta">Target {{ FundRequest::rupiah($f->target_amount) }} &middot; terkumpul {{ FundRequest::rupiah($collected) }} &middot; batas {{ $f->deadline->format('d M Y') }}</p>
            <p class="desc">{{ \Illuminate\Support\Str::limit($f->description, 220) }}</p>

            <div class="info-box" style="margin-top:6px">
                <strong>Rekening:</strong> {{ $f->bank_name }} &middot; {{ $f->account_number }} a.n. {{ $f->account_name }}<br>
                @if ($sameName)
                    <span style="color:#166534">Nama rekening sama dengan nama penerima.</span>
                @else
                    <span style="color:#991b1b">Nama rekening berbeda dari nama penerima ({{ $f->receiver->name }}). Periksa dokumen dengan teliti.</span>
                @endif
                <br><a href="{{ route('funds.document', $f) }}" target="_blank" rel="noopener">Lihat dokumen pendukung</a>
                &middot; <a href="{{ route('funds.show', $f) }}" target="_blank" rel="noopener">Lihat halaman kebutuhan</a>
            </div>

            <span class="status {{ $css[$f->status] ?? 'status-pending' }}" style="margin-top:8px">{{ $label[$f->status] ?? $f->status }}</span>
            @if ($f->admin_note) <p class="note-box">Catatan admin: {{ $f->admin_note }}</p> @endif
        </div>

        <div class="item-actions admin-actions">
            @if ($f->status === 'pending')
                <form method="POST" action="{{ route('admin.funds.approve', $f) }}">
                    @csrf
                    <button class="btn btn-success btn-small-dark" type="submit">Setujui</button>
                </form>
            @endif
            @if (in_array($f->status, ['pending', 'published'], true))
                <details class="reject-box">
                    <summary class="btn btn-danger btn-small-dark">Tolak</summary>
                    <form method="POST" action="{{ route('admin.funds.reject', $f) }}">
                        @csrf
                        <textarea name="admin_note" rows="3" placeholder="Alasan penolakan atau saran perbaikan" required></textarea>
                        <button class="btn btn-danger btn-small-dark" type="submit">Kirim penolakan</button>
                    </form>
                </details>
            @endif
        </div>
    </div>
@empty
    <div class="card placeholder">
        <h3>Tidak ada kebutuhan dana</h3>
        <p>Belum ada kebutuhan dengan status ini.</p>
    </div>
@endforelse
@endsection
