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
                <h3 class="text-lg font-semibold mb-4 text-gray-800">Presentaciones</h3>

                <div id="thumbnails" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3 sm:gap-4">
                    @foreach($producto['presentaciones'] as $index => $pres)
                        <div onclick="changeImage(this, '{{ $pres->foto_url }}')"
                             class="thumbnail group cursor-pointer rounded-xl overflow-hidden border-2 transition-all hover:scale-105
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
            </div>

            <!-- === LADO DERECHO - INFORMACIÓN === -->
            <div class="p-5 sm:p-8 lg:p-10 flex flex-col">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">
                    {{ $producto['descripcion'] ?? 'Perfumes economicos para caballero 100 ML' }}
                </h1>

                <div class="mt-6 flex items-baseline gap-2">
                    <span class="text-4xl sm:text-5xl font-bold text-blue-600">
                        ${{ number_format($producto['precio'] ?? 1.40, 2) }}
                    </span>
                </div>

                <p class="mt-3 text-base text-green-600 font-medium">
                    Stock: <span class="font-semibold">{{ $producto['stock'] ?? 'S' }}</span>
                </p>

                <div class="my-10 border-t border-gray-100 pt-8 text-gray-600 leading-relaxed text-sm sm:text-base">
                    <!-- Agrega aquí descripción larga si tienes -->
                </div>

                <div class="mt-auto space-y-4">
                    <button onclick="addToCart('{{ $producto['codigo'] }}')"
                            class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-5 sm:py-6 rounded-2xl flex items-center justify-center gap-3 text-base sm:text-lg transition-all">
                        <span>Añadir al carrito</span>
                        <span class="text-2xl">🛒</span>
                    </button>

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
function changeImage(element, newSrc) {
    document.getElementById('mainImage').src = newSrc;

    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('border-blue-600', 'shadow-md');
        thumb.classList.add('border-transparent');
    });

    element.classList.add('border-blue-600', 'shadow-md');
}

function addToCart(codigo) {
    alert(`✅ Producto ${codigo} añadido al carrito`);
}
</script>
@endsection
