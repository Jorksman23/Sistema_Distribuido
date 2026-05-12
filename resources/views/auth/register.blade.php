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

            <label class="block">
                <span class="text-sm text-slate-600">Contraseña <span class="text-red-500">*</span></span>
                <input type="password" name="password" required minlength="6"
                       class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-400">
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
@endsection
