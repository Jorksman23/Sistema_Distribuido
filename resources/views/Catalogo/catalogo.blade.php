@extends('layouts.app')
@section('title', 'Catálogo')

@section('content')
<div class="max-w-8xl mx-auto mt-6 px-4 mb-16">
<div class="flex flex-col lg:flex-row gap-6">

    {{-- ===== SIDEBAR FILTROS ===== --}}
    <aside class="w-full lg:w-72 shrink-0">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 lg:sticky lg:top-6">

            {{-- HEADER DEL FILTRO CON BOTÓN TOGGLE EN MÓVIL --}}
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#3E7CB4]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2"
                            d="M6 4v10m0 0a2 2 0 1 0 0 4m0-4a2 2 0 1 1 0 4m0 0v2m6-16v2m0 0a2 2 0 1 0 0 4m0-4a2 2 0 1 1 0 4m0 0v10m6-16v10m0 0a2 2 0 1 0 0 4m0-4a2 2 0 1 1 0 4m0 0v2"/>
                    </svg>
                    <h2 class="font-extrabold text-gray-900 text-base">Filtros</h2>
                </div>
                {{-- Botón solo visible en móvil --}}
                <button type="button"
                        onclick="toggleFiltros()"
                        class="lg:hidden flex items-center gap-1 text-xs font-semibold text-gray-500 border border-gray-200 px-3 py-1.5 rounded-full hover:border-[#0300a3] hover:text-[#0300a3] transition">
                    <span id="filtrosBtnTexto">Mostrar</span>
                    <svg id="filtrosIcono" class="w-3.5 h-3.5 transition-transform duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            {{-- CONTENIDO DEL FILTRO: oculto en móvil por defecto, visible en lg --}}
            <div id="filtrosContenido" class="hidden lg:block">
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
                            class="w-full bg-[#0300a3] hover:bg-[#0200cc] text-white font-semibold py-2 rounded-xl transition mb-2">
                        Aplicar filtros
                    </button>
                    <a href="{{ route('catalogo.index') }}"
                            class="block w-full text-center text-sm font-semibold text-white bg-gray-800 hover:bg-gray-900 py-2 rounded-xl transition">
                        Limpiar filtros
                    </a>
                </form>
            </div>
        </div>
    </aside>

    {{-- ===== CONTENIDO PRINCIPAL ===== --}}
    <div class="flex-1 min-w-0">

        {{-- HEADER --}}
        <div class="flex items-center justify-between mb-5">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900">Catálogo de Productos</h1>
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
                            <span class="absolute top-2 left-2 bg-white/90 text-[#0300a3] text-xs font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
                                {{ $prod->categoria }}
                            </span>
                        @endif
                        {{-- ETIQUETA STOCK --}}
                        @if($prod->stock_total > 0)
                            <span class="absolute bottom-2 left-2 text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">
                                En stock
                            </span>
                        @else
                            <span class="absolute bottom-2 left-2 text-xs font-semibold text-red-500 bg-red-50 px-2 py-0.5 rounded-full">
                                Sin stock
                            </span>
                        @endif
                        <form method="POST" action="{{ route('wishlist.toggle') }}" onsubmit="toggleWishlist(event, this)">
                        @csrf
                        <input type="hidden" name="codigo_item" value="{{ $prod->codigo }}">
                                <button type="submit"
                                class="absolute top-2 right-2 w-8 h-8 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition shadow-sm hover:scale-110">
                            <span class="icon-filled {{ in_array($prod->codigo, $wishCodes) ? '' : 'hidden' }}">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </span>
                            <span class="icon-outline {{ in_array($prod->codigo, $wishCodes) ? 'hidden' : '' }}">
                                <svg class="w-4 h-4 text-gray-400 hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                            </span>
                            </button>
                        </form>
                    </div>

                    <div class="p-3 flex flex-col flex-1">
    <h3 class="text-sm font-bold text-gray-900 leading-snug mb-2 line-clamp-2 break-words min-h-[2.8rem]">
        {{ $prod->descripcion1 }}
    </h3>

    <p class="text-base font-bold text-gray-900">
        ${{ $prod->pvp1 }}
    </p>

    {{-- BOTONES --}}
    <div class="mt-3 flex gap-2">

        {{-- VER DETALLE --}}
        <a href="{{ route('products.show', $prod->codigo) }}"
           class="flex-1 min-h-[42px] flex items-center justify-center text-center text-[11px] whitespace-nowrap text-white bg-gray-800 px-2 py-2 rounded-xl hover:bg-gray-900 transition font-medium">
            Ver detalle
        </a>

        @if($prod->stock_total > 0)

            @if($prod->tiene_presentaciones)

                {{-- Tiene presentaciones: redirigir al detalle --}}
                <a href="{{ route('products.show', $prod->codigo) }}"
                   class="flex-1 min-h-[42px] flex items-center justify-center text-center text-[11px] whitespace-nowrap text-white bg-[#0300a3] px-2 py-2 rounded-xl hover:bg-[#0200cc] transition font-medium">
                    Ver modelos
                </a>

            @else

                {{-- Sin presentaciones: agregar directo. La ubicación se toma de session('ubicacion_seleccionada')--}}
                <form method="POST"
                      action="{{ route('carrito.add') }}"
                      class="flex-1"
                      onsubmit="agregarAlCarrito(event, this)">
                    @csrf

                    <input type="hidden" name="codigo_item"  value="{{ $prod->codigo }}">
                    <input type="hidden" name="nombre"       value="{{ $prod->descripcion1 }}">
                    <input type="hidden" name="pvp3"         value="{{ $prod->pvp1 }}">
                    <input type="hidden" name="imagen"       value="{{ $prod->imagen }}">
                    <input type="hidden" name="presentacion" value="0">

                    <button type="submit"
                            class="w-full min-h-[42px] flex items-center justify-center text-center text-[11px] whitespace-nowrap text-white bg-[#0300a3] px-2 py-2 rounded-xl hover:bg-[#0200cc] transition font-medium">
                        + Carrito
                    </button>
                </form>

            @endif

        @else

            {{-- SIN STOCK --}}
            <button disabled
                    class="flex-1 min-h-[42px] flex items-center justify-center text-center text-xs text-gray-400 bg-gray-100 px-2 py-2 rounded-xl cursor-not-allowed font-medium">
                Sin stock
            </button>

        @endif

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
<script>
function toggleFiltros() {
    const contenido = document.getElementById('filtrosContenido');
    const btnTexto  = document.getElementById('filtrosBtnTexto');
    const icono     = document.getElementById('filtrosIcono');

    const abierto = !contenido.classList.contains('hidden');

    if (abierto) {
        contenido.classList.add('hidden');
        btnTexto.textContent = 'Mostrar';
        icono.style.transform = 'rotate(0deg)';
    } else {
        contenido.classList.remove('hidden');
        btnTexto.textContent = 'Ocultar';
        icono.style.transform = 'rotate(180deg)';
    }
}
</script>
