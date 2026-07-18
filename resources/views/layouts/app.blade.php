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

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body>
    {{-- Tombol sidebar pada tablet dan mobile --}}
    <button
        type="button"
        class="sidebar-toggle"
        id="sidebarToggle"
        aria-label="Buka menu navigasi"
        aria-controls="appSidebar"
        aria-expanded="false"
    >
        <svg
            viewBox="0 0 24 24"
            fill="none"
            aria-hidden="true"
        >
            <path
                d="M4 7H20M4 12H20M4 17H20"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
            />
        </svg>
    </button>

    <div
        class="sidebar-backdrop"
        id="sidebarBackdrop"
        aria-hidden="true"
    ></div>

    <aside
        class="sidebar"
        id="appSidebar"
    >
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    aria-hidden="true"
                >
                    <path
                        d="M7 7.5L12 4.5L17 7.5V13.5L12 16.5L7 13.5V7.5Z"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M7 7.5L12 10.5L17 7.5M12 10.5V16.5"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linejoin="round"
                    />

                    <path
                        d="M4 16.5L7 18.3M20 16.5L17 18.3"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>
            </div>

            <div class="sidebar-brand-copy">
                <h1>Supply Chain Global</h1>
                <p>Risk Intelligence Platform</p>
            </div>
        </div>

        <nav
            class="sidebar-menu"
            aria-label="Navigasi utama"
        >
            <div class="sidebar-menu-group">
                <span class="sidebar-section-label">
                    Overview
                </span>

                <a
                    href="{{ route('dashboard') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('dashboard')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('dashboard'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <rect
                                x="4"
                                y="4"
                                width="6"
                                height="6"
                                rx="1.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />

                            <rect
                                x="14"
                                y="4"
                                width="6"
                                height="6"
                                rx="1.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />

                            <rect
                                x="4"
                                y="14"
                                width="6"
                                height="6"
                                rx="1.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />

                            <rect
                                x="14"
                                y="14"
                                width="6"
                                height="6"
                                rx="1.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        Global Dashboard
                    </span>
                </a>

                <a
                    href="{{ route('countries.index') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('countries.*')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('countries.*'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <circle
                                cx="12"
                                cy="12"
                                r="8.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />

                            <path
                                d="M3.8 12H20.2M12 3.5C14.2 5.8 15.4 8.7 15.4 12C15.4 15.3 14.2 18.2 12 20.5C9.8 18.2 8.6 15.3 8.6 12C8.6 8.7 9.8 5.8 12 3.5Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        Country Intelligence
                    </span>
                </a>
            </div>

            <div class="sidebar-menu-group">
                <span class="sidebar-section-label">
                    Intelligence
                </span>

                <a
                    href="{{ route('weather.index') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('weather.*')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('weather.*'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M7.5 17.5H17C19.2 17.5 21 15.8 21 13.6C21 11.5 19.4 9.8 17.4 9.7C16.8 7.2 14.6 5.5 12 5.5C9.2 5.5 6.8 7.6 6.5 10.4C4.5 10.7 3 12.3 3 14.3C3 16.1 4.5 17.5 6.3 17.5H7.5Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M8 20L7.5 21M13 20L12.5 21M18 20L17.5 21"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        Global Weather
                    </span>
                </a>

                <a
                    href="{{ route('currency.index') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('currency.*')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('currency.*'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M5 7H18M15 4L18 7L15 10"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M19 17H6M9 14L6 17L9 20"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        Currency Impact
                    </span>
                </a>

                <a
                    href="{{ route('news.index') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('news.*')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('news.*'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M5 5H16V19H6.5C5.7 19 5 18.3 5 17.5V5Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M16 8H19V17.5C19 18.3 18.3 19 17.5 19H16V8Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M8 9H13M8 12H13M8 15H11"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        News Intelligence
                    </span>
                </a>

                <a
                    href="{{ route('articles.index') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('articles.*')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('articles.*'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M7 3.5H14L18 7.5V20.5H7V3.5Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M14 3.5V7.5H18M10 12H15M10 15.5H15"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        Artikel Analisis
                    </span>
                </a>
            </div>

            <div class="sidebar-menu-group">
                <span class="sidebar-section-label">
                    Monitoring
                </span>

                <a
                    href="{{ route('ports.index') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('ports.*')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('ports.*'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 3V16M8.5 7H15.5M6 12H18"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />

                            <path
                                d="M5 14C5 18 7.7 20.5 12 20.5C16.3 20.5 19 18 19 14M5 14L3.5 16M19 14L20.5 16"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <circle
                                cx="12"
                                cy="4.5"
                                r="1.5"
                                stroke="currentColor"
                                stroke-width="1.8"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        Port Monitoring
                    </span>
                </a>

                <a
                    href="{{ route('comparison.index') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('comparison.*')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('comparison.*'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M8 5L5 8L8 11M5 8H18"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />

                            <path
                                d="M16 19L19 16L16 13M19 16H6"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        Country Comparison
                    </span>
                </a>

                <a
                    href="{{ route('favorites.index') }}"
                    class="
                        sidebar-link
                        {{
                            request()->routeIs('favorites.*')
                                ? 'active'
                                : ''
                        }}
                    "
                    @if (request()->routeIs('favorites.*'))
                        aria-current="page"
                    @endif
                >
                    <span class="sidebar-link-icon">
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 3.8L14.5 8.9L20.1 9.7L16.1 13.6L17 19.2L12 16.6L7 19.2L7.9 13.6L3.9 9.7L9.5 8.9L12 3.8Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>

                    <span class="sidebar-link-text">
                        Favorite Monitoring
                    </span>
                </a>

                @if (auth()->user()?->isAdmin())
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="
                            sidebar-link
                            {{
                                request()->routeIs('admin.*')
                                    ? 'active'
                                    : ''
                            }}
                        "
                        @if (request()->routeIs('admin.*'))
                            aria-current="page"
                        @endif
                    >
                        <span class="sidebar-link-icon">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path
                                    d="M12 3L19 6V11.5C19 16 16.2 19.2 12 21C7.8 19.2 5 16 5 11.5V6L12 3Z"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linejoin="round"
                                />

                                <path
                                    d="M9.5 12L11.2 13.7L14.8 10"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </span>

                        <span class="sidebar-link-text">
                            Admin Panel
                        </span>
                    </a>
                @endif
            </div>
        </nav>

        @auth
            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-avatar">
                        {{
                            strtoupper(
                                substr(
                                    auth()->user()->name,
                                    0,
                                    1
                                )
                            )
                        }}
                    </div>

                    <div class="sidebar-user-info">
                        <strong>
                            {{ auth()->user()->name }}
                        </strong>

                        <span title="{{ auth()->user()->email }}">
                            {{ auth()->user()->email }}
                        </span>
                    </div>

                    @if (auth()->user()->isAdmin())
                        <span class="sidebar-role">
                            Admin
                        </span>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ route('logout') }}"
                >
                    @csrf

                    <button
                        type="submit"
                        class="sidebar-logout"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            aria-hidden="true"
                        >
                            <path
                                d="M10 5H6.5C5.7 5 5 5.7 5 6.5V17.5C5 18.3 5.7 19 6.5 19H10"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />

                            <path
                                d="M14 8L18 12L14 16M18 12H9"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        <span>Logout</span>
                    </button>
                </form>
            </div>
        @endauth
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

        @yield('content')
    </main>

    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {
                const sidebar =
                    document.getElementById(
                        'appSidebar'
                    );

                const toggle =
                    document.getElementById(
                        'sidebarToggle'
                    );

                const backdrop =
                    document.getElementById(
                        'sidebarBackdrop'
                    );

                if (
                    ! sidebar
                    || ! toggle
                    || ! backdrop
                ) {
                    return;
                }

                const closeSidebar = function () {
                    sidebar.classList.remove(
                        'is-open'
                    );

                    backdrop.classList.remove(
                        'is-visible'
                    );

                    document.body.classList.remove(
                        'sidebar-open'
                    );

                    toggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                };

                toggle.addEventListener(
                    'click',
                    function () {
                        const isOpen =
                            sidebar.classList.toggle(
                                'is-open'
                            );

                        backdrop.classList.toggle(
                            'is-visible',
                            isOpen
                        );

                        document.body.classList.toggle(
                            'sidebar-open',
                            isOpen
                        );

                        toggle.setAttribute(
                            'aria-expanded',
                            isOpen
                                ? 'true'
                                : 'false'
                        );
                    }
                );

                backdrop.addEventListener(
                    'click',
                    closeSidebar
                );

                sidebar
                    .querySelectorAll('a')
                    .forEach(function (link) {
                        link.addEventListener(
                            'click',
                            function () {
                                if (
                                    window.innerWidth
                                    <= 991
                                ) {
                                    closeSidebar();
                                }
                            }
                        );
                    });

                window.addEventListener(
                    'resize',
                    function () {
                        if (
                            window.innerWidth
                            > 991
                        ) {
                            closeSidebar();
                        }
                    }
                );
            }
        );
    </script>

    @stack('scripts')
</body>
</html>