<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthWebController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.index');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');
        $loginField = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $attempt = Auth::attempt([
            $loginField => $credentials['login'],
            'password' => $credentials['password'],
            'status' => 'active',
        ], $remember);

        if (! $attempt) {
            throw ValidationException::withMessages([
                'login' => 'Email/Username atau password salah, atau akun belum aktif.',
            ]);
        }

        $user = Auth::user();
        if ($user && $user->hasRole(['kitchen', 'kitchen_head', 'kitchen_staff']) && ! $user->hasRole(['owner', 'admin'])) {
            Auth::logout();

            throw ValidationException::withMessages([
                'login' => 'Akun tidak diizinkan mengakses Portal Dashboard.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
