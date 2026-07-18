@extends('layouts.admin')

@section(
    'title',
    'Admin Dashboard - Supply Chain Global'
)

@section('content')
    <div class="topbar">
        <div>
            <h2>Admin Dashboard</h2>

            <p>
                Kelola user, dataset pelabuhan,
                dan artikel analisis.
            </p>
        </div>

        <div>
            <span class="badge text-bg-primary fs-6">
                Administrator
            </span>
        </div>
    </div>

    {{-- Kartu ringkasan --}}
    <section class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card h-100">
                <span class="text-secondary">
                    Total Users
                </span>

                <h2 class="mt-2 mb-1">
                    {{ number_format($totalUsers) }}
                </h2>

                <small class="text-secondary">
                    {{ number_format($activeUsers) }}
                    akun aktif
                </small>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card h-100">
                <span class="text-secondary">
                    Administrators
                </span>

                <h2 class="mt-2 mb-1">
                    {{ number_format($adminUsers) }}
                </h2>

                <small class="text-secondary">
                    Akun dengan akses admin
                </small>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card h-100">
                <span class="text-secondary">
                    Port Dataset
                </span>

                <h2 class="mt-2 mb-1">
                    {{ number_format($totalPorts) }}
                </h2>

                <small class="text-secondary">
                    Pelabuhan tersimpan
                </small>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="dashboard-card h-100">
                <span class="text-secondary">
                    Analysis Articles
                </span>

                <h2 class="mt-2 mb-1">
                    {{ number_format($totalArticles) }}
                </h2>

                <small class="text-secondary">
                    {{ number_format($publishedArticles) }}
                    published,
                    {{ number_format($draftArticles) }}
                    draft
                </small>
            </div>
        </div>
    </section>

    {{-- Tombol menu pengelolaan --}}
    <section class="dashboard-card mb-4">
        <div class="mb-3">
            <h3 class="mb-1">
                Administrative Management
            </h3>

            <p class="text-secondary mb-0">
                Pilih bagian yang ingin dikelola.
            </p>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <a
                    href="{{ route('admin.users.index') }}"
                    class="btn btn-primary w-100"
                >
                    Kelola User
                </a>
            </div>

            <div class="col-md-4">
                <a
                    href="{{ route('admin.ports.index') }}"
                    class="btn btn-primary w-100"
                >
                    Kelola Dataset Pelabuhan
                </a>
            </div>

            <div class="col-md-4">
                <a
                    href="{{ route('admin.articles.index') }}"
                    class="btn btn-primary w-100"
                >
                    Kelola Artikel Analisis
                </a>
            </div>
        </div>
    </section>

    {{-- Data terbaru --}}
    <section class="row g-4">
        <div class="col-xl-6">
            <div class="dashboard-card h-100">
                <div class="mb-3">
                    <h3 class="mb-1">
                        Recent Users
                    </h3>

                    <p class="text-secondary mb-0">
                        Lima akun terbaru.
                    </p>
                </div>

                @if ($recentUsers->isEmpty())
                    <div class="alert alert-info mb-0">
                        Belum ada data user.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($recentUsers as $user)
                                    <tr>
                                        <td>
                                            <strong>
                                                {{ $user->name }}
                                            </strong>

                                            <br>

                                            <small class="text-secondary">
                                                {{ $user->email }}
                                            </small>
                                        </td>

                                        <td>
                                            <span
                                                class="badge {{
                                                    $user->role === 'admin'
                                                        ? 'text-bg-primary'
                                                        : 'text-bg-light'
                                                }}"
                                            >
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>

                                        <td>
                                            @if ($user->is_active)
                                                <span
                                                    class="badge text-bg-success"
                                                >
                                                    Active
                                                </span>
                                            @else
                                                <span
                                                    class="badge text-bg-secondary"
                                                >
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a
                            href="{{ route('admin.users.index') }}"
                            class="btn btn-sm btn-outline-primary"
                        >
                            Lihat Semua User
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-xl-6">
            <div class="dashboard-card h-100">
                <div class="mb-3">
                    <h3 class="mb-1">
                        Recent Ports
                    </h3>

                    <p class="text-secondary mb-0">
                        Lima data pelabuhan terbaru.
                    </p>
                </div>

                @if ($recentPorts->isEmpty())
                    <div class="alert alert-info mb-0">
                        Belum ada data pelabuhan.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr>
                                    <th>Port</th>
                                    <th>Country</th>
                                    <th>Risk</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($recentPorts as $port)
                                    <tr>
                                        <td>
                                            <strong>
                                                {{ $port->name }}
                                            </strong>

                                            <br>

                                            <small class="text-secondary">
                                                {{ $port->port_code ?? '-' }}
                                            </small>
                                        </td>

                                        <td>
                                            {{ $port->country_name ?? '-' }}
                                        </td>

                                        <td>
                                            @php
                                                $riskClass = match (
                                                    $port->risk_level
                                                ) {
                                                    'low' =>
                                                        'text-bg-success',

                                                    'high' =>
                                                        'text-bg-danger',

                                                    default =>
                                                        'text-bg-warning',
                                                };
                                            @endphp

                                            <span
                                                class="badge {{ $riskClass }}"
                                            >
                                                {{ ucfirst(
                                                    $port->risk_level
                                                    ?? 'unknown'
                                                ) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a
                            href="{{ route('admin.ports.index') }}"
                            class="btn btn-sm btn-outline-primary"
                        >
                            Lihat Semua Pelabuhan
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection