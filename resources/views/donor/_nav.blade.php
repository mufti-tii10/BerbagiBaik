<div class="subnav">
    <a class="{{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('donor.dashboard') }}">Ringkasan</a>
    <a class="{{ $active === 'items' ? 'active' : '' }}" href="{{ route('donor.items.index') }}">Barang Saya</a>
    <a class="{{ $active === 'requests' ? 'active' : '' }}" href="{{ route('donor.requests.index') }}">Pengajuan Masuk</a>
    <a class="{{ $active === 'history' ? 'active' : '' }}" href="{{ route('donor.history') }}">Riwayat Donasi</a>
</div>
