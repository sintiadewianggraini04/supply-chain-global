@extends('layouts.guest')

@section(
    'title',
    'Daftar User - Supply Chain Global'
)

@section('content')
    <div class="mb-4">
        <h2 class="mb-2">
            Daftar User
        </h2>

        <p class="text-secondary mb-0">
            Buat akun untuk mengakses dashboard
            pemantauan supply chain.
        </p>
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
        action="{{ route('register.store') }}"
    >
        @csrf

        <div class="mb-3">
            <label
                for="name"
                class="form-label"
            >
                Nama Lengkap
            </label>

            <input
                type="text"
                id="name"
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
                for="email"
                class="form-label"
            >
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                autocomplete="email"
                required
            >
        </div>

        <div class="mb-3">
            <label
                for="password"
                class="form-label"
            >
                Password
            </label>

            <input
                type="password"
                id="password"
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
                for="password_confirmation"
                class="form-label"
            >
                Konfirmasi Password
            </label>

            <input
                type="password"
                id="password_confirmation"
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
            Buat Akun User
        </button>
    </form>

    <div class="text-center mt-4">
        Sudah memiliki akun?

        <a href="{{ route('login') }}">
            Kembali ke Login
        </a>
    </div>
@endsection