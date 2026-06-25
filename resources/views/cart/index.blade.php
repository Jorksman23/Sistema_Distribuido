@extends('layouts.app')
@section('title', 'Mi Carrito')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">

    {{-- HEADER --}}
    <div class="flex flex-wrap items-center gap-4 mb-8">
        <div class="flex items-center gap-2">
            <svg class="w-7 h-7 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h1 class="whitespace-nowrap text-2xl sm:text-3xl font-bold text-gray-800">Carrito de compras</h1>
        </div>
        <span id="carritoHeaderCount"
              class="bg-[#0300a3] text-white text-xs font-bold px-2.5 py-1 rounded-full {{ $count > 0 ? '' : 'hidden' }}">
            {{ $count }} {{ $count === 1 ? 'producto' : 'productos' }}
        </span>
    </div>

    @if(session('success_cart'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
            ✓ {{ session('success_cart') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            ⚠ {{ $errors->first() }}
        </div>
    @endif

    {{-- ESTADO VACÍO: siempre en el DOM, oculto si hay items --}}
    <div id="carritoVacio"
         class="flex flex-col items-center justify-center py-24 text-gray-400 {{ count($items) === 0 ? '' : 'hidden' }}">
        <svg class="w-20 h-20 mb-4 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <p class="text-lg font-medium text-gray-500">Tu carrito está vacío</p>
        <p class="text-sm mt-1 mb-6">Agrega productos desde el catálogo</p>
        <a href="{{ route('catalogo.index') }}"
           class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-6 py-2.5 rounded-full transition">
            Ir al catálogo
        </a>
    </div>

    @if(count($items) > 0)
    {{-- CONTENIDO DEL CARRITO --}}
    <div id="carritoContenido" class="flex flex-col lg:flex-row gap-8">

        {{-- ===== LISTA DE PRODUCTOS ===== --}}
        <div id="listaProductos" class="flex-1 space-y-4">

            @foreach($items as $item)
                {{-- Clase item-carrito para identificar cada fila --}}
                <div class="item-carrito bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex flex-col sm:flex-row gap-4">

                    {{-- IMAGEN --}}
                    <div class="shrink-0 w-24 h-24 rounded-xl overflow-hidden bg-gray-50 border border-gray-100">
                        <img src="{{ $item->imagen_url }}"
                             alt="{{ $item->nombre }}"
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null;this.src='https://placehold.co/96x96?text=?'">
                    </div>

                    {{-- INFO --}}
                    <div class="flex-1 min-w-0 flex flex-col">
                        <h3 class="whitespace-nowrap text-sm font-semibold text-gray-800 line-clamp-2 leading-snug">
                            {{ $item->nombre }}
                        </h3>

                        @if($item->nombre_presentacion)
                            <span class="whitespace-nowrap inline-block mt-1 text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">
                                {{ $item->nombre_presentacion }}
                            </span>
                        @endif

                        <p class="text-base font-bold text-gray-900 mt-1">
                            ${{ $item->pvp3 }}
                        </p>

                        {{-- CONTROLES CANTIDAD --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-3">
                            <form method="POST" action="{{ route('carrito.update') }}"
                                  class="flex items-center gap-3 flex-wrap"
                                  data-precio="{{ $item->pvp3 }}"
                                  onsubmit="actualizarCantidad(event, this)">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="id_item_web" value="{{ $item->id_item_web }}">
                                <div class="flex items-center border border-gray-200 rounded-xl overflow-hidden bg-gray-50">
                                    <button type="submit" name="cantidad" value="{{ max(1, $item->cantidad - 1) }}"
                                            class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition font-bold">
                                        −
                                    </button>
                                    <span class="cantidad-display w-10 text-center text-sm font-semibold text-gray-800">
                                        {{ $item->cantidad }}
                                    </span>
                                    <button type="submit" name="cantidad" value="{{ $item->cantidad + 1 }}"
                                            class="w-8 h-8 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition font-bold">
                                        +
                                    </button>
                                </div>
                                <span class="text-sm text-gray-500">
                                    = <strong class="text-gray-800 subtotal-fila">${{ number_format($item->pvp3 * $item->cantidad, 2) }}</strong>
                                </span>
                            </form>

                            {{-- ELIMINAR --}}
                            <form method="POST" action="{{ route('carrito.remove') }}"
                                  onsubmit="eliminarProducto(event, this)">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="id_item_web" value="{{ $item->id_item_web }}">
                                <button type="submit"
                                        class="flex items-center gap-1 text-xs text-red-700 font-bold">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- VACIAR CARRITO --}}
            <form method="POST" action="{{ route('carrito.vaciar') }}"
                  onsubmit="vaciarCarrito(event, this)">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1 text-xs text-red-700 font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Limpiar carrito de compras
                </button>
            </form>
        </div>

        {{-- ===== RESUMEN ===== --}}
        <div class="w-full lg:w-80 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 lg:sticky lg:top-6">
                <h2 class="text-lg font-bold text-gray-800 mb-5">Resumen</h2>

                <div class="space-y-3 mb-5">
                    @if($trabajaConIvaIncluido === 'S')
                        {{-- Escenario S: precio sin IVA, se suma encima --}}
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span id="resumen-subtotal" class="font-medium text-gray-800">${{ $subtotal }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>IVA {{ $porcentajeIva }}%</span>
                            <span id="resumen-iva" class="font-medium text-gray-800">${{ $iva }}</span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900">
                            <span>Total</span>
                            <span id="resumen-total" class="text-xl">${{ $total }}</span>
                        </div>
                    @else
                        {{-- Escenario N: precio incluye IVA, se desglosa --}}
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>Subtotal</span>
                            <span id="resumen-subtotal" class="font-medium text-gray-800">${{ $subtotal }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-500">
                            <span>IVA incluido {{ $porcentajeIva }}%</span>
                            <span id="resumen-iva" class="font-medium text-gray-800">${{ $iva }}</span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 flex justify-between font-bold text-gray-900">
                            <span>Total bruto</span>
                            <span id="resumen-total" class="text-xl">${{ $totalBruto }}</span>
                        </div>
                    @endif
                </div>

                <p class="text-xs text-gray-400 mb-4">
                    Al continuar con la compra aceptas nuestros
                    <a href="#" class="text-[#0300a3] hover:underline">términos y condiciones</a>
                </p>

                <a href="{{ route('pedidos.pagar') }}"
                class="block w-full text-center bg-[#0300a3] hover:bg-[#0200cc] text-white font-semibold py-3 rounded-xl transition mb-3">
                    Proceder con el pago
                </a>

                <a href="{{ route('catalogo.index') }}"
                class="block w-full text-center text-sm text-gray-500 hover:text-gray-800 transition py-2">
                    ← Seguir comprando
                </a>
            </div>
        </div>

    </div>
    @endif
</div>
@endsection
