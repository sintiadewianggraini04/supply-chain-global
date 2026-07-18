<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->input('search')
        );

        $users = User::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($subQuery) use ($search) {
                            $subQuery
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $editingUser = null;

        if ($request->filled('edit')) {
            $editingUser = User::query()
                ->find($request->integer('edit'));
        }

        return view('admin.users.index', [
            'users' => $users,
            'editingUser' => $editingUser,
            'search' => $search,
        ]);
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
            ],

            'role' => [
                'required',
                Rule::in(['admin', 'user']),
            ],
        ]);

        User::query()->create([
            'name' => $validated['name'],
            'email' => strtolower(
                $validated['email']
            ),
            'password' => $validated['password'],
            'role' => $validated['role'],
            'is_active' =>
                $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User berhasil ditambahkan.'
            );
    }

    public function update(
        Request $request,
        User $user
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',

                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
            ],

            'role' => [
                'required',
                Rule::in(['admin', 'user']),
            ],
        ]);

        if ($user->is(auth()->user())) {
            if (
                $validated['role'] !== 'admin'
                || ! $request->boolean('is_active')
            ) {
                return back()->with(
                    'error',
                    'Akun admin yang sedang digunakan tidak dapat dinonaktifkan atau diubah menjadi user.'
                );
            }
        }

        $user->name = $validated['name'];
        $user->email = strtolower(
            $validated['email']
        );
        $user->role = $validated['role'];
        $user->is_active =
            $request->boolean('is_active');

        if (! empty($validated['password'])) {
            $user->password =
                $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'Data user berhasil diperbarui.'
            );
    }

    public function destroy(
        User $user
    ): RedirectResponse {
        if ($user->is(auth()->user())) {
            return back()->with(
                'error',
                'Akun admin yang sedang digunakan tidak dapat dihapus.'
            );
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                'User berhasil dihapus.'
            );
    }
}