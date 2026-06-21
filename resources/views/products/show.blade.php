@extends('layouts.app')
@section('title', $producto['descripcion'] ?? 'Detalle del producto')

@section('content')
@if($errors->any())
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">
            ⚠ {{ $errors->first() }}
        </div>
    </div>
@endif
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    @if(!empty($producto))
    <div class="bg-white border border-gray-200 shadow-[0_10px_40px_rgba(0,0,0,0.06)] overflow-hidden relative">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
            {{-- ===== LADO IZQUIERDO — IMAGEN PRINCIPAL ===== --}}
            <div class="p-5 sm:p-8 lg:p-10 flex items-center justify-center">

                <div class="w-full">
                    <img id="mainImage"
                         src="{{ $producto['imagen_url'] ?? asset('images/no-image.png') }}"
                         alt="{{ $producto['descripcion'] ?? '' }}"
                         class="w-full h-auto object-contain"
                         onerror="this.onerror=null;this.src='https://placehold.co/500x500?text=Sin+imagen'">
                </div>
            </div>

            {{-- ===== LADO DERECHO — INFO + PRESENTACIONES + BOTONES ===== --}}
            <div class="p-5 sm:p-8 lg:p-10 flex flex-col gap-5">

                {{-- NOMBRE --}}
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
                    {{ $producto['descripcion'] ?? '' }}
                </h1>

                {{-- PRECIO --}}
                <div class="flex items-baseline gap-2">
                    <span class="text-4xl sm:text-5xl font-bold text-[#1e1bb3]">
                        ${{ number_format($producto['precio'] ?? 0, 2) }}
                    </span>
                </div>

                {{-- STOCK --}}
                <p class="text-base font-medium" id="stockContainer">
                    @if(($producto['stock_total'] ?? 0) > 0)
                        <span class="text-green-600">
                            Stock disponible:
                            <span class="font-bold">{{ (int) $producto['stock_total'] }}</span> unidades
                        </span>
                    @else
                        <span class="text-red-500 font-bold">Sin stock disponible</span>
                    @endif
                </p>

                {{-- ===== PRESENTACIONES ===== --}}
                @if(count($producto['presentaciones']) > 0)
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-3">
                        Elige un modelo
                        <span class="text-xs text-gray-400 font-normal ml-1">
                            ({{ count($producto['presentaciones']) }} disponibles)
                        </span>
                    </p>

                    {{-- Grid de thumbnails --}}
                    <div class="relative">

                        {{-- Botón anterior --}}
                        <button id="prevBtn" type="button"
                            class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 shadow-sm text-gray-500 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        {{-- Carrusel --}}
                        <div id="carouselContainer"
                             class="flex gap-3 overflow-x-auto scroll-smooth px-10 py-2 no-scrollbar">
                            @foreach($producto['presentaciones'] as $index => $pres)
                                <div onclick="changeImage(this, '{{ $pres->foto_url }}', {{ $pres->codigo }}, {{ $pres->stock_presentacion }})"
                                     data-stock="{{ $pres->stock_presentacion }}"
                                     class="thumbnail flex-shrink-0 w-24 cursor-pointer rounded-xl overflow-hidden border-2 transition-all hover:scale-105
                                            {{ $index === 0 ? 'border-[#0300a3] shadow-md' : 'border-gray-200' }}">
                                    <img src="{{ $pres->foto_url }}"
                                         alt="{{ $pres->nombre }}"
                                         class="w-full aspect-square object-cover"
                                         onerror="this.onerror=null;this.src='https://placehold.co/96x96?text=?'">
                                    <p class="text-center text-[10px] font-medium text-gray-600 px-1 py-1 line-clamp-2 leading-tight">
                                        {{ $pres->nombre }}
                                    </p>
                                    {{-- Badge sin stock --}}
                                    @if($pres->stock_presentacion <= 0)
                                        <p class="text-center text-[9px] font-bold text-red-500 pb-1">Sin stock</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Botón siguiente --}}
                        <button id="nextBtn" type="button"
                            class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-8 h-8 flex items-center justify-center rounded-full bg-white border border-gray-200 shadow-sm text-gray-500 hover:bg-gray-50 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endif

                <style>
                    .no-scrollbar::-webkit-scrollbar { display: none; }
                    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                </style>

                {{-- ===== BOTONES ===== --}}
                <div class="mt-auto space-y-3">

                    {{-- Formulario --}}
                    <form method="POST" action="{{ route('carrito.add') }}" id="formCarrito" onsubmit="agregarAlCarrito(event, this)">
                        @csrf
                        <input type="hidden" name="codigo_item"  value="{{ $producto['codigo'] }}">
                        <input type="hidden" name="nombre"       value="{{ $producto['descripcion'] }}">
                        <input type="hidden" name="pvp3"         value="{{ $producto['precio'] }}">
                        <input type="hidden" name="imagen"       value="{{ $producto['imagen'] }}">
                        <input type="hidden" name="presentacion" id="presentacionSeleccionada" value="0">

                        @if(count($producto['presentaciones']) === 0)
                            {{-- Sin presentaciones --}}
                            @if(($producto['stock_total'] ?? 0) > 0)
                                <button type="submit" id="btnAgregar"
                                        class="w-full bg-[#0300a3] hover:bg-[#0200cc] text-white font-semibold py-4 rounded-2xl flex items-center justify-center gap-3 text-lg transition-all">
                                    <span>Añadir al carrito</span>
                                    <span class="text-2xl">🛒</span>
                                </button>
                            @else
                                <button type="button" id="btnAgregar" disabled
                                        class="w-full bg-gray-200 text-gray-400 font-semibold py-4 rounded-2xl flex items-center justify-center gap-3 text-lg cursor-not-allowed">
                                    <span>Sin stock</span>
                                </button>
                            @endif
                        @else
                            {{-- Con presentaciones: espera selección --}}
                            <button type="button" id="btnAgregar" disabled
                                    class="w-full bg-gray-200 text-gray-500 font-semibold py-4 rounded-2xl flex items-center justify-center gap-3 text-lg cursor-not-allowed transition-all">
                                <span>Elige un modelo 👆</span>
                            </button>
                        @endif
                    </form>

                    {{-- Volver --}}
                    <a href="{{ route('catalogo.index') }}"
                       class="w-full block text-center bg-gray-50 border border-gray-200 hover:bg-gray-100 text-gray-700 font-semibold py-4 rounded-2xl transition-all">
                        ← Volver al catálogo
                    </a>
                </div>

            </div>
        </div>
    </div>
    @else
        <p class="text-red-500 text-center py-20 text-xl">Producto no encontrado.</p>
    @endif
    {{-- ===== PRODUCTOS RELACIONADOS ===== --}}
    @if(!empty($relacionados))
    <div class="mt-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Productos relacionados</h2>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($relacionados as $rel)
                <div class="min-w-0 bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 group">

                    <div class="relative overflow-hidden bg-gray-50">
                        <img src="{{ productImageUrl($rel->imagen) }}"
                            alt="{{ $rel->descripcion1 }}"
                            class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300"
                            onerror="this.onerror=null;this.src='https://placehold.co/300x300?text=Sin+imagen'">

                        @if($rel->categoria)
                            <span class="absolute top-2 left-2 bg-white/90 text-[#0300a3] text-xs font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
                                {{ $rel->categoria }}
                            </span>
                        @endif

                        @if($rel->stock_total > 0)
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
                            <input type="hidden" name="codigo_item" value="{{ $rel->codigo }}">
                            <button type="submit"
                                    class="absolute top-2 right-2 w-8 h-8 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition shadow-sm hover:scale-110">
                                <span class="icon-filled {{ in_array($rel->codigo, $wishCodes ?? []) ? '' : 'hidden' }}">
                                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </span>
                                <span class="icon-outline {{ in_array($rel->codigo, $wishCodes ?? []) ? 'hidden' : '' }}">
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
                            {{ $rel->descripcion1 }}
                        </h3>

                        <p class="text-base font-bold text-gray-900">
                            ${{ number_format($rel->pvp1, 2) }}
                        </p>

                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('products.show', $rel->codigo) }}"
                               class="flex-1 min-h-[42px] flex items-center justify-center text-center text-[11px] whitespace-nowrap text-white bg-gray-800 px-2 py-2 rounded-xl hover:bg-gray-900 transition font-medium">
                                Ver detalle
                            </a>

                            @if($rel->stock_total > 0)
                                @if($rel->tiene_presentaciones)
                                    <a href="{{ route('products.show', $rel->codigo) }}"
                                       class="flex-1 min-h-[42px] flex items-center justify-center text-center text-[11px] whitespace-nowrap text-white bg-[#0300a3] px-2 py-2 rounded-xl hover:bg-[#0200cc] transition font-medium">
                                        Ver modelos
                                    </a>
                                @else
                                    <form method="POST"
                                          action="{{ route('carrito.add') }}"
                                          class="flex-1"
                                          onsubmit="agregarAlCarrito(event, this)">
                                        @csrf
                                        <input type="hidden" name="codigo_item"  value="{{ $rel->codigo }}">
                                        <input type="hidden" name="nombre"       value="{{ $rel->descripcion1 }}">
                                        <input type="hidden" name="pvp3"         value="{{ $rel->pvp1 }}">
                                        <input type="hidden" name="imagen"       value="{{ $rel->imagen }}">
                                        <input type="hidden" name="presentacion" value="0">
                                        <button type="submit"
                                                class="w-full min-h-[42px] flex items-center justify-center text-center text-[11px] whitespace-nowrap text-white bg-[#0300a3] px-2 py-2 rounded-xl hover:bg-[#0200cc] transition font-medium">
                                            + Carrito
                                        </button>
                                    </form>
                                @endif
                            @else
                                <button disabled
                                        class="flex-1 min-h-[42px] flex items-center justify-center text-center text-xs text-gray-400 bg-gray-100 px-2 py-2 rounded-xl cursor-not-allowed font-medium">
                                    Sin stock
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
</div>

<script>
function changeImage(element, newSrc, codigoPresentacion, stockPresentacion) {
    // Cambiar imagen principal
    document.getElementById('mainImage').src = newSrc;

    // Marcar thumbnail seleccionado
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('border-[#0300a3]', 'shadow-md');
        thumb.classList.add('border-gray-200');
    });
    element.classList.add('border-[#0300a3]', 'shadow-md');
    element.classList.remove('border-gray-200');

    // Guardar presentación seleccionada
    document.getElementById('presentacionSeleccionada').value = codigoPresentacion ?? 0;

    // Actualizar stock y botón
    const stockContainer = document.getElementById('stockContainer');
    const btnAgregar     = document.getElementById('btnAgregar');

    if (stockPresentacion > 0) {
        stockContainer.innerHTML = `
            <span class="text-green-600">
                Stock disponible: <span class="font-bold">${Math.floor(stockPresentacion)}</span> unidades
            </span>
        `;
        btnAgregar.type      = 'submit';
        btnAgregar.disabled  = false;
        btnAgregar.className = 'w-full bg-[#0300a3] hover:bg-[#0200cc] text-white font-semibold py-4 rounded-2xl flex items-center justify-center gap-3 text-lg transition-all';
        btnAgregar.innerHTML = '<span>Añadir al carrito</span><span class="text-2xl">🛒</span>';
    } else {
        stockContainer.innerHTML = `
            <span class="text-red-500 font-bold">Sin stock disponible</span>
        `;
        btnAgregar.type      = 'button';
        btnAgregar.disabled  = true;
        btnAgregar.className = 'w-full bg-gray-200 text-gray-400 font-semibold py-4 rounded-2xl flex items-center justify-center gap-3 text-lg cursor-not-allowed transition-all';
        btnAgregar.innerHTML = '<span>Sin stock</span>';
    }
}

// Carrusel con flechas
const container    = document.getElementById('carouselContainer');
const prevBtn      = document.getElementById('prevBtn');
const nextBtn      = document.getElementById('nextBtn');
const scrollAmount = 200;

if (prevBtn) prevBtn.addEventListener('click', () => container.scrollBy({ left: -scrollAmount, behavior: 'smooth' }));
if (nextBtn) nextBtn.addEventListener('click', () => container.scrollBy({ left:  scrollAmount, behavior: 'smooth' }));

document.addEventListener('keydown', (e) => {
    if (!container) return;
    if (e.key === 'ArrowLeft')  container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    if (e.key === 'ArrowRight') container.scrollBy({ left:  scrollAmount, behavior: 'smooth' });
});
</script>
@endsection
