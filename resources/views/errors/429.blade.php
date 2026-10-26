@extends('layouts.auth')

@section('title', 'Error 429')

@section('content')
    <p class="error-code">429</p>
    <h1>Demasiados intentos</h1>
    <p class="intro">Espera un minuto antes de volver a intentarlo.</p>
    <a class="button" href="{{ route('login') }}">Volver al inicio</a>
@endsection
