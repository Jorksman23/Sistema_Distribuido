@extends('layouts.app')
@section('title', 'Mi Lista de Deseos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 mb-16">

    {{-- ENCABEZADO --}}
    <div class="flex items-center gap-3 mb-8">
        <svg class="w-7 h-7 text-red-500" fill="currentColor" viewBox="0 0 24 24">
            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
        </svg>
        <h1 class="text-3xl font-bold text-gray-900">Mi Lista de Deseos</h1>
        <span class="ml-2 bg-red-100 text-red-600 text-sm font-semibold px-3 py-0.5 rounded-full">
            {{ count($items) }} {{ count($items) === 1 ? 'producto' : 'productos' }}
        </span>
    </div>

    @if(count($items) === 0)
        {{-- ESTADO VACÍO --}}
        <div class="flex flex-col items-center justify-center py-24 text-gray-400">
            <svg class="w-20 h-20 mb-4 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <p class="text-lg font-medium text-gray-500">Tu lista de deseos está vacía</p>
            <p class="text-sm mt-1">Agrega productos desde el catálogo</p>
            <a href="{{ route('catalogo.index') }}"
               class="mt-6 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium px-6 py-2.5 rounded-full transition">
                Ir al catálogo
            </a>
        </div>

    @else
        {{-- GRID DE PRODUCTOS --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($items as $item)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-md transition-all duration-300">

                    {{-- IMAGEN --}}
                    <div class="relative overflow-hidden bg-gray-50">
                        <a href="{{ route('products.show', $item->codigo_item) }}">
                            <img src="{{ $item->imagen_url }}"
                                 alt="{{ $item->nombre }}"
                                 class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.onerror=null;this.src='https://placehold.co/300x300?text=Sin+imagen'">
                        </a>

                        {{-- BOTÓN ELIMINAR DE WISHLIST --}}
                        <form method="POST" action="{{ route('wishlist.toggle') }}"
                            class="absolute top-2 right-2">
                            @csrf
                            <input type="hidden" name="codigo_item" value="{{ $item->codigo_item }}">
                            <input type="hidden" name="redirect_to"  value="wishlist">
                            <button type="submit"
                                    class="w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm hover:scale-110 transition"
                                    title="Quitar de favoritos">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </button>
                        </form>
                    </div>

                    {{-- INFO --}}
                    <div class="p-3">
                        <h3 class="text-xs font-semibold text-gray-800 line-clamp-2 min-h-[2rem] mb-1">
                            {{ $item->nombre }}
                        </h3>
                        <p class="text-sm font-bold text-gray-900">${{ $item->pvp3 }}</p>
                        <div class="mt-2 flex gap-1.5">
                            <a href="{{ route('products.show', $item->codigo_item) }}"
                            class="flex-1 text-center text-xs text-white bg-gray-800 hover:bg-gray-900 py-1.5 rounded-lg transition font-medium">
                                Ver detalle
                            </a>
                            <button onclick="addToCart('{{ $item->codigo_item }}')"
                                    class="flex-1 text-xs text-white bg-[#0300a3] hover:bg-[#0200cc] py-1.5 px-2 rounded-lg transition font-medium">
                                + Carrito
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
