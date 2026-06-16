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
    <div class="bg-white border border-gray-200 shadow-[0_10px_40px_rgba(0,0,0,0.06)] overflow-hidden">
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
                    <form method="POST" action="{{ route('carrito.add') }}" id="formCarrito">
                        @csrf
                        <input type="hidden" name="codigo_item"  value="{{ $producto['codigo'] }}">
                        <input type="hidden" name="nombre"       value="{{ $producto['descripcion'] }}">
                        <input type="hidden" name="pvp3"         value="{{ $producto['precio'] }}">
                        <input type="hidden" name="imagen"       value="{{ $producto['imagen'] }}">
                        <input type="hidden" name="presentacion" id="presentacionSeleccionada" value="0">
                        <input type= "hidden" name = "ubicacion" id="ubicacionSeleccionada" value = "">

                        {{-- SELECTOR DE UBICACIÓN --}}
                        @if(count($producto['presentaciones']) === 0)
                            @if(count($ubicaciones) > 1)
                                <div class="mb-4">
                                    <p class="text-sm font-semibold text-gray-900 mb-2">
                                        Elige una ubicación
                                    </p>
                                    <select id="selectUbicacion"
                                            class="w-full border-2 border-gray-800 rounded-xl px-4 py-3 bg-white text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3] focus:border-[#0300a3]"
                                            onchange="document.getElementById('ubicacionSeleccionada').value = this.value">
                                        @foreach($ubicaciones as $ub)
                                            <option value="{{ $ub->ubicacion }}">
                                                {{ $ub->nombre_ubicacion }} — {{ (int)$ub->stock }} unidades
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif(count($ubicaciones) === 1)
                                {{-- Una sola ubicación: se envía sin mostrar selector --}}
                                <script>
                                    document.getElementById('ubicacionSeleccionada').value = '{{ $ubicaciones[0]->ubicacion }}';
                                </script>
                            @endif
                        @else
                            {{-- Ubicaciones dinámicas por presentación --}}
                            <div id="ubicacionDinamica" class="hidden mb-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">Elige una ubicación</p>
                                <select id="selectUbicacionDinamica"
                                        class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0300a3]"
                                        onchange="document.getElementById('ubicacionSeleccionada').value = this.value">
                                </select>
                            </div>
                        @endif

                        @if(count($producto['presentaciones']) === 0)
                            {{-- Sin presentaciones --}}
                            @if(($producto['stock_total'] ?? 0) > 0)
                                <button type="button" id="btnAgregar"
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
</div>

<script>
const ubicacionesPorPresentacion = @json($ubicacionesPorPresentacion);

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

    // Actualizar ubicaciones dinámicas
    const ubicacionDinamica      = document.getElementById('ubicacionDinamica');
    const selectUbicacionDinamica = document.getElementById('selectUbicacionDinamica');
    const ubicacionSeleccionada  = document.getElementById('ubicacionSeleccionada');

    const ubicaciones = ubicacionesPorPresentacion[codigoPresentacion] ?? [];

    if (ubicaciones.length > 0 && stockPresentacion > 0) {
        selectUbicacionDinamica.innerHTML = ubicaciones.map(u =>
            `<option value="${u.ubicacion}">${u.nombre_ubicacion} — ${Math.floor(u.stock)} unidades</option>`
        ).join('');
        // Preseleccionar la primera
        ubicacionSeleccionada.value = ubicaciones[0].ubicacion;
        ubicacionDinamica.classList.remove('hidden');

        // Actualizar cuando el usuario cambia
        selectUbicacionDinamica.onchange = function() {
            ubicacionSeleccionada.value = this.value;
        };
    } else {
        ubicacionDinamica.classList.add('hidden');
        ubicacionSeleccionada.value = '';
    }

    // Actualizar stock y botón
    const stockContainer = document.getElementById('stockContainer');
    const btnAgregar     = document.getElementById('btnAgregar');

    if (stockPresentacion > 0) {
        stockContainer.innerHTML = `
            <span class="text-green-600">
                Stock disponible: <span class="font-bold">${Math.floor(stockPresentacion)}</span> unidades
            </span>
        `;
        btnAgregar.disabled  = false;
        btnAgregar.className = 'w-full bg-[#0300a3] hover:bg-[#0200cc] text-white font-semibold py-4 rounded-2xl flex items-center justify-center gap-3 text-lg transition-all';
        btnAgregar.innerHTML = '<span>Añadir al carrito</span><span class="text-2xl">🛒</span>';
    } else {
        stockContainer.innerHTML = `
            <span class="text-red-500 font-bold">Sin stock disponible</span>
        `;
        btnAgregar.disabled  = true;
        btnAgregar.className = 'w-full bg-gray-200 text-gray-400 font-semibold py-4 rounded-2xl flex items-center justify-center gap-3 text-lg cursor-not-allowed transition-all';
        btnAgregar.innerHTML = '<span>Sin stock</span>';
        ubicacionDinamica.classList.add('hidden');
        ubicacionSeleccionada.value = '';
    }
}

// Envío del formulario — un solo clic
const formCarrito = document.getElementById('formCarrito');
const btnAgregar  = document.getElementById('btnAgregar');

if (btnAgregar && formCarrito) {
    btnAgregar.addEventListener('click', function () {
        if (this.disabled) return;
        this.disabled  = true;
        this.innerHTML = '<span>Agregando...</span>';
        formCarrito.submit();
    });
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

function bloquearOtrasUbicaciones(selectElement, valorElegido) {
    Array.from(selectElement.options).forEach(opt => {
        if (opt.value !== valorElegido) {
            opt.disabled = true;
            opt.style.color = '#ccc';
        }
    });
}
</script>
@endsection
