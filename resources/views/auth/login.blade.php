@extends('layouts.guest')

@section(
    'title',
    'Login User - Supply Chain Global'
)

@section('content')
    <div class="mb-4">
        <h2 class="mb-2">
            Login User
        </h2>

        <p class="text-secondary mb-0">
            Masukkan email dan password akun
            yang telah diberikan administrator.
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
        action="{{ route('login.store') }}"
    >
        @csrf

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
                autofocus
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
                autocomplete="current-password"
                required
            >
        </div>

        <div class="form-check mb-4">
            <input
                type="checkbox"
                id="remember"
                name="remember"
                value="1"
                class="form-check-input"
            >

            <label
                for="remember"
                class="form-check-label"
            >
                Ingat saya
            </label>
        </div>

        <button
            type="submit"
            class="btn btn-primary w-100"
        >
            Login
        </button>
    </form>

    <div class="text-center mt-4">
        Administrator?

        <a href="{{ route('admin.login') }}">
            Login Admin
        </a>
    </div>
@endsection