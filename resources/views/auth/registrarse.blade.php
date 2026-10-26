@extends('layouts.auth')

@section('title', 'Registro de usuario')
@section('card-class', 'register-card')

@section('content')
    <h1>Crear cuenta</h1>
    <p class="intro">Completa los siguientes datos para registrarte.</p>

    @include('auth.mensajes')

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div class="field">
            <label for="name">Nombre completo</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                autocomplete="name" minlength="3" maxlength="120" required autofocus>
        </div>

        <div class="form-row">
            <div class="field">
                <label for="dni">DNI</label>
                <input id="dni" type="text" name="dni" value="{{ old('dni') }}"
                    placeholder="8 dígitos" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required>
            </div>

            <div class="field">
                <label for="email">Correo electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                    autocomplete="username" maxlength="254" required>
            </div>
        </div>

        <div class="field">
            <label for="password">Contraseña</label>
            <div class="password-field">
                <input id="password" type="password" name="password"
                    autocomplete="new-password" minlength="8" maxlength="72" aria-describedby="password-help" required>
                <button class="show-password" type="button" data-toggle-password="password"
                    aria-label="Mostrar contraseña" aria-pressed="false">Ver</button>
            </div>
            <small id="password-help">De 8 a 72 caracteres, con mayúscula, minúscula y número.</small>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <div class="password-field">
                <input id="password_confirmation" type="password" name="password_confirmation"
                    autocomplete="new-password" minlength="8" maxlength="72" required>
                <button class="show-password" type="button" data-toggle-password="password_confirmation"
                    aria-label="Mostrar confirmación de contraseña" aria-pressed="false">Ver</button>
            </div>
        </div>

        <button class="button" type="submit">Registrarme</button>
    </form>

    <p class="form-link">¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a></p>
@endsection
