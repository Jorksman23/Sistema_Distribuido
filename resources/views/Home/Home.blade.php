@extends('layouts.app')
@section('title', 'Tienda')

@section('content')
{{-- ===== CARRUSEL INFINITO ===== --}}
@if(isset($carrusel) && count($carrusel) > 0)
<div class="mt-12 mb-4">
    <div class="max-w-7xl mx-auto px-4 mb-4 flex items-center justify-between">
        <h2 class="text-2xl font-bold text-gray-800">Productos Destacados</h2>
        <a href="{{ route('catalogo.index') }}" class="text-sm text-[#0300a3] hover:underline font-medium">Ver todos →</a>
    </div>

    <div class="overflow-hidden relative">

        <div class="carrusel-track flex gap-4 py-4 px-2">

            {{-- Primera pasada --}}
            @foreach($carrusel as $prod)
                <div class="shrink-0 w-48 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <div class="relative h-40 bg-gray-50 overflow-hidden">
                        <img src="{{ $prod->imagen_url }}"
                             alt="{{ $prod->descripcion1 }}"
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                             onerror="this.onerror=null;this.src='https://placehold.co/200x160?text=Sin+imagen'">
                        @if($prod->categoria)
                            <span class="absolute top-2 left-2 bg-white/90 text-[#0300a3] text-xs font-bold px-2 py-0.5 rounded-full uppercase">
                                {{ $prod->categoria }}
                            </span>
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="text-xs font-semibold text-gray-700 line-clamp-2 min-h-[2rem] mb-1">
                            {{ $prod->descripcion1 }}
                        </h3>
                        <p class="text-sm font-bold text-gray-900">${{ $prod->pvp1 }}</p>
                        <a href="{{ route('products.show', $prod->codigo) }}"
                           class="mt-2 block text-center text-xs text-white bg-gray-800 hover:bg-gray-900 py-1.5 rounded-lg transition font-medium">
                            Ver detalle
                        </a>
                    </div>
                </div>
            @endforeach

            {{-- Segunda pasada (duplicado para loop infinito) --}}
            @foreach($carrusel as $prod)
                <div class="shrink-0 w-48 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                    <div class="relative h-40 bg-gray-50 overflow-hidden">
                        <img src="{{ $prod->imagen_url }}"
                             alt="{{ $prod->descripcion1 }}"
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                             onerror="this.onerror=null;this.src='https://placehold.co/200x160?text=Sin+imagen'">
                        @if($prod->categoria)
                            <span class="absolute top-2 left-2 bg-white/90 text-[#0300a3] text-xs font-bold px-2 py-0.5 rounded-full uppercase">
                                {{ $prod->categoria }}
                            </span>
                        @endif
                    </div>
                    <div class="p-3">
                        <h3 class="text-xs font-semibold text-gray-700 line-clamp-2 min-h-[2rem] mb-1">
                            {{ $prod->descripcion1 }}
                        </h3>
                        <p class="text-sm font-bold text-gray-900">${{ $prod->pvp1 }}</p>
                        <a href="{{ route('products.show', $prod->codigo) }}"
                           class="mt-2 block text-center text-xs text-white bg-gray-800 hover:bg-gray-900 py-1.5 rounded-lg transition font-medium">
                            Ver detalle
                        </a>
                    </div>
                </div>
            @endforeach

        </div>{{-- fin carrusel-track --}}
    </div>{{-- fin overflow-hidden --}}
</div>{{-- fin carrusel --}}
@endif

{{-- CATEGORÍAS --}}
<div class="max-w-7xl mx-auto mt-10 px-4">
    <h2 class="text-2xl font-bold mb-4">Categorías</h2>
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded shadow text-center">Home</div>
        <div class="bg-white p-6 rounded shadow text-center">Electronics</div>
        <div class="bg-white p-6 rounded shadow text-center">Appliances</div>
        <div class="bg-white p-6 rounded shadow text-center">Outdoor</div>
    </div>
</div>

{{-- PRODUCTOS DESTACADOS --}}
<div class="max-w-7xl mx-auto mt-10 px-4">
    <h2 class="text-2xl font-bold mb-4">Deals of the Day</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @forelse($productos ?? [] as $prod)
            <div class="bg-white shadow rounded-lg overflow-hidden hover:shadow-lg transition">
                <img src="{{ $prod->imagen_url }}" alt="{{ $prod->descripcion1 }}"
                     class="w-full h-40 object-cover">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-700 truncate">
                        {{ $prod->descripcion1 }}
                    </h3>
                    <p class="text-blue-600 font-bold mt-2">${{ $prod->pvp1 }}</p>
                    <p class="text-xs text-gray-500">Stock: {{ $prod->stock }}</p>

                    <div class="mt-4 flex justify-between items-center">
                        <a href="{{ route('products.show', $prod->codigo) }}"
                           class="text-sm text-white bg-blue-600 px-3 py-1 rounded hover:bg-blue-700">
                           Ver detalle
                        </a>
                        <button onclick="addToCart('{{ $prod->codigo }}')"
                                class="text-sm text-white bg-green-600 px-3 py-1 rounded hover:bg-green-700">
                            Añadir 🛒
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500">No hay productos destacados disponibles.</p>
        @endforelse
    </div>
</div>

@endsection

@push('scripts')
<style>
    .carrusel-track {
        animation: scrollCarrusel 30s linear infinite;
        width: max-content;
    }
    .carrusel-track:hover {
        animation-play-state: paused;
    }
    @keyframes scrollCarrusel {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
</style>
@endpush
