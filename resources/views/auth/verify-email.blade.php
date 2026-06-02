@extends('layouts.app')
@section('title', 'Verificación de correo')

@section('content')
<div class="fixed inset-0 bg-black/40 z-40"></div>

<div class="fixed inset-0 z-50 flex items-center justify-center pointer-events-none">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-8 shadow-xl pointer-events-auto text-center">

        <h1 class="text-2xl font-bold mb-4 text-indigo-700">
            Verifica tu correo electrónico
        </h1>

        <p class="text-slate-600 mb-6">
            Te hemos enviado un enlace de confirmación a tu correo.
            Haz clic en el botón de abajo si necesitas que te reenviemos el enlace.
        </p>

        @if(session('message'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-green-800 text-sm">
                {{ session('message') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="w-full rounded-full bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700">
                Reenviar enlace de verificación
            </button>
        </form>

        <p class="text-sm text-slate-500 mt-4">
            Si ya verificaste tu correo, <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">inicia sesión aquí</a>.
        </p>
    </div>
</div>
@endsection
