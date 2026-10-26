@extends('layouts.auth')

@section('title', 'Error 500')

@section('content')
    <p class="error-code">500</p>
    <h1>Error del servidor</h1>
    <p class="intro">No se pudo completar la solicitud. Inténtalo de nuevo en unos momentos.</p>
    <a class="button" href="{{ route('login') }}">Volver al inicio</a>
@endsection
