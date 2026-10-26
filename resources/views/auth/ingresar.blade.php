@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')
    <h1>Iniciar sesión</h1>
    <p class="intro">Ingresa con tu correo y contraseña.</p>

    @include('auth.mensajes')

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="field">
            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                placeholder="correo@ejemplo.com" autocomplete="username" maxlength="254" required autofocus>
        </div>

        <div class="field">
            <label for="password">Contraseña</label>
            <div class="password-field">
                <input id="password" type="password" name="password"
                    autocomplete="current-password" maxlength="72" required>
                <button class="show-password" type="button" data-toggle-password="password"
                    aria-label="Mostrar contraseña" aria-pressed="false">Ver</button>
            </div>
        </div>

        <div class="form-options">
            <label class="checkbox">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Recordarme
            </label>
            <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        </div>

        <button class="button" type="submit">Ingresar</button>
    </form>

    <div class="separator">o</div>
    <a class="button secondary" href="{{ route('google.redirect') }}">Continuar con Google</a>

    <p class="form-link">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
@endsection
