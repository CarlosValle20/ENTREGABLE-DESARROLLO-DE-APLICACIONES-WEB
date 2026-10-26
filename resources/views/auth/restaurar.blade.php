@extends('layouts.auth')

@section('title', 'Nueva contraseña')

@section('content')
    <h1>Restablecer contraseña</h1>
    <p class="intro">Escribe y confirma tu nueva contraseña.</p>

    @include('auth.mensajes')

    <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}"
                autocomplete="username" maxlength="254" required>
        </div>

        <div class="field">
            <label for="password">Nueva contraseña</label>
            <div class="password-field">
                <input id="password" type="password" name="password"
                    autocomplete="new-password" minlength="8" maxlength="72" aria-describedby="password-help" required autofocus>
                <button class="show-password" type="button" data-toggle-password="password"
                    aria-label="Mostrar nueva contraseña" aria-pressed="false">Ver</button>
            </div>
            <small id="password-help">De 8 a 72 caracteres, con mayúscula, minúscula y número.</small>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmar nueva contraseña</label>
            <div class="password-field">
                <input id="password_confirmation" type="password" name="password_confirmation"
                    autocomplete="new-password" minlength="8" maxlength="72" required>
                <button class="show-password" type="button" data-toggle-password="password_confirmation"
                    aria-label="Mostrar confirmación de contraseña" aria-pressed="false">Ver</button>
            </div>
        </div>

        <button class="button" type="submit">Guardar contraseña</button>
    </form>

    <p class="form-link"><a href="{{ route('password.request') }}">Solicitar otro enlace</a></p>
    <p class="form-link"><a href="{{ route('login') }}">Volver al inicio de sesión</a></p>
@endsection
