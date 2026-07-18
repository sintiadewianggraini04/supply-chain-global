<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if ($user === null) {
            return redirect()
                ->route('admin.login')
                ->with(
                    'error',
                    'Silakan login sebagai administrator.'
                );
        }

        if (! $user->is_active) {
            auth()->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->with(
                    'error',
                    'Akun administrator sedang dinonaktifkan.'
                );
        }

        if (! $user->isAdmin()) {
            abort(
                403,
                'Akun ini tidak memiliki akses administrator.'
            );
        }

        return $next($request);
    }
}