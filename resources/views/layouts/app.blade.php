<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'BerbagiBaik')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="navbar">
        <div class="navbar-inner">
            <a class="brand" href="{{ route('home') }}">Berbagi<span>Baik</span></a>
            @auth
                <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                <div class="nav-user">
                    <span>{{ auth()->user()->name }}</span>
                    <span class="badge">{{ ['donor' => 'Donatur', 'receiver' => 'Penerima', 'admin' => 'Admin'][auth()->user()->role] ?? auth()->user()->role }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-small" type="submit">Keluar</button>
                </form>
            @else
                <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                <a class="btn btn-accent" href="{{ route('register') }}">Daftar</a>
            @endauth
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="footer">&copy; {{ date('Y') }} BerbagiBaik &middot; Berbagi barang layak pakai, tepat sasaran.</footer>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
