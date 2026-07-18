@extends('layouts.guest')

@section(
    'title',
    'Login Admin - Supply Chain Global'
)

@section('content')
    <div class="mb-4">
        <span class="badge text-bg-primary mb-3">
            Administrator
        </span>

        <h2 class="mb-2">
            Admin Login
        </h2>

        <p class="text-secondary mb-0">
            Masukkan email dan password
            administrator.
        </p>
    </div>

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
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.login.store') }}"
    >
        @csrf

        <div class="mb-3">
            <label
                for="adminEmail"
                class="form-label"
            >
                Email Admin
            </label>

            <input
                type="email"
                id="adminEmail"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                autocomplete="email"
                required
                autofocus
            >
        </div>

        <div class="mb-3">
            <label
                for="adminPassword"
                class="form-label"
            >
                Password
            </label>

            <input
                type="password"
                id="adminPassword"
                name="password"
                class="form-control"
                autocomplete="current-password"
                required
            >
        </div>

        <div class="form-check mb-4">
            <input
                type="checkbox"
                id="rememberAdmin"
                name="remember"
                value="1"
                class="form-check-input"
            >

            <label
                for="rememberAdmin"
                class="form-check-label"
            >
                Ingat saya
            </label>
        </div>

        <button
            type="submit"
            class="btn btn-primary w-100"
        >
            Login sebagai Admin
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('login') }}">
            Kembali ke Login User
        </a>
    </div>
@endsection