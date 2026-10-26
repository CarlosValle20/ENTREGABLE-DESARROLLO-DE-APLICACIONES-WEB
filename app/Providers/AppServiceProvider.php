<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('oauth', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));
        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(20)->by($r->ip()));
        RateLimiter::for('register', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
        RateLimiter::for('recovery', fn (Request $r) => Limit::perMinute(5)->by($r->ip()));
        ResetPassword::createUrlUsing(fn ($user, string $token) => rtrim(config('app.url'), '/').route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false));
        ResetPassword::toMailUsing(fn ($user, string $token) => (new MailMessage)
            ->subject('Restablece tu contraseña · Acceso')
            ->greeting('Hola, '.$user->name)
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta en Sistema de Autenticación Web.')
            ->action('Crear nueva contraseña', rtrim(config('app.url'), '/').route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false))
            ->line('El enlace vence en '.config('auth.passwords.users.expire').' minutos y solo se puede utilizar una vez.')
            ->line('Si no solicitaste este cambio, puedes ignorar este mensaje. Tu contraseña seguirá siendo la misma.')
            ->salutation('El equipo de Acceso'));
    }
}
