<div class="subnav">
    <a class="{{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('receiver.dashboard') }}">Ringkasan</a>
    <a href="{{ route('catalog.index') }}">Cari Barang</a>
    <a href="{{ route('funds.index') }}">Donasi Dana</a>
    <a class="{{ $active === 'requests' ? 'active' : '' }}" href="{{ route('receiver.requests.index') }}">Pengajuan Saya</a>
    <a class="{{ $active === 'funds' ? 'active' : '' }}" href="{{ route('receiver.funds.index') }}">Kebutuhan Dana Saya</a>
</div>
