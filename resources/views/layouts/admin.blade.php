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
        @yield('title', 'Admin Dashboard')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h1>Admin Panel</h1>
            <p>Supply Chain Global</p>
        </div>

        <nav class="sidebar-menu">
            <a
                href="{{ route('admin.dashboard') }}"
                class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            >
                Admin Overview
            </a>

            <a
                href="{{ route('admin.users.index') }}"
                class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
            >
                Kelola User
            </a>

            <a
                href="{{ route('admin.ports.index') }}"
                class="{{ request()->routeIs('admin.ports.*') ? 'active' : '' }}"
            >
                Dataset Pelabuhan
            </a>

            <a
                href="{{ route('admin.articles.index') }}"
                class="{{ request()->routeIs('admin.articles.*') ? 'active' : '' }}"
            >
                Artikel Analisis
            </a>

            <a href="{{ route('dashboard') }}">
                Kembali ke Website
            </a>
        </nav>

        <div
            class="mt-auto pt-4"
            style="
                border-top: 1px solid
                rgba(255, 255, 255, 0.15);
            "
        >
            <div class="mb-3">
                <strong>
                    {{ auth()->user()->name }}
                </strong>

                <br>

                <small>
                    {{ auth()->user()->email }}
                </small>
            </div>

            <form
                method="POST"
                action="{{ route('logout') }}"
            >
                @csrf

                <button
                    type="submit"
                    class="btn btn-outline-light btn-sm w-100"
                >
                    Logout Admin
                </button>
            </form>
        </div>
    </aside>

    <main class="main-content">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>