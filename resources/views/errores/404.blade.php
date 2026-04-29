@extends('layouts.app')
@section('title', 'Página no encontrada')

@section('content')
    <div class="max-w-md mx-auto text-center py-20">
        <i class="fa-solid fa-compass fa-4x text-slate-300 mb-6"></i>
        <h1 class="text-3xl font-bold mb-2">404</h1>
        <p class="text-slate-500 mb-6">No encontramos la página que buscabas.</p>
        <a href="{{ route('home') }}" class="inline-flex rounded-full bg-indigo-600 text-white px-5 py-2 hover:bg-indigo-700">
            Volver al inicio
        </a>
    </div>
@endsection
