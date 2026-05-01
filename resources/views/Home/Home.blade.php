@extends('layouts.app')
@section('title', 'Home')

@section('content')

{{-- HERO --}}
<div class="max-w-7xl mx-auto mt-6 px-4">
    <div class="bg-gradient-to-r from-orange-400 to-yellow-500 rounded-xl p-10 text-white">
        <h2 class="text-4xl font-bold mb-3">Elevate Your Lifestyle</h2>
        <p>Productos premium con ofertas</p>
        <button class="mt-4 bg-blue-600 px-5 py-2 rounded">Ver productos</button>
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

{{-- PRODUCTOS --}}
<div class="max-w-7xl mx-auto mt-10 px-4">
    <h2 class="text-2xl font-bold mb-4">Deals of the Day</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        {{-- aquí irán los productos cuando conectemos la tabla in_item --}}
    </div>
</div>
@endsection
