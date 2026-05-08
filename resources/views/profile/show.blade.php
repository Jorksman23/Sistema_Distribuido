@extends('layouts.app')
@section('title', 'Mi cuenta')

@section('content')
<div class="max-w-5xl mx-auto mt-8 px-4 mb-16">
<div class="flex gap-6">

    {{-- ===== SIDEBAR CUENTA ===== --}}
    <aside class="w-56 shrink-0">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">

            {{-- Avatar + nombre --}}
            <div class="flex flex-col items-center text-center mb-6 pb-5 border-b border-gray-100">
                <div class="w-14 h-14 rounded-full bg-orange-100 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="font-semibold text-gray-800 text-sm leading-tight">{{ $usuario->nombre }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $usuario->email }}</p>
            </div>

            {{-- Menú --}}
            <nav class="space-y-1">
                <a href="{{ route('profile.show') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium bg-orange-50 text-orange-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Mi perfil
                </a>
                <a href="#"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Mis pedidos
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-red-500 hover:bg-red-50 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </nav>
        </div>
    </aside>

    {{-- ===== CONTENIDO PRINCIPAL ===== --}}
    <div class="flex-1 space-y-6">

        {{-- ALERTAS --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('success_pass'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                ✓ {{ session('success_pass') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                @foreach($errors->all() as $e)
                    <p>• {{ $e }}</p>
                @endforeach
            </div>
        @endif

        {{-- INFORMACIÓN PERSONAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-gray-800">Información Personal</h2>
            </div>

            {{-- Datos de solo lectura --}}
            <div class="grid grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Nombre completo</p>
                    <p class="font-semibold text-gray-800">{{ $usuario->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Correo electrónico</p>
                    <p class="font-semibold text-gray-800">{{ $usuario->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Teléfono</p>
                    <p class="font-semibold text-gray-800">{{ $usuario->telefono ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Cédula / RUC</p>
                    <p class="font-semibold text-gray-800">{{ $usuario->cedula_ruc ?? '—' }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Dirección</p>
                    <p class="font-semibold text-gray-800">{{ $usuario->direccion ?? '—' }}</p>
                </div>
            </div>

            {{-- Formulario edición --}}
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nombre</label>
                        <input type="text" name="nombre"
                               value="{{ old('nombre', $usuario->nombre) }}" required
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $usuario->email) }}" required
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Teléfono</label>
                        <input type="text" name="telefono"
                               value="{{ old('telefono', $usuario->telefono) }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">
                            Cédula / RUC
                            <span class="text-gray-300 font-normal">(no editable)</span>
                        </label>
                        <input type="text" value="{{ $usuario->cedula_ruc }}" disabled
                               class="w-full border border-gray-100 rounded-xl px-3 py-2 text-sm bg-gray-50 text-gray-400 cursor-not-allowed">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Dirección</label>
                        <input type="text" name="direccion"
                               value="{{ old('direccion', $usuario->direccion) }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                </div>
                <button type="submit"
                        class="mt-4 w-full bg-orange-400 hover:bg-orange-500 text-white font-semibold py-2.5 rounded-xl transition">
                    Guardar cambios
                </button>
            </form>
        </div>

        {{-- CAMBIAR CONTRASEÑA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-gray-800 mb-5">Cambiar contraseña</h2>
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Contraseña actual</label>
                        <input type="password" name="current" required
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nueva contraseña</label>
                        <input type="password" name="password" required
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" required
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-300">
                    </div>
                </div>
                <button type="submit"
                        class="mt-4 w-full bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2.5 rounded-xl transition">
                    Cambiar contraseña
                </button>
            </form>
        </div>

        {{-- HISTORIAL DE PEDIDOS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-gray-800">Historial de Pedidos</h2>
            </div>
            <div class="text-center py-10 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="font-medium text-gray-500">Aún no tienes pedidos</p>
                <p class="text-sm mt-1">Cuando realices una compra aparecerá aquí.</p>
                <a href="{{ route('catalogo.index') }}"
                   class="mt-4 inline-block bg-orange-400 hover:bg-orange-500 text-white text-sm font-medium px-5 py-2 rounded-full transition">
                    Ir al catálogo
                </a>
            </div>
        </div>

    </div>
</div>
</div>
@endsection
