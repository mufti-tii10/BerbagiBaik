<div class="subnav">
    <a class="{{ $active === 'dashboard' ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Ringkasan</a>
    <a class="{{ $active === 'items' ? 'active' : '' }}" href="{{ route('admin.items.index') }}">Verifikasi Barang</a>
    <a class="{{ $active === 'requests' ? 'active' : '' }}" href="{{ route('admin.requests.index') }}">Pantau Pengajuan</a>
    <a class="{{ $active === 'users' ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Pengguna</a>
    <a class="{{ $active === 'reports' ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">Laporan</a>
</div>
