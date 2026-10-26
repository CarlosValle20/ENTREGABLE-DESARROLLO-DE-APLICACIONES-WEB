@extends('layouts.auth')

@section('title', 'Error 419')

@section('content')
    <p class="error-code">419</p>
    <h1>El formulario venció</h1>
    <p class="intro">Vuelve a abrir el formulario e inténtalo de nuevo.</p>
    <a class="button" href="{{ route('login') }}">Volver al inicio</a>
@endsection
