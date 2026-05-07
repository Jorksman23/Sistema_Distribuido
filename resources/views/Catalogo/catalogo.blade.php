@extends('layouts.app')
@section('title', 'Catálogo')

@section('content')
<div class="max-w-7xl mx-auto mt-8 px-4 mb-16">

    {{-- HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Catálogo de Productos</h1>
            <p class="text-sm text-gray-400 mt-1">{{ $total }} productos encontrados</p>
        </div>
    </div>

    {{-- GRID --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
        @forelse($productos as $prod)
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 group">

                <div class="relative overflow-hidden bg-gray-50">
                    <img src="{{ $prod->imagen_url }}"
                         alt="{{ $prod->descripcion1 }}"
                         class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                         onerror="this.src='https://via.placeholder.com/300x200?text=Sin+imagen'">

                    @if($prod->categoria)
                        <span class="absolute top-2 left-2 bg-white/90 text-blue-600 text-xs font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
                            {{ $prod->categoria }}
                        </span>
                    @endif

                    <button class="absolute top-2 right-2 w-8 h-8 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition shadow-sm">
                        <svg class="w-4 h-4 text-gray-400 hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </button>
                </div>

                <div class="p-4">
                    <h3 class="text-sm font-semibold text-gray-800 leading-snug mb-3 line-clamp-2 min-h-[2.5rem]">
                        {{ $prod->descripcion1 }}
                    </h3>
                    <p class="text-lg font-bold text-gray-900">${{ $prod->pvp1 }}</p>

                    <div class="mt-3 flex gap-2">
                        <a href="{{ route('products.show', $prod->codigo) }}"
                           class="flex-1 text-center text-xs text-white bg-gray-800 px-2 py-2 rounded-xl hover:bg-gray-900 transition font-medium">
                           Ver detalle
                        </a>
                        <button onclick="addToCart('{{ $prod->codigo }}')"
                                class="flex-1 text-xs text-white bg-orange-400 px-2 py-2 rounded-xl hover:bg-orange-500 transition font-medium">
                            + Carrito
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-4 text-center py-20 text-gray-400">
                <p>No hay productos disponibles.</p>
            </div>
        @endforelse
    </div>

    {{-- PAGINACIÓN --}}
    @if($lastPage > 1)
    <div class="flex items-center justify-center mt-10 gap-1">

        @if($currentPage > 1)
            <a href="?page={{ $currentPage - 1 }}"
               class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-orange-400 hover:text-orange-500 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        @php
            $start = max(1, $currentPage - 2);
            $end   = min($lastPage, $currentPage + 2);
        @endphp

        @if($start > 1)
            <a href="?page=1" class="w-9 h-9 flex items-center justify-center rounded-full text-sm text-gray-600 hover:bg-orange-50 transition">1</a>
            @if($start > 2)
                <span class="w-9 h-9 flex items-center justify-center text-gray-400">…</span>
            @endif
        @endif

        @for($i = $start; $i <= $end; $i++)
            <a href="?page={{ $i }}"
               class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-medium transition
                      {{ $i === $currentPage ? 'bg-orange-400 text-white shadow-sm' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-500' }}">
                {{ $i }}
            </a>
        @endfor

        @if($end < $lastPage)
            @if($end < $lastPage - 1)
                <span class="w-9 h-9 flex items-center justify-center text-gray-400">…</span>
            @endif
            <a href="?page={{ $lastPage }}" class="w-9 h-9 flex items-center justify-center rounded-full text-sm text-gray-600 hover:bg-orange-50 transition">{{ $lastPage }}</a>
        @endif

        @if($currentPage < $lastPage)
            <a href="?page={{ $currentPage + 1 }}"
               class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-orange-400 hover:text-orange-500 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @endif

    </div>

    <p class="text-center text-xs text-gray-400 mt-3">
        Página {{ $currentPage }} de {{ $lastPage }}
    </p>
    @endif

</div>
@endsection
