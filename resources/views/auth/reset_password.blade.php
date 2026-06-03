@extends('layouts.app')
@section('title', 'Nueva contraseña')

@section('content')
    <div class="max-w-md mx-auto mt-11 bg-white border border-slate-200 rounded-2xl p-8 sm:p-7 shadow-md">
        <h1 class="text-2xl font-bold mb-8 text-center">Elige una nueva contraseña</h1>

        @if($errors->any())
            <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Formulario de reseteo -->
        <form method="POST" action="{{ route('password.reset.submit') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="block">
                <span class="text-sm text-slate-600">Email</span>
                <input type="email" name="email" value="{{ old('email', $email) }}" required
                       class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Nueva contraseña (mín. 8)</span>
                <input type="password" name="password" required
                       class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Confirmar</span>
                <input type="password" name="password_confirmation" required
                       class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </label>

            <div class="pt-4">
                <button type="submit"
                        class="w-full rounded-full bg-indigo-600 text-white py-3 font-medium hover:bg-indigo-700 transition-colors">
                    Restablecer contraseña
                </button>
            </div>
        </form>
    </div>
@endsection
