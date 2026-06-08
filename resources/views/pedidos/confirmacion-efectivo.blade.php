@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- PANEL IZQUIERDO --}}
        <div class="flex-1">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">

                {{-- Icono --}}
                <div class="w-14 h-14 rounded-full bg-blue-50 flex items-center justify-center mb-6">
                    <svg class="w-7 h-7 text-[#0300a3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                {{-- Título --}}
                <h2 class="text-2xl font-bold text-[#0300a3] mb-2">¡Pedido registrado!</h2>
                <p class="text-gray-500 mb-8">Tu pedido fue registrado correctamente y quedó reservado.</p>

                {{-- Cards info --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">

                    {{-- Código --}}
                    <div class="border border-gray-200 rounded-xl p-5">
                        <p class="text-xs uppercase text-gray-400 font-semibold tracking-wide mb-2">Identificador</p>
                        <p class="text-base font-bold text-gray-800 mb-3">Código de pedido: {{ $codigoOrden }}</p>
                        <button onclick="navigator.clipboard.writeText('{{ $codigoOrden }}')"
                                class="flex items-center gap-1 text-xs text-gray-500 border border-gray-200 rounded-lg px-3 py-1.5 hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Copiar código
                        </button>
                    </div>

                    {{-- Total --}}
                    <div class="border border-gray-200 rounded-xl p-5">
                        <p class="text-xs uppercase text-gray-400 font-semibold tracking-wide mb-2">Monto a cancelar</p>
                        <p class="text-base font-bold text-gray-800 mb-3">Total a pagar: ${{ number_format($total, 2) }}</p>
                        <span class="inline-block bg-yellow-100 text-yellow-700 text-xs font-semibold px-3 py-1 rounded-full">
                            Pago en efectivo
                        </span>
                    </div>

                </div>

                {{-- Botones --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-6">
                    <a href="{{ route('profile.show') }}"
                    class="flex items-center justify-center gap-2 bg-[#0300a3] hover:bg-[#0200cc] text-white text-sm font-semibold px-6 py-3 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Ver mis pedidos
                    </a>
                    <a href="{{ route('pedidos.descargar', $codigoOrden) }}"
                    class="flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold px-6 py-3 rounded-xl transition shadow-sm">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Descargar PDF
                    </a>
                    <a href="{{ route('catalogo.index') }}"
                    class="flex items-center justify-center gap-2 border border-[#0300a3] text-[#0300a3] hover:bg-blue-50 text-sm font-semibold px-6 py-3 rounded-xl transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Seguir comprando
                    </a>
                </div>

            </div>
        </div>

        {{-- PANEL DERECHO --}}
        <div class="w-full lg:w-80 shrink-0 flex flex-col gap-4">

            {{-- Próximos pasos --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h5 class="font-bold text-gray-800 mb-5">Próximos pasos</h5>

                <div class="space-y-5">
                    <div class="flex gap-3">
                        <span class="w-6 h-6 rounded-full bg-[#0300a3] text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Guarda tu código de pedido</p>
                            <p class="text-xs text-gray-500 mt-0.5">Toma una captura o anota el código {{ $codigoOrden }}.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <span class="w-6 h-6 rounded-full bg-[#0300a3] text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Acércese a la tienda</p>
                            <p class="text-xs text-gray-500 mt-0.5">Visita nuestra sucursal en horario de atención.</p>
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <span class="w-6 h-6 rounded-full bg-[#0300a3] text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Realice el pago</p>
                            <p class="text-xs text-gray-500 mt-0.5">Presente su código y complete la compra.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ayuda --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-5">
                <p class="text-sm font-bold text-yellow-800 mb-1">¿Necesitas ayuda?</p>
                <p class="text-xs text-yellow-700">Contáctanos por WhatsApp o visita nuestra tienda.</p>
            </div>

        </div>

    </div>
</div>
@endsection
