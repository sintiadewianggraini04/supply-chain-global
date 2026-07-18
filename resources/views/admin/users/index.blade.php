@extends('layouts.admin')

@section(
    'title',
    'Kelola User - Admin'
)

@section('content')
    <div class="topbar">
        <div>
            <h2>Kelola User</h2>

            <p>
                Tambah, ubah, aktifkan,
                nonaktifkan, dan hapus user.
            </p>
        </div>

        <span class="badge text-bg-primary fs-6">
            {{ $users->total() }} Users
        </span>
    </div>

    <section class="dashboard-card mb-4">
        <h3 class="mb-3">
            {{ $editingUser
                ? 'Edit User'
                : 'Tambah User'
            }}
        </h3>

        <form
            method="POST"
            action="{{
                $editingUser
                    ? route(
                        'admin.users.update',
                        $editingUser
                    )
                    : route('admin.users.store')
            }}"
        >
            @csrf

            @if ($editingUser)
                @method('PATCH')
            @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">
                        Nama
                    </label>

                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old(
                            'name',
                            $editingUser?->name
                        ) }}"
                        required
                    >
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        value="{{ old(
                            'email',
                            $editingUser?->email
                        ) }}"
                        required
                    >
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Password
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        {{ $editingUser ? '' : 'required' }}
                    >

                    @if ($editingUser)
                        <small class="text-secondary">
                            Kosongkan jika tidak diganti.
                        </small>
                    @endif
                </div>

                <div class="col-md-4">
                    <label class="form-label">
                        Role
                    </label>

                    <select
                        name="role"
                        class="form-select"
                        required
                    >
                        <option
                            value="user"
                            @selected(
                                old(
                                    'role',
                                    $editingUser?->role
                                ) === 'user'
                            )
                        >
                            User
                        </option>

                        <option
                            value="admin"
                            @selected(
                                old(
                                    'role',
                                    $editingUser?->role
                                ) === 'admin'
                            )
                        >
                            Admin
                        </option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label d-block">
                        Status
                    </label>

                    <div class="form-check mt-2">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="form-check-input"
                            id="isActive"
                            @checked(
                                old(
                                    'is_active',
                                    $editingUser?->is_active
                                        ?? true
                                )
                            )
                        >

                        <label
                            class="form-check-label"
                            for="isActive"
                        >
                            Akun aktif
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        {{ $editingUser
                            ? 'Simpan Perubahan'
                            : 'Tambah User'
                        }}
                    </button>

                    @if ($editingUser)
                        <a
                            href="{{ route('admin.users.index') }}"
                            class="btn btn-outline-secondary"
                        >
                            Batal
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <section class="dashboard-card">
        <form
            method="GET"
            class="row g-2 mb-4"
        >
            <div class="col-md-10">
                <input
                    type="search"
                    name="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Cari nama atau email user"
                >
            </div>

            <div class="col-md-2">
                <button
                    class="btn btn-outline-primary w-100"
                >
                    Cari
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
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
                                {{ ucfirst($user->role) }}
                            </td>

                            <td>
                                <span
                                    class="badge {{
                                        $user->is_active
                                            ? 'text-bg-success'
                                            : 'text-bg-secondary'
                                    }}"
                                >
                                    {{ $user->is_active
                                        ? 'Active'
                                        : 'Inactive'
                                    }}
                                </span>
                            </td>

                            <td>
                                {{ $user->created_at
                                    ->format('d M Y')
                                }}
                            </td>

                            <td>
                                <div class="d-flex gap-2">
                                    <a
                                        href="{{ route(
                                            'admin.users.index',
                                            [
                                                'edit' => $user->id,
                                                'search' => $search,
                                            ]
                                        ) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >
                                        Edit
                                    </a>

                                    @if (! $user->is(auth()->user()))
                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.users.destroy',
                                                $user
                                            ) }}"
                                            onsubmit="
                                                return confirm(
                                                    'Hapus user ini?'
                                                );
                                            "
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                class="btn btn-sm btn-danger"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="text-center text-secondary"
                            >
                                Data user tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $users->links() }}
    </section>
@endsection