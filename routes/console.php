<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('acceso:ultimo-enlace', function () {
    if (! app()->environment('local') || config('mail.default') !== 'log') {
        $this->error('Este comando solo está disponible en entorno local con MAIL_MAILER=log.');

        return 1;
    }
    $path = storage_path('logs/mail.log');
    if (! is_file($path)) {
        $this->error('Todavía no hay mensajes. Solicita un enlace desde el formulario de recuperación.');

        return 1;
    }
    $content = quoted_printable_decode(file_get_contents($path));
    $base = preg_quote(rtrim(config('app.url'), '/'), '~');
    preg_match_all('~'.$base.'/restablecer-contrasena/[a-zA-Z0-9]+\?email=[^\s"<]+~', $content, $matches);
    if (empty($matches[0])) {
        $this->error('No hay enlaces para el APP_URL actual. Comprueba APP_URL y solicita un enlace nuevo.');

        return 1;
    }
    $url = rtrim(html_entity_decode(end($matches[0]), ENT_QUOTES, 'UTF-8'), '.,);');
    $this->warn('Solo para desarrollo: no compartas este enlace sensible.');
    $this->line($url);

    return 0;
})->purpose('Muestra el último enlace del correo local de recuperación sin exponerlo en una ruta web.');
