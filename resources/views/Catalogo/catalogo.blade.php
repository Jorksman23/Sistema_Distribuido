@extends('layouts.app')
@section('title', 'Catálogo')

@section('content')
<div class="max-w-8xl mx-auto mt-6 px-4 mb-16">
<div class="flex flex-col lg:flex-row gap-6">

    {{-- ===== SIDEBAR FILTROS ===== --}}
    <aside class="w-full lg:w-72 shrink-0">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:sticky lg:top-6">

            <div class="flex items-center gap-2 mb-5">
                <svg class="w-5 h-5 text-[#0300a3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                <h2 class="font-bold text-gray-800 text-base">Filtros</h2>
            </div>

            <form method="GET" action="{{ route('catalogo.index') }}">

                {{-- BÚSQUEDA --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Búsqueda</label>
                    <input type="text" name="q" value="{{ $filters['search'] }}"
                           placeholder="Buscar producto..."
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3]/30">
                </div>

                {{-- GRUPO --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Grupo</label>
                    <select name="grupo"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3]/30">
                        <option value="">Todos</option>
                        @foreach($grupos as $g)
                            <option value="{{ $g['codigo'] }}"
                                {{ $filters['grupo'] == $g['codigo'] ? 'selected' : '' }}>
                                {{ $g['grupo'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- LÍNEA --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Línea</label>
                    <select name="linea"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3]/30">
                        <option value="">Todas</option>
                        @foreach($lineas as $l)
                            <option value="{{ $l['codigo'] }}"
                                {{ $filters['linea'] == $l['codigo'] ? 'selected' : '' }}>
                                {{ $l['linea'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- UBICACIÓN --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Ubicación</label>
                    <select name="ubicacion"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3]/30">
                        <option value="">Todas</option>
                        @foreach($ubicaciones as $u)
                            <option value="{{ $u['codigo'] }}"
                                {{ $filters['ubicacion'] == $u['codigo'] ? 'selected' : '' }}>
                                {{ $u['ubicacion'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- RANGO DE PRECIO --}}
                <div class="mb-5">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Precio</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="precio_min" min="0" step="0.01"
                               value="{{ $filters['precioMin'] ?: '' }}"
                               placeholder="Min"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3]/30">
                        <span class="text-gray-400 text-sm">—</span>
                        <input type="number" name="precio_max" min="0" step="0.01"
                               value="{{ $filters['precioMax'] ?: '' }}"
                               placeholder="Max"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3]/30">
                    </div>
                </div>

                {{-- ORDENAR POR --}}
                <div class="mb-6">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Ordenar por</label>
                    <select name="orden"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3]/30">
                        <option value="codigo"      {{ $filters['orden'] === 'codigo'      ? 'selected' : '' }}>Por defecto</option>
                        <option value="nombre"      {{ $filters['orden'] === 'nombre'      ? 'selected' : '' }}>Nombre</option>
                        <option value="precio_asc"  {{ $filters['orden'] === 'precio_asc'  ? 'selected' : '' }}>Precio ascendente</option>
                        <option value="precio_desc" {{ $filters['orden'] === 'precio_desc' ? 'selected' : '' }}>Precio descendente</option>
                    </select>
                </div>

                {{-- BOTONES --}}
                <button type="submit"
                        class="w-full bg-[#0300a3] hover:bg-[#0200cc] text-white font-semibold py-2.5 rounded-xl transition mb-2">
                    Aplicar filtros
                </button>
                <a href="{{ route('catalogo.index') }}"
                        class="block w-full text-center text-sm font-semibold text-white bg-gray-800 hover:bg-gray-900 py-2 rounded-xl transition">
                    Limpiar filtros
                </a>
            </form>
        </div>
    </aside>

    {{-- ===== CONTENIDO PRINCIPAL ===== --}}
    <div class="flex-1 min-w-0">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Catálogo de Productos</h1>
                <p class="text-sm text-gray-400 mt-1">{{ $total }} productos encontrados</p>
            </div>
        </div>

        {{-- GRID --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @forelse($productos as $prod)
                <div class="min-w-0 bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 group">

                    <div class="relative overflow-hidden bg-gray-50">
                        <img src="{{ $prod->imagen_url }}"
                             alt="{{ $prod->descripcion1 }}"
                             class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300"
                             onerror="this.onerror=null;this.src='https://placehold.co/300x300?text=Sin+imagen'">

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

                    <div class="p-3">
                        <h3 class="text-xs font-semibold text-gray-800 leading-snug mb-2 line-clamp-2 min-h-[2rem]">
                            {{ $prod->descripcion1 }}
                        </h3>
                        <p class="text-base font-bold text-gray-900">${{ $prod->pvp1 }}</p>
                        <div class="mt-2 flex gap-1.5">
                            <a href="{{ route('products.show', $prod->codigo) }}"
                               class="flex-1 text-center text-xs text-white bg-gray-800 px-2 py-1.5 rounded-lg hover:bg-gray-900 transition font-medium">
                               Ver detalle
                            </a>
                            <button onclick="addToCart('{{ $prod->codigo }}')"
                                    class="flex-1 text-xs text-white bg-[#0300a3] px-2 py-1.5 rounded-lg hover:bg-[#0200cc] transition font-medium">
                                + Carrito
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-4 text-center py-20 text-gray-400">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="font-medium">No hay productos con esos filtros.</p>
                    <a href="{{ route('catalogo.index') }}" class="text-sm text-orange-400 hover:underline mt-2 inline-block">Limpiar filtros</a>
                </div>
            @endforelse
        </div>

        {{-- PAGINACIÓN --}}
        @if($lastPage > 1)
        @php
            $queryParams = array_filter([
                'q'          => $filters['search'],
                'grupo'      => $filters['grupo'],
                'linea'      => $filters['linea'],
                'ubicacion'  => $filters['ubicacion'],
                'precio_min' => $filters['precioMin'] ?: null,
                'precio_max' => $filters['precioMax'] ?: null,
                'orden'      => $filters['orden'] !== 'codigo' ? $filters['orden'] : null,
            ]);
        @endphp

        <div class="flex items-center justify-center mt-8 gap-1">

            @if($currentPage > 1)
                <a href="?{{ http_build_query(array_merge($queryParams, ['page' => $currentPage - 1])) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-[#0300a3] hover:text-[#0300a3] transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
            @endif

            @php $start = max(1, $currentPage - 2); $end = min($lastPage, $currentPage + 2); @endphp

            @if($start > 1)
                <a href="?{{ http_build_query(array_merge($queryParams, ['page' => 1])) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-full text-sm text-gray-600 hover:bg-orange-50 transition">1</a>
                @if($start > 2)
                    <span class="w-9 h-9 flex items-center justify-center text-gray-400">…</span>
                @endif
            @endif

            @for($i = $start; $i <= $end; $i++)
                <a href="?{{ http_build_query(array_merge($queryParams, ['page' => $i])) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-full text-sm font-medium transition
                          {{ $i === $currentPage ? 'bg-[#0300a3] text-white shadow-sm' : 'text-gray-600 hover:bg-[#0300a3]/10 hover:text-[#0300a3]' }}">
                    {{ $i }}
                </a>
            @endfor

            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span class="w-9 h-9 flex items-center justify-center text-gray-400">…</span>
                @endif
                <a href="?{{ http_build_query(array_merge($queryParams, ['page' => $lastPage])) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-full text-sm text-gray-600 hover:bg-orange-50 transition">{{ $lastPage }}</a>
            @endif

            @if($currentPage < $lastPage)
                <a href="?{{ http_build_query(array_merge($queryParams, ['page' => $currentPage + 1])) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:border-[#0300a3] hover:text-[#0300a3] transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @endif

        </div>
        <p class="text-center text-xs text-gray-400 mt-3">Página {{ $currentPage }} de {{ $lastPage }}</p>
        @endif

    </div>
</div>
</div>
@endsection
