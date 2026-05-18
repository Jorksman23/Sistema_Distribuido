@extends('layouts.app')

@section('title', 'Detalle del producto ' . ($producto['descripcion1'] ?? ''))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">

    @if(!empty($producto))
    <div class="bg-white shadow-xl rounded-3xl overflow-hidden">

        <!-- === CONTENIDO PRINCIPAL === -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">

            <!-- === LADO IZQUIERDO - IMAGEN + PRESENTACIONES === -->
            <div class="bg-gray-50 p-5 sm:p-8 lg:p-10">

                <!-- Imagen Principal -->
                <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 mb-8">
                    <img id="mainImage"
                         src="{{ $producto['imagen_url'] ?? asset('images/no-image.png') }}"
                         alt="{{ $producto['descripcion'] ?? '' }}"
                         class="w-full h-auto max-h-[380px] sm:max-h-[450px] lg:max-h-[520px] object-contain mx-auto">
                </div>

                <!-- Presentaciones -->
                @if(count($producto['presentaciones']) > 0)
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Presentaciones</h3>
                    <div class="relative">
                        <!-- Flecha izquierda -->
                        <button id="prevBtn"
                            class="absolute left-0 top-1/2 transform -translate-y-1/2 w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-orange-50 hover:text-orange-600 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <!-- Carrusel -->
                        <div id="carouselContainer"
                            class="flex overflow-x-auto gap-4 scroll-smooth px-12 py-2 no-scrollbar">
                            @foreach($producto['presentaciones'] as $index => $pres)

                                <div onclick="changeImage(this, '{{ $pres->foto_url }}', {{ $pres->codigo }}, {{ $pres->stock_presentacion }})"
                                    data-stock="{{ $pres->stock_presentacion}}"
                                    class="thumbnail flex-shrink-0 w-32 sm:w-36 md:w-40 cursor-pointer rounded-xl overflow-hidden border-2 transition-all hover:scale-105
                                            {{ $index === 0 ? 'border-blue-600 shadow-md' : 'border-transparent' }}">
                                    <img src="{{ $pres->foto_url }}"
                                        alt="{{ $pres->nombre }}"
                                        class="w-full aspect-square object-cover">
                                    <p class="text-center text-[10px] sm:text-xs font-medium text-gray-600 mt-1.5 line-clamp-2">
                                        {{ $pres->nombre }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                        <!-- Flecha derecha -->
                        <button id="nextBtn"
                            class="absolute right-0 top-1/2 transform -translate-y-1/2 w-9 h-9 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-orange-50 hover:text-orange-600 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                @endif
                <style>
                .no-scrollbar::-webkit-scrollbar { display: none; }
                .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                </style>
            </div>

            <!-- === LADO DERECHO - INFORMACIÓN === -->
            <div class="p-5 sm:p-8 lg:p-10 flex flex-col">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
                    {{ $producto['descripcion'] ?? 'Descripcion' }}
                </h1>

                <div class="mt-6 flex items-baseline gap-2">
                    <span class="text-4xl sm:text-5xl font-bold text-blue-600">
                        ${{ number_format($producto['precio'] ?? 1.40, 2) }}
                    </span>
                </div>

                <p class="mt-3 text-base font-medium" id="stockContainer">
                    @if(($producto['stock_total'] ?? 0) > 0)
                        <span class="text-green-600">
                            Stock disponible: <span class="font-bold">{{ (int) $producto['stock_total'] }}</span> unidades
                        </span>
                    @else
                        <span class="text-red-500 font-bold">
                            Sin stock disponible
                        </span>
                    @endif
                </p>

                <div class="my-10 border-t border-gray-100 pt-8 text-gray-600 leading-relaxed text-sm sm:text-base">
                    <!-- Agrega aquí descripción larga si tienes -->
                </div>
                <div class="my-10 border-t border-gray-100 pt-8 text-gray-600 leading-relaxed text-sm sm:text-base">
                </div>

                <div class="mt-auto space-y-4" id="botonCarrito">
                    @if (count($producto['presentaciones']) === 0)
                        {{--PRODUCTO SIN PRESENTACIONES--}}
                        @if(($producto['stock_total'] ?? 0) > 0)
                            <form method="POST" action="{{ route('carrito.add') }}" id="formCarrito">
                                @csrf
                                <input type="hidden" name="codigo_item"  value="{{ $producto['codigo'] }}">
                                <input type="hidden" name="nombre"        value="{{ $producto['descripcion'] }}">
                                <input type="hidden" name="pvp3"          value="{{ $producto['precio'] }}">
                                <input type="hidden" name="imagen"        value="{{ $producto['imagen'] }}">
                                <input type="hidden" name="presentacion"  id="presentacionSeleccionada" value="0">
                                <button type="submit" id="btnAgregar"
                                        class="w-full bg-[#0300a3] hover:bg-blue-800 text-white font-semibold py-5 sm:py-6 rounded-2xl flex items-center justify-center gap-3 text-base sm:text-lg transition-all">
                                    <span>Añadir al carrito</span>
                                    <span class="text-2xl">🛒</span>
                                </button>
                            </form>
                        @else
                            <button disabled id="btnAgregar"
                                    class="w-full bg-[#0300a3] text-gray-500 font-semibold py-5 sm:py-6 rounded-2xl flex items-center justify-center gap-3 text-base sm:text-lg cursor-not-allowed">
                                <span>Sin stock</span>
                            </button>
                        @endif
                    @else
                        {{-- PRODUCTO CON PRESENTACIONES - Debe elegir primero --}}
                        <form method="POST" action="{{ route('carrito.add') }}" id="formCarrito">
                            @csrf
                            <input type="hidden" name="codigo_item"  value="{{ $producto['codigo'] }}">
                            <input type="hidden" name="nombre"        value="{{ $producto['descripcion'] }}">
                            <input type="hidden" name="pvp3"          value="{{ $producto['precio'] }}">
                            <input type="hidden" name="imagen"        value="{{ $producto['imagen'] }}">
                            <input type="hidden" name="presentacion"  id="presentacionSeleccionada" value="0">
                            <button type="submit" id="btnAgregar" disabled
                                    class="w-full bg-gray-300 text-gray-900 font-semibold py-5 sm:py-6 rounded-2xl flex items-center justify-center gap-3 text-base sm:text-lg cursor-not-allowed transition-all">
                                <span>Elige un diseño</span>
                                <span class="text-2xl">👆</span>
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('catalogo.index') }}"
                    class="w-full block text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-5 sm:py-6 rounded-2xl transition-all">
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
function changeImage(element, newSrc, codigoPresentacion, stockPresentacion) {
    //Cambiar imagen principal
    document.getElementById('mainImage').src = newSrc;
    //Marcar miniatura seleccionada
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('border-blue-600', 'shadow-md');
        thumb.classList.add('border-transparent');
    });

    element.classList.add('border-blue-600', 'shadow-md');
    // Guardar la presentación seleccionada
    document.getElementById('presentacionSeleccionada').value = codigoPresentacion ?? 0;
    //Actualiza stock dinamicamente
    const stockContainer = document.getElementById('stockContainer');
    const btnAgregar = document.getElementById('btnAgregar');
    if(stockPresentacion >0 ){
        stockContainer.innerHTML = `
            <span class="text-green-600">
                Stock disponible: <span class="font-bold">${Math.floor(stockPresentacion)}</span> unidades
            </span>
        `;
        //Hailitar boton
        btnAgregar.ariaDisabled = false;
        btnAgregar.classList.remove('bg-gray-300','text-gray-500','cursor-not-allowed');
        btnAgregar.classList.add('bg-blue-700', 'hover:bg-blue-800', 'text-white');
        btnAgregar.innerHTML = '<span>Añadir al carrito</span><span class="text-2xl">🛒</span>';
    }else{
        stockContainer.innerHTML = `
            <span class="text-red-500 font-bold">
                Sin stock disponible
            </span>
        `;
        // Deshabilitar botón
        btnAgregar.disabled = true;
        btnAgregar.classList.remove('bg-blue-700', 'hover:bg-blue-800', 'text-white');
        btnAgregar.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
        btnAgregar.innerHTML = '<span>Sin stock</span>';
    }
}

// Carrusel con flechas y teclado
const container = document.getElementById('carouselContainer');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');
const scrollAmount = 200;

prevBtn.addEventListener('click', () => {
    container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
});
nextBtn.addEventListener('click', () => {
    container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') {
        container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    } else if (e.key === 'ArrowRight') {
        container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    }
});
</script>
@endsection
