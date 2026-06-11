@extends('layouts.app')
@section('title', 'Registrarse')

@section('content')

{{-- OVERLAY --}}
<div class="fixed inset-0 bg-black/40 z-40" onclick="window.location.href='/'"></div>

{{-- CARD --}}
<div class="fixed inset-0 z-50 flex items-center justify-center pointer-events-none">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-8 shadow-xl pointer-events-auto overflow-y-auto max-h-[90vh]">

        <h1 class="text-2xl font-bold mb-6 text-center">Crear cuenta</h1>

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 p-3 text-rose-800 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf

            <label class="block">
                <span class="text-sm text-slate-600">Nombre completo <span class="text-red-500">*</span></span>
                <input type="text" name="nombre" value="{{ old('nombre') }}" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Tipo de identificación <span class="text-red-500">*</span></span>
                <select name="tipo_identificacion" required
                        class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    <option value="">Seleccione...</option>
                    <option value="C" {{ old('tipo_identificacion') == 'C' ? 'selected' : '' }}>Cédula</option>
                    <option value="R" {{ old('tipo_identificacion') == 'R' ? 'selected' : '' }}>RUC</option>
                    <option value="P" {{ old('tipo_identificacion') == 'P' ? 'selected' : '' }}>Pasaporte</option>
                </select>
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Cédula / RUC</span>
                <input type="text" name="cedula_ruc" value="{{ old('cedula_ruc') }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Email <span class="text-red-500">*</span></span>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Teléfono</span>
                <input type="text" name="telefono" value="{{ old('telefono') }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Dirección</span>
                <input type="text" name="direccion" value="{{ old('direccion') }}"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </label>

            {{-- <label class="block">
                <span class="text-sm text-slate-600">Contraseña <span class="text-red-500">*</span></span>
                <input type="password" name="password" required minlength="6"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </label> --}}
            <label class="block">
    <span class="text-sm text-slate-600">Contraseña <span class="text-red-500">*</span></span>

    <div class="relative mt-1">
        <input
            type="password"
            name="password"
            id="password"
            required
            minlength="6"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-11 focus:outline-none focus:ring-2 focus:ring-indigo-400">

        <button
            type="button"
            onclick="togglePassword()"
            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 transition">

            <!-- Ícono ojo abierto -->
            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>

            <!-- Ícono ojo cerrado -->
            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 hidden"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.1-2.407A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.132 5.411M15 12a3 3 0 00-3-3m0 0a2.99 2.99 0 00-2.12.879M12 9l9 9M3 3l18 18"/>
            </svg>
        </button>
    </div>
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
</div>
@push('scripts')

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: '¡Cuenta creada!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#3E7CB4'
    });
</script>
@endif

@if($errors->any())
<script>
    Swal.fire({
        icon: 'error',
        title: 'Error al registrarse',
        text: "{{ $errors->first() }}",
        confirmButtonColor: '#3E7CB4'
    });
</script>
@endif

@endpush
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeOpen = document.getElementById('eyeOpen');
    const eyeClosed = document.getElementById('eyeClosed');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        passwordInput.type = 'password';
        eyeClosed.classList.add('hidden');
        eyeOpen.classList.remove('hidden');
    }
}
</script>
@endsection
