@extends('layouts.app')

@section('title', 'Pago del pedido')

@section('content')
<div class="max-w-5xl mx-auto px-6 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Resumen y Pago</h1>

    {{-- Resumen del carrito --}}
    <div class="grid md:grid-cols-2 gap-8">
        <div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Resumen del Carrito</h2>

                @foreach($items as $item)
                    <div class="flex items-center justify-between border-b py-3">
                        <div class="flex items-center gap-4">
                            <img src="{{ $item->imagen_url }}" alt="{{ $item->nombre }}" class="w-16 h-16 object-cover rounded">
                            <div>
                                <p class="font-medium text-gray-800">{{ $item->nombre }}</p>
                                <p class="text-sm text-gray-500">Cantidad: {{ $item->cantidad }}</p>
                            </div>
                        </div>
                        <p class="font-semibold text-gray-700">${{ number_format($item->pvp3 * $item->cantidad, 2) }}</p>
                    </div>
                @endforeach

                <div class="mt-4 text-right">
                    <p class="text-gray-600">Subtotal: ${{ $total }}</p>
                    <p class="font-bold text-gray-800 text-lg">Total a pagar: ${{ $total }}</p>
                </div>
            </div>

            <div class="mt-6 bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">Datos de Facturación</h2>
                <form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" placeholder="RUC o Cédula" class="border rounded px-3 py-2 w-full">
                        <input type="text" placeholder="Nombre completo para factura" class="border rounded px-3 py-2 w-full">
                        <input type="text" placeholder="Dirección exacta" class="border rounded px-3 py-2 w-full md:col-span-2">
                        <input type="text" placeholder="Ciudad" class="border rounded px-3 py-2 w-full">
                        <input type="text" placeholder="Código Postal" class="border rounded px-3 py-2 w-full">
                    </div>
                </form>
            </div>
        </div>

        {{-- Pasarela de pago --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">Pasarela de Pago</h2>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <button class="border rounded py-2 hover:bg-blue-50">Tarjeta</button>
                <button class="border rounded py-2 hover:bg-blue-50">PayPhone</button>
                <button class="border rounded py-2 hover:bg-blue-50">Deuna!</button>
                <button class="border rounded py-2 hover:bg-blue-50">PayPal</button>
            </div>

            <form>
                <input type="text" placeholder="Número de tarjeta" class="border rounded px-3 py-2 w-full mb-3">
                <div class="grid grid-cols-3 gap-3 mb-3">
                    <input type="text" placeholder="MM/YY" class="border rounded px-3 py-2 w-full">
                    <input type="text" placeholder="CVC" class="border rounded px-3 py-2 w-full">
                    <select class="border rounded px-3 py-2 w-full">
                        <option>Pago Corriente</option>
                        <option>3 meses</option>
                        <option>6 meses</option>
                    </select>
                </div>

                <button type="submit" class="bg-blue-600 text-white w-full py-3 rounded hover:bg-blue-700 font-semibold">
                    Finalizar Pedido - ${{ $total }}
                </button>
                <p class="text-xs text-gray-500 mt-2 text-center">Pago encriptado y seguro 🔒</p>
            </form>
        </div>
    </div>
</div>
@endsection
