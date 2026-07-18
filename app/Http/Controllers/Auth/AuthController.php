<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showUserLogin(): View
    {
        return view('auth.login');
    }

    public function showAdminLogin(): View
    {
        return view('auth.admin-login');
    }

    public function userLogin(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $authenticated = Auth::attempt(
            [
                'email' => strtolower(
                    trim($validated['email'])
                ),

                'password' => $validated['password'],

                'is_active' => true,
            ],

            $request->boolean('remember')
        );

        if (! $authenticated) {
            return back()
                ->withInput(
                    $request->only('email')
                )
                ->withErrors([
                    'email' =>
                        'Email, password, atau status akun tidak valid.',
                ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->isAdmin()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput(
                    $request->only('email')
                )
                ->withErrors([
                    'email' =>
                        'Akun administrator harus masuk melalui Login Admin.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard'));
    }

    public function adminLogin(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'password' => [
                'required',
                'string',
            ],
        ]);

        $authenticated = Auth::attempt(
            [
                'email' => strtolower(
                    trim($validated['email'])
                ),

                'password' => $validated['password'],

                'is_active' => true,
            ],

            $request->boolean('remember')
        );

        if (! $authenticated) {
            return back()
                ->withInput(
                    $request->only('email')
                )
                ->withErrors([
                    'email' =>
                        'Email, password, atau status akun tidak valid.',
                ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isAdmin()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput(
                    $request->only('email')
                )
                ->withErrors([
                    'email' =>
                        'Akun tersebut bukan akun administrator.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(
                route('admin.dashboard')
            );
    }

    public function logout(
        Request $request
    ): RedirectResponse {
        $wasAdmin = $request->user()
            ?->isAdmin() ?? false;

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route(
                $wasAdmin
                    ? 'admin.login'
                    : 'login'
            )
            ->with(
                'success',
                'Anda berhasil logout.'
            );
    }
}