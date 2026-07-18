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
        @yield('title', 'Supply Chain Global')
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: #eef3f9;
        }

        .guest-page {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .guest-card {
            width: 100%;
            max-width: 470px;
            padding: 34px;
            background: #ffffff;
            border: 1px solid #dde5ef;
            border-radius: 18px;
            box-shadow: 0 16px 45px rgba(15, 23, 42, 0.08);
        }

        .guest-brand {
            margin-bottom: 28px;
            text-align: center;
        }

        .guest-brand h1 {
            margin-bottom: 6px;
            font-size: 1.65rem;
            font-weight: 750;
            color: #13233a;
        }

        .guest-brand p {
            margin: 0;
            color: #64748b;
        }
    </style>

    @stack('styles')
</head>

<body>
    <main class="guest-page">
        <section class="guest-card">
            <div class="guest-brand">
                <h1>Supply Chain Global</h1>

                <p>Risk Intelligence Platform</p>
            </div>

            @yield('content')
        </section>
    </main>

    @stack('scripts')
</body>
</html>