@extends('layouts.app')
@section('title', 'Recuperar contraseña')

@section('content')
    <div class="mt-20 mb-10 flex justify-center">
        <div class="max-w-md w-full bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
            <h1 class="text-2xl font-bold mb-2">¿Olvidaste tu contraseña?</h1>
            <p class="text-slate-500 text-sm mb-6">
                Ingresa tu email y te enviaremos un enlace para restablecerla.
            </p>

            <form action="{{ route('password.send.link') }}" method="post" class="space-y-4">
                @csrf
                <label class="block">
                    <span class="text-sm text-slate-600">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                </label>
                <button class="w-full rounded-full bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700">
                    Enviar enlace
                </button>
            </form>

            <p class="text-sm text-center mt-4">
                <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Volver a iniciar sesión</a>
            </p>
        </div>
    </div>
@endsection
