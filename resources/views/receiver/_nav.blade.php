<div class="subnav">
    <a class="{{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('receiver.dashboard') }}">Ringkasan</a>
    <a href="{{ route('catalog.index') }}">Cari Barang</a>
    <a class="{{ $active === 'requests' ? 'active' : '' }}" href="{{ route('receiver.requests.index') }}">Pengajuan Saya</a>
</div>
