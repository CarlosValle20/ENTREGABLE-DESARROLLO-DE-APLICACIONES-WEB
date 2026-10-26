<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.ingresar');
    }

    public function showRegisterForm(): View
    {
        return view('auth.registrarse');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->email))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'dni' => ['required', 'regex:/^[0-9]{8}$/', 'unique:users,dni'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'string', 'max:72', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create($data);
        event(new Registered($user));

        return to_route('login')->with('status', 'Tu cuenta se creó correctamente. Ya puedes iniciar sesión.');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->email))]);
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:72'],
            'remember' => ['sometimes', 'boolean'],
        ]);
        $key = 'login:'.hash('sha256', $request->email.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Demasiados intentos. Inténtalo en '.RateLimiter::availableIn($key).' segundos.']);
        }
        if (! Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'El correo o la contraseña no son correctos.']);
        }
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login')->with('status', 'Has cerrado sesión.');
    }

    public function dashboard(): View
    {
        return view('dashboard');
    }
}
