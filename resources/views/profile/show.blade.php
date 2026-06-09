@extends('layouts.app')
@section('title', 'Mi cuenta')

@section('content')
<style>
    /* Focus de inputs con color indigo */
    .profile-input:focus {
        outline: none;
        border-color: #6366f1;
        box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
    }
</style>

<div class="max-w-7xl mx-auto mt-6 sm:mt-8 px-3 sm:px-4 mb-16">
<div class="flex flex-col lg:flex-row gap-6">

    {{-- ===== SIDEBAR CUENTA ===== --}}
    <aside class="w-full lg:w-56 shrink-0">
        {{-- Borde gris suave --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-300 overflow-hidden">

            {{-- Cabecera azul con avatar y datos --}}
            <div class="bg-indigo-600 px-[10px] py-5 flex flex-col items-center text-center">

                {{-- Ícono de usuario --}}
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center mb-3">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>

                {{-- Nombre con padding de 10px para nombres largos --}}
                <p class="font-bold text-white text-sm px-[5px] break-words text-center w-full leading-tight">
                    {{ $usuario->nombre }}
                </p>

                {{-- Email --}}
                <p class="text-xs text-indigo-200 mt-1 px-[5px] break-words w-full">
                    {{ $usuario->email }}
                </p>
            </div>

            {{-- Menú de navegación --}}
            <nav class="p-3 space-y-1">

                {{-- Mi perfil — mismo color que Mis pedidos, negro al hover --}}
                <a href="{{ route('profile.show') }}"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium
                          text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Mi perfil
                </a>

                {{-- Mis pedidos — negro al hover --}}
                <a href="{{ route('profile.show') }}#historial-pedidos"
                   class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium
                          text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Mis pedidos
                </a>

                {{-- Cerrar sesión — rojo pálido, rojo intenso al hover --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium
                                   text-red-400 hover:bg-red-50 hover:text-red-600 transition">
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
    <div class="flex-1 min-w-0 space-y-6">

        {{-- ALERTA: datos actualizados correctamente --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                ✓ {{ session('success') }}
            </div>
        @endif

        {{-- ALERTA: contraseña cambiada correctamente --}}
        @if(session('success_pass'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                ✓ {{ session('success_pass') }}
            </div>
        @endif

        {{-- ALERTA: errores de validación --}}
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                @foreach($errors->all() as $e)
                    <p>• {{ $e }}</p>
                @endforeach
            </div>
        @endif

        {{-- ===== INFORMACIÓN PERSONAL ===== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <h2 class="font-bold text-gray-800 mb-5">Información Personal</h2>

            {{-- Datos de solo lectura (no editables) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-xl">
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
                <div class="col-span-1 sm:col-span-2">
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Dirección</p>
                    <p class="font-semibold text-gray-800">{{ $usuario->direccion ?? '—' }}</p>
                </div>
            </div>

            {{-- Formulario de edición --}}
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Nombre --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nombre</label>
                        <input type="text" name="nombre"
                               value="{{ old('nombre', $usuario->nombre) }}" required
                               class="profile-input w-full border border-gray-200 rounded-xl px-3 py-2 text-sm transition">
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Email</label>
                        <input type="email" name="email"
                               value="{{ old('email', $usuario->email) }}" required
                               class="profile-input w-full border border-gray-200 rounded-xl px-3 py-2 text-sm transition">
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Teléfono</label>
                        <input type="text" name="telefono"
                               value="{{ old('telefono', $usuario->telefono) }}"
                               class="profile-input w-full border border-gray-200 rounded-xl px-3 py-2 text-sm transition">
                    </div>

                    {{-- Cédula / RUC — no editable --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">
                            Cédula / RUC <span class="text-gray-300">(no editable)</span>
                        </label>
                        <input type="text" value="{{ $usuario->cedula_ruc }}" disabled
                               class="w-full border border-gray-100 rounded-xl px-3 py-2 text-sm
                                      bg-gray-50 text-gray-400 cursor-not-allowed">
                    </div>

                    {{-- Dirección --}}
                    <div class="col-span-1 sm:col-span-2">
                        <label class="block text-xs text-gray-500 mb-1">Dirección</label>
                        <input type="text" name="direccion"
                               value="{{ old('direccion', $usuario->direccion) }}"
                               class="profile-input w-full border border-gray-200 rounded-xl px-3 py-2 text-sm transition">
                    </div>
                </div>

                {{-- Botón guardar —  gris oscuro --}}
                <button type="submit"
                        class="mt-4 w-full bg-gray-800 hover:bg-gray-900 text-white
                               font-semibold py-2.5 rounded-xl transition">
                    Guardar cambios
                </button>
            </form>
        </div>

        {{-- ===== CAMBIAR CONTRASEÑA ===== --}}
        <div class="min-w-0 bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <h2 class="font-bold text-gray-800 mb-5">Cambiar contraseña</h2>
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')
                <div class="space-y-4">

                    {{-- Contraseña actual --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Contraseña actual</label>
                        <div class="relative">
                            <input type="password" name="current" id="current" required
                                   class="profile-input w-full border border-gray-200 rounded-xl px-3 py-2 pr-10 text-sm transition">
                            <button type="button" onclick="togglePass('current')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Nueva contraseña --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Nueva contraseña</label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required
                                   class="profile-input w-full border border-gray-200 rounded-xl px-3 py-2 pr-10 text-sm transition">
                            <button type="button" onclick="togglePass('password')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Confirmar nueva contraseña --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Confirmar nueva contraseña</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="profile-input w-full border border-gray-200 rounded-xl px-3 py-2 pr-10 text-sm transition">
                            <button type="button" onclick="togglePass('password_confirmation')"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Botón cambiar contraseña — gris oscuro --}}
                <button type="submit"
                        class="mt-4 w-full bg-gray-800 hover:bg-gray-900 text-white
                               font-semibold py-2.5 rounded-xl transition">
                    Cambiar contraseña
                </button>
            </form>
        </div>

                {{-- ===== HISTORIAL DE PEDIDOS ===== --}}
        <div id="historial-pedidos" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <h2 class="font-bold text-gray-800 mb-5 text-center">Historial de Pedidos</h2>

            @if(empty($pedidos))
                {{-- Estado vacío: sin pedidos aún --}}
                <div class="text-center py-10 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="font-medium text-gray-500">Aún no tienes pedidos</p>
                    <p class="text-sm mt-1">Cuando realices una compra aparecerá aquí.</p>

                    <a href="{{ route('catalogo.index') }}"
                       class="mt-4 inline-block bg-gray-800 hover:bg-gray-900 text-white
                              text-sm font-medium px-5 py-2 rounded-full transition">
                        Ir al catálogo
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Pedido</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Fecha</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Total</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Método</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Estado</th>
                                <th class="px-4 py-3 text-center font-medium text-gray-600">Comprobante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($pedidos as $pedido)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-4 font-medium">
                                    #{{ $pedido->codigo }}
                                </td>
                                <td class="px-4 py-4 text-gray-600">
                                    {{ \Carbon\Carbon::parse($pedido->fecha_creacion)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-4 font-semibold text-gray-800">
                                    ${{ number_format($pedido->gran_total, 2) }}
                                </td>
                                <td class="px-4 py-4 text-gray-600 capitalize">
                                    {{ $pedido->tipo_pago }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($pedido->estatus == '2' || $pedido->estatus == 'P')
                                        <span class="inline-block px-3 py-1 text-xs font-medium bg-green-100 text-green-700 rounded-full">
                                            Completado
                                        </span>
                                    @else
                                        <span class="inline-block px-3 py-1 text-xs font-medium bg-yellow-100 text-yellow-700 rounded-full">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                @if($pedido->estatus == '2' || $pedido->estatus == 'P')
                                    <a href="{{ route('pedidos.descargar', $pedido->codigo) }}"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 hover:text-emerald-700 border border-emerald-200 hover:bg-emerald-50 px-3 py-1.5 rounded-full transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Descargar
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
</div>

@push('scripts')
<script>
// Alternar visibilidad de campos de contraseña
function togglePass(id) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
@endpush
@endsection
