@extends('layouts.app')
@section('title', 'Registrarse')

@section('content')
<div class="max-w-md mx-auto bg-white border border-slate-200 rounded-2xl p-8">
    <h1 class="text-2xl font-bold mb-6">Crear cuenta</h1>

    @if($errors->any())
        <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 p-3 text-rose-800 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
        @csrf
        <label class="block">
            <span class="text-sm text-slate-600">Nombre</span>
            <input type="text" name="nombre" value="{{ old('nombre') }}" required
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm text-slate-600">Email</span>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>
        <label class="block">
            <span class="text-sm text-slate-600">Contraseña</span>
            <input type="password" name="password" required
                   class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2">
        </label>
        <button class="w-full rounded-full bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700">
            Registrarse
        </button>
    </form>

    <p class="text-sm text-slate-500 text-center mt-4">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Inicia sesión</a>
    </p>
</div>
@endsection
