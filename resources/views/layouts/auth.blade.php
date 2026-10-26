<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Sistema de usuarios</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <a class="skip-link" href="#contenido">Saltar al contenido</a>
    <main class="page">
        <header class="page-title">
            <a href="{{ route('login') }}">Sistema de Autenticación Web</a>
        </header>

        <section id="contenido" class="card @yield('card-class')" tabindex="-1">
            @yield('content')
        </section>
    </main>
</body>
</html>
