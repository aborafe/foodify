<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();

            return redirect()->route($employee?->role === 'cashier' ? 'admin.orders' : 'admin.dashboard');
        }

        return view('admin.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $remember = (bool) ($credentials['remember'] ?? false);

        if (! Auth::guard('employee')->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect or inactive.',
            ]);
        }

        $request->session()->regenerate();

        $employee = Auth::guard('employee')->user();

        return redirect()->intended(
            $employee?->role === 'cashier'
                ? route('admin.orders')
                : route('admin.dashboard')
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('employee')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
