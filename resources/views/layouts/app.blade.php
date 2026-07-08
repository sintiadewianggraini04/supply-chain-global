<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', 'Global Supply Chain')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h1>Supply Chain Global</h1>
            <p>Risk Intelligence Platform</p>
        </div>

<nav class="sidebar-menu">
    <a
        href="{{ route('dashboard') }}"
        class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
    >
        Dashboard
    </a>

    <a
        href="{{ route('countries.index') }}"
        class="{{ request()->routeIs('countries.*') ? 'active' : '' }}"
    >
        Country Intelligence
    </a>

    <a href="#">
        Global Weather
    </a>

    <a href="#">
        Currency Impact
    </a>

    <a href="#">
        News Intelligence
    </a>

    <a href="#">
        Port Monitoring
    </a>

    <a href="#">
        Country Comparison
    </a>

    <a href="#">
        Watchlist
    </a>

    <a href="#">
        Admin
    </a>
</nav>
    </aside>

    <main class="main-content">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>