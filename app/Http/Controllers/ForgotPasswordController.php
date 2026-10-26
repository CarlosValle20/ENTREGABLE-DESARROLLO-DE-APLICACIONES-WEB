<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ForgotPasswordController extends Controller
{
    public const STATUS = 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña. Revisa también la carpeta de spam.';

    public function showLinkRequestForm(): View
    {
        return view('auth.recuperacion');
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.restaurar', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->email))]);
        $request->validate(['email' => ['required', 'string', 'email', 'max:254']]);
        try {

            Password::sendResetLink($request->only('email'));
        } catch (TransportExceptionInterface $exception) {

            logger()->error('El transporte de correo no está disponible. Revise la configuración SMTP local.');
        }

        return back()->with('status', self::STATUS);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->merge(['email' => mb_strtolower(trim((string) $request->email))]);
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required', 'string', 'max:72', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                DB::transaction(function () use ($user, $password) {
                    $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
                    if (config('session.driver') === 'database') {
                        DB::table('sessions')->where('user_id', $user->id)->delete();
                    }
                });
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? to_route('login')->with('status', __($status))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($status)]);
    }
}
