@extends('layouts.app')

@section('title', 'Detalle de producto')

@section('content')
<div class="max-w-4xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Detalle del producto {{ $codigo }}</h1>

    @if(!empty($producto))
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <img src="{{ $producto[0]['foto_url'] ?? '' }}" alt="Imagen del producto"
                 class="w-full h-64 object-cover">
            <div class="p-6">
                <h2 class="text-xl font-semibold text-gray-700">
                    {{ $producto[0]['descripcion'] ?? '' }}
                </h2>
                <p class="text-blue-600 font-bold mt-2">${{ $producto[0]['precio'] ?? '' }}</p>
                <p class="text-sm text-gray-500 mt-1">Stock: {{ $producto[0]['stock'] ?? '' }}</p>

                <p class="mt-4 text-gray-600">
                    Presentación: {{ $producto[0]['nombre'] ?? '' }}
                </p>

                <div class="mt-6 flex gap-4">
                    <button onclick="addToCart('{{ $codigo }}')"
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
