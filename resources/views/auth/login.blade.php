@extends('layouts.app')
@section('title', 'Iniciar sesión')

@section('content')

{{-- OVERLAY --}}
<div class="fixed inset-0 bg-black/40 z-40" onclick="window.location.href='/'"></div>

{{-- CARD --}}
<div class="fixed inset-0 z-50 flex items-center justify-center pointer-events-none">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-8 shadow-xl pointer-events-auto">

        <h1 class="text-2xl font-bold mb-6 text-center">Iniciar sesión</h1>

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 p-3 text-rose-800 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <label class="block">
                <span class="text-sm text-slate-600">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </label>
            <label class="block">
                <span class="text-sm text-slate-600">Contraseña</span>
                <input type="password" name="password" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </label>
            <button class="w-full rounded-full bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700">
                Ingresar
            </button>
        </form>

        <p class="text-sm text-slate-500 text-center mt-4">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Regístrate</a>
        </p>
    </div>
</div>
@push('scripts')
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Cuenta creada!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#6366f1'
    });
</script>
@endif

@if($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: "{{ $errors->first() }}",
        confirmButtonColor: '#6366f1'
    });
</script>
@endif
@endpush
@endsection
