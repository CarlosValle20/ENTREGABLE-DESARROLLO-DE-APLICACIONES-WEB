@extends('layouts.auth')

@section('title', 'Error 404')

@section('content')
    <p class="error-code">404</p>
    <h1>Página no encontrada</h1>
    <p class="intro">La dirección solicitada no existe.</p>
    <a class="button" href="{{ route('login') }}">Volver al inicio</a>
@endsection
