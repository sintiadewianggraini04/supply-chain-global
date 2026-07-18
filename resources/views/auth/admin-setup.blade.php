@extends('layouts.guest')

@section(
    'title',
    'Setup Admin - Supply Chain Global'
)

@section('content')
    <div class="mb-4">
        <span class="badge text-bg-warning mb-3">
            Initial Setup
        </span>

        <h2 class="mb-2">
            Buat Administrator Pertama
        </h2>

        <p class="text-secondary mb-0">
            Halaman ini hanya tersedia selama
            belum ada akun administrator.
        </p>
    </div>

    <div class="alert alert-info">
        Setelah administrator pertama dibuat,
        pendaftaran admin publik otomatis ditutup.
        Admin berikutnya harus dibuat melalui
        menu Kelola User.
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.setup.store') }}"
    >
        @csrf

        <div class="mb-3">
            <label
                for="adminName"
                class="form-label"
            >
                Nama Administrator
            </label>

            <input
                type="text"
                id="adminName"
                name="name"
                class="form-control"
                value="{{ old('name') }}"
                autocomplete="name"
                required
                autofocus
            >
        </div>

        <div class="mb-3">
            <label
                for="adminSetupEmail"
                class="form-label"
            >
                Email Administrator
            </label>

            <input
                type="email"
                id="adminSetupEmail"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                autocomplete="email"
                required
            >
        </div>

        <div class="mb-3">
            <label
                for="adminSetupPassword"
                class="form-label"
            >
                Password
            </label>

            <input
                type="password"
                id="adminSetupPassword"
                name="password"
                class="form-control"
                autocomplete="new-password"
                required
            >

            <small class="text-secondary">
                Minimal 8 karakter serta mengandung
                huruf dan angka.
            </small>
        </div>

        <div class="mb-4">
            <label
                for="adminPasswordConfirmation"
                class="form-label"
            >
                Konfirmasi Password
            </label>

            <input
                type="password"
                id="adminPasswordConfirmation"
                name="password_confirmation"
                class="form-control"
                autocomplete="new-password"
                required
            >
        </div>

        <button
            type="submit"
            class="btn btn-primary w-100"
        >
            Buat Administrator
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('admin.login') }}">
            Kembali ke Login Admin
        </a>
    </div>
@endsection