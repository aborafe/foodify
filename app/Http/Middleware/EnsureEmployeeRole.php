<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response|RedirectResponse
    {
        $employee = $request->user('employee');

        if (! $employee) {
            return redirect()->route('admin.login');
        }

        if (! $employee->is_active) {
            auth('employee')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->withErrors(['email' => 'Your employee account is inactive.']);
        }

        if ($roles !== [] && ! in_array($employee->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
