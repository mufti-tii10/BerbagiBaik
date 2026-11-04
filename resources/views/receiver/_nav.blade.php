@php
    $pendingDonations = \App\Models\Donation::where('status', 'pending')
        ->whereHas('fundRequest', fn ($q) => $q->where('receiver_id', auth()->id()))->count();
@endphp
<div class="subnav">
    <a class="{{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('receiver.dashboard') }}">Ringkasan</a>
    <a href="{{ route('catalog.index') }}">Cari Barang</a>
    <a href="{{ route('funds.index') }}">Donasi Dana</a>
    <a class="{{ $active === 'requests' ? 'active' : '' }}" href="{{ route('receiver.requests.index') }}">Pengajuan Saya</a>
    <a class="{{ $active === 'funds' ? 'active' : '' }}" href="{{ route('receiver.funds.index') }}">Kebutuhan Dana Saya</a>
    <a class="{{ $active === 'donations' ? 'active' : '' }}" href="{{ route('receiver.donations.index') }}">
        Donasi Masuk @if ($pendingDonations > 0)<span class="dot">{{ $pendingDonations }}</span>@endif
    </a>
</div>
