@extends('layouts.app')
@section('title', 'Tienda')

@section('content')

{{-- HERO --}}
<div class="max-w-7xl mx-auto mt-6 px-4">
    <div class="bg-gradient-to-r from-orange-400 to-yellow-500 rounded-xl p-10 text-white">
        <h2 class="text-4xl font-bold mb-3">Elevate Your Lifestyle</h2>
        <p>Productos premium con ofertas</p>
        <a href="{{ route('products.index') }}"
           class="mt-4 inline-block bg-blue-600 px-5 py-2 rounded text-white hover:bg-blue-700">
           Ver productos
        </a>
    </div>
</div>

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
