@extends('layouts.app')

@section('title', 'Pedido Confirmado')

@section('content')
<div class="max-w-2xl mx-auto px-6 py-12">
    <div class="bg-white rounded-2xl shadow-lg p-10 text-center">
        <div class="text-6xl mb-6">✅</div>
        <h1 class="text-3xl font-bold text-[#003087] mb-2">¡Pedido Realizado con Éxito!</h1>
        <p class="text-gray-600 mb-8">Gracias por tu compra</p>

        <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left">
            <p class="text-sm text-gray-500">Número de Pedido</p>
            <p class="text-2xl font-bold text-[#0F52BA]">{{ $documento ?? request('documento') }}</p>
        </div>

        <a href="{{ route('pedidos.verp', ['documento' => $documento ?? request('documento')]) }}"
            class="inline-block bg-[#0F52BA] text-white px-8 py-3 rounded-xl hover:bg-[#003087]">
            Ver Mis Pedidos
        </a>

    </div>
</div>
@endsection
