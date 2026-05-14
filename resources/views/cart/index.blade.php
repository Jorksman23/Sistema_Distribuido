@extends('layouts.app')
@section('title', 'Mi Carrito')

@section('content')

{{-- OVERLAY que cierra el carrito al hacer clic fuera --}}
<div class="fixed inset-0 bg-black/40 z-40"
     onclick="window.history.back()"></div>

{{-- PANEL DESLIZANTE DESDE LA DERECHA --}}
<div class="fixed top-0 right-0 h-full w-full max-w-md bg-white z-50 flex flex-col shadow-2xl">

    {{-- HEADER --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-2">
            <span class="text-xl">🛒</span>
            <h1 class="text-lg font-bold text-gray-800">Mi Carrito</h1>
            @if($count > 0)
                <span class="bg-[#0300a3] text-white text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ $count }}
                </span>
            @endif
        </div>
        <button onclick="window.history.back()"
                class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition text-gray-500 text-xl">
            &times;
        </button>
    </div>

    {{-- CONTENIDO SCROLLEABLE --}}
    <div class="flex-1 overflow-y-auto px-5 py-4">

        @if(session('success_cart'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                ✓ {{ session('success_cart') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        @if(count($items) === 0)
            {{-- ESTADO VACÍO --}}
            <div class="flex flex-col items-center justify-center h-full py-20 text-gray-400">
                <span class="text-6xl mb-4">🛒</span>
                <p class="text-lg font-medium text-gray-500">Tu carrito está vacío</p>
                <p class="text-sm mt-1 mb-6">Agrega productos desde el catálogo</p>
                <a href="{{ route('catalogo.index') }}"
                   onclick="window.history.back()"
                   class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-6 py-2.5 rounded-full transition">
                    Ir al catálogo
                </a>
            </div>

        @else
            {{-- LISTA DE PRODUCTOS --}}
            <div class="space-y-4">
                @foreach($items as $item)
                    <div class="flex gap-3 bg-gray-50 rounded-2xl p-3 border border-gray-100">

                        {{-- IMAGEN --}}
                        <div class="shrink-0 w-20 h-20 rounded-xl overflow-hidden bg-white border border-gray-100">
                            <img src="{{ $item->imagen_url }}"
                                 alt="{{ $item->nombre }}"
                                 loading="lazy"
                                 class="w-full h-full object-cover"
                                 onerror="this.onerror=null;this.src='https://placehold.co/80x80?text=?'">
                        </div>

                        {{-- INFO --}}
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xs font-semibold text-gray-800 line-clamp-2 leading-snug">
                                {{ $item->nombre }}
                            </h3>

                            @if($item->nombre_presentacion)
                                <span class="inline-block mt-1 text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">
                                    {{ $item->nombre_presentacion }}
                                </span>
                            @endif

                            <p class="text-sm font-bold text-gray-900 mt-1">
                                ${{ $item->pvp3 }}
                            </p>

                            {{-- CONTROLES DE CANTIDAD --}}
                            <form method="POST" action="{{ route('carrito.update') }}"
                                  class="flex items-center gap-2 mt-2">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="id_item_web" value="{{ $item->id_item_web }}">
                                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white">
                                    <button type="submit" name="cantidad" value="{{ max(1, $item->cantidad - 1) }}"
                                            class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-red-50 text-red-500 hover:text-red-700 transition">
                                        −
                                    </button>
                                    <span class="w-8 text-center text-xs font-semibold text-gray-800">
                                        {{ $item->cantidad }}
                                    </span>
                                    <button type="submit" name="cantidad" value="{{ $item->cantidad + 1 }}"
                                            class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-red-50 text-red-500 hover:text-red-700 transition">
                                        +
                                    </button>
                                </div>
                                <span class="text-xs text-gray-600 font-medium">
                                    = ${{ number_format($item->pvp3 * $item->cantidad, 2) }}
                                </span>
                            </form>
                        </div>

                        {{-- BOTÓN ELIMINAR --}}
                        <form method="POST" action="{{ route('carrito.remove') }}"
                              class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="id_item_web" value="{{ $item->id_item_web }}">
                            <button type="submit"
                                    class="w-7 h-7 flex items-center justify-center rounded-full hover:bg-red-50 text-gray-300 hover:text-red-500 transition"
                                    title="Eliminar">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>

                    </div>
                @endforeach
            </div>

            {{-- VACIAR CARRITO --}}
            <form method="POST" action="{{ route('carrito.vaciar') }}" class="mt-4">
                @csrf
                <button type="submit"
                        onclick="return confirm('¿Vaciar todo el carrito?')"
                        class="w-full flex justify-content gap-2 px-3 py-2 rounded-xl text-sm font-medium
                                       text-red-400 hover:bg-red-50 hover:text-red-600 transition">
                    Vaciar carrito
                </button>
            </form>
        @endif

    </div>

    {{-- FOOTER CON TOTAL Y CHECKOUT --}}
    @if(count($items) > 0)
        <div class="border-t border-gray-100 px-5 py-4 bg-white">

            {{-- RESUMEN --}}
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm text-gray-500">Total</span>
                <span class="text-xl font-bold text-gray-900">${{ $total }}</span>
            </div>

            {{-- BOTÓN CHECKOUT --}}
            <a href="#"
            class="block w-full text-center bg-[#0300a3] hover:bg-[#0200cc] text-white font-semibold py-3 rounded-xl transition">
                Proceder al pago
            </a>

            {{-- SEGUIR COMPRANDO --}}
            <a href="{{ route('catalogo.index') }}"
            class="block w-full text-center text-sm text-white bg-gray-800 hover:bg-gray-900 font-medium py-3 rounded-xl transition mt-3">
                ← Seguir comprando
            </a>
        </div>
    @endif

</div>
@endsection
