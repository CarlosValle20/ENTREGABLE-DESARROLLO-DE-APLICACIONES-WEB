@extends('layouts.auth')

@section('title', 'Recuperar contraseña')

@section('content')
    <h1>Recuperar contraseña</h1>
    <p class="intro">Ingresa el correo de tu cuenta para solicitar un enlace.</p>

    @include('auth.mensajes')

    <form action="{{ route('password.email') }}" method="POST">
        @csrf

        <div class="field">
            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                autocomplete="email" maxlength="254" required autofocus>
        </div>

        <button class="button" type="submit">Enviar enlace</button>
    </form>

    <p class="note">El enlace vence en {{ config('auth.passwords.users.expire') }} minutos y solo se puede usar una vez.</p>
    <p class="form-link"><a href="{{ route('login') }}">Volver al inicio de sesión</a></p>
@endsection
