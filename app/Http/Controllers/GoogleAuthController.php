<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    private function configured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->configured()) {
            return to_route('login')->withErrors(['google' => 'Google aún no está configurado. Puedes iniciar sesión con tu correo y contraseña.']);
        }
        $request->session()->put('google_purpose', 'login');

        return Socialite::driver('google')->redirect();
    }

    public function link(Request $request): RedirectResponse
    {
        if (! $this->configured()) {
            return to_route('dashboard')->withErrors(['google' => 'La integración con Google aún no está configurada.']);
        }
        $request->validate(['current_password' => ['required', 'string', 'current_password:web']]);
        $request->session()->put('google_purpose', 'link');
        $request->session()->put('google_link_user', $request->user()->id);

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if (! $this->configured()) {
            return to_route('login')->withErrors(['google' => 'Google aún no está configurado.']);
        }
        $purpose = $request->session()->pull('google_purpose');
        $linkUser = $request->session()->pull('google_link_user');
        $target = Auth::check() ? 'dashboard' : 'login';
        if (! $purpose || $request->has('error')) {
            return to_route($target)->withErrors(['google' => 'No se completó la autorización con Google. Inténtalo nuevamente.']);
        }
        try {

            $profile = Socialite::driver('google')->user();
            $verified = filter_var($profile->user['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
            if (! $verified || ! $profile->getId() || ! $profile->getEmail()) {
                return to_route($target)->withErrors(['google' => 'Google debe proporcionar un correo electrónico verificado.']);
            }
            $email = mb_strtolower($profile->getEmail());
            if ($purpose === 'link') {
                if (! Auth::check() || $request->user()->id !== $linkUser || $email !== $request->user()->email) {
                    return to_route($target)->withErrors(['google' => 'Inicia sesión y vincula una cuenta de Google con el mismo correo.']);
                }
                if (filled($request->user()->google_id) && $request->user()->google_id !== $profile->getId()) {
                    return to_route('dashboard')->withErrors(['google' => 'Ya hay otra identidad de Google vinculada a esta cuenta.']);
                }
                $request->user()->forceFill(['google_id' => $profile->getId()])->save();

                return to_route('dashboard')->with('status', 'Vinculaste Google correctamente. Ya puedes usarlo para iniciar sesión.');
            }
            $user = User::where('google_id', $profile->getId())->first();
            if (! $user || $user->email !== $email) {
                return to_route('login')->withErrors(['google' => 'Para usar Google, crea primero tu cuenta con DNI. Luego inicia sesión con tu contraseña y vincula Google desde el panel.']);
            }
            Auth::login($user);
            $request->session()->regenerate();

            return to_route('dashboard');
        } catch (InvalidStateException $exception) {
            return to_route($target)->withErrors(['google' => 'La autorización venció o no es válida. Vuelve a iniciar el proceso.']);
        } catch (GuzzleException $exception) {
            return to_route($target)->withErrors(['google' => 'No se pudo conectar con Google. Inténtalo más tarde.']);
        } catch (UniqueConstraintViolationException $exception) {
            return to_route($target)->withErrors(['google' => 'Esta identidad de Google ya está vinculada a otra cuenta.']);
        }
    }
}
