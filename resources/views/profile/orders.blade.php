@extends('layouts.app')

@section('title', 'Mis pedidos')

@section('content')

<style>
    nav[role="navigation"] p{
        display:none;
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
                    <a href="{{ route('profile.orders') }}" #historial-pedidos"
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

    <div class="flex-1 min-w-0">
        {{-- ===== HISTORIAL DE PEDIDOS ===== --}}
        <div id="historial-pedidos" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-gray-900 text-2xl flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6 text-gray-700"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 1 1-3.536-7.153"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 4v4h4"/>
                    </svg>
                    Historial de Pedidos
                </h2>

                <button onclick="window.location.reload()"
                        class="flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-gray-800 border border-gray-200 hover:border-gray-400 px-3 py-1.5 rounded-full transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualizar
                </button>
            </div>
        </div>
            @if($pedidos->total() == 0)
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
                    <div class="mt-6 flex justify-center">
                        {{ $pedidos->onEachSide(1)->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
