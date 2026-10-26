@extends('layouts.auth')

@section('title', 'Panel de usuario')
@section('card-class', 'dashboard-card')

@section('content')
    <nav class="panel-nav" aria-label="Menú principal">
        <a href="{{ route('dashboard') }}" aria-current="page">Inicio</a>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="logout" type="submit">Cerrar sesión</button>
        </form>
    </nav>

    <h1>Bienvenido, {{ Str::before(auth()->user()->name, ' ') }}</h1>
    <p class="intro">Has iniciado sesión. Estos son los datos de tu cuenta.</p>

    @include('auth.mensajes')

    <dl class="user-data">
        <div>
            <dt>Nombre completo</dt>
            <dd>{{ auth()->user()->name }}</dd>
        </div>
        <div>
            <dt>DNI</dt>
            <dd>{{ auth()->user()->dni }}</dd>
        </div>
        <div>
            <dt>Correo electrónico</dt>
            <dd>{{ auth()->user()->email }}</dd>
        </div>
    </dl>

    <section class="google-account" aria-labelledby="google-title">
        <h2 id="google-title">Cuenta de Google</h2>
        @if (auth()->user()->google_id)
            <p>Tu cuenta de Google está vinculada. Puedes usarla para iniciar sesión.</p>
        @elseif (config('services.google.client_id') && config('services.google.client_secret'))
            <p>Vincula una cuenta de Google con el mismo correo. Confirma tu contraseña para continuar.</p>
            <form action="{{ route('google.link') }}" method="POST">
                @csrf
                <div class="field">
                    <label for="current_password">Contraseña actual</label>
                    <input id="current_password" type="password" name="current_password"
                        autocomplete="current-password" maxlength="72" required>
                </div>
                <button class="button secondary" type="submit">Vincular Google</button>
            </form>
        @else
            <p>El acceso con Google no está disponible por el momento.</p>
        @endif
    </section>

    <p class="note">Recuerda cerrar sesión si usas una computadora compartida.</p>
@endsection
