@extends('layouts.app')

@section('title', 'Detalle de producto')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">
        Detalle del producto {{ $producto['codigo'] ?? '' }}
    </h1>

    @if(!empty($producto))
        <div class="bg-white shadow rounded-lg overflow-hidden">
            {{-- Imagen principal --}}
            <img src="{{ $producto['imagen_url'] ?? '' }}" alt="Imagen del producto"
                 class="w-full h-64 object-cover">

            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-700">
                    {{ $producto['descripcion'] ?? '' }}
                </h2>
                <p class="text-blue-600 font-bold mt-2">${{ $producto['precio'] ?? '' }}</p>
                <p class="text-sm text-gray-500 mt-1">Stock: {{ $producto['stock'] ?? '' }}</p>

                {{-- Presentaciones --}}
                @if(!empty($producto['presentaciones']))
                    <h3 class="text-lg font-bold mt-6">Presentaciones</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                        @foreach($producto['presentaciones'] as $pres)
                            <div class="bg-gray-100 p-2 rounded">
                                <img src="{{ $pres->foto_url }}" alt="{{ $pres->nombre }}"
                                     class="w-full h-32 object-cover">
                                <p class="text-sm text-gray-700 mt-2">{{ $pres->nombre }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex gap-4">
                    <button onclick="addToCart('{{ $producto['codigo'] }}')"
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                        Añadir al carrito 🛒
                    </button>
                    <a href="{{ route('products.index') }}"
                       class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                        Volver al catálogo
                    </a>
                </div>
            </div>
        </div>
    @else
        <p class="text-red-500">No se encontró información para este producto.</p>
    @endif
</div>
@endsection
