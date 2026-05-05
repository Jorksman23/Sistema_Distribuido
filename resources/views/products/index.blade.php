@extends('layouts.app')

@section('title', 'Productos')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Catálogo de Productos</h1>

   <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
    @forelse($productos as $prod)
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <img src="{{ $prod->imagen_url }}" alt="{{ $prod->descripcion1 }}" class="w-full h-40 object-cover">
            <div class="p-4">
                <h2 class="text-lg font-semibold">{{ $prod->descripcion1 }}</h2>
                <p class="text-blue-600 font-bold">${{ $prod->pvp1 }}</p>
            </div>
        </div>
    @empty
        <p class="text-gray-500">No hay productos disponibles.</p>
    @endforelse
</div>

</div>
@endsection
