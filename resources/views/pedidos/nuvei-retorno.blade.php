@extends('layouts.app')

@section('title', 'Estado del pago')

@section('content')
<div class="max-w-2xl mx-auto px-6 py-12">
    <div class="bg-white rounded-2xl shadow-lg p-10 text-center">

        @if($estado === 'success')
            {{-- Pago recibido (pendiente de confirmación por webhook) --}}
            <div class="w-16 h-16 mx-auto rounded-full bg-green-50 flex items-center justify-center mb-6">
                <svg class="w-9 h-9 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">¡Gracias por tu pago!</h1>
            <p class="text-gray-500 mb-6">
                Recibimos tu pago y lo estamos confirmando. En breve verás tu pedido
                actualizado en tu historial.
            </p>

        @elseif($estado === 'failure')
            {{-- Pago rechazado --}}
            <div class="w-16 h-16 mx-auto rounded-full bg-red-50 flex items-center justify-center mb-6">
                <svg class="w-9 h-9 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">El pago no se completó</h1>
            <p class="text-gray-500 mb-6">
                Tu pago no pudo procesarse. Puedes intentarlo nuevamente o elegir otra
                forma de pago. No se realizó ningún cargo.
            </p>

        @elseif($estado === 'review')
            {{-- En revisión (antifraude) --}}
            <div class="w-16 h-16 mx-auto rounded-full bg-amber-50 flex items-center justify-center mb-6">
                <svg class="w-9 h-9 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Tu pago está en revisión</h1>
            <p class="text-gray-500 mb-6">
                Tu pago está siendo revisado. Te notificaremos el resultado y podrás
                verlo reflejado en tu historial de pedidos.
            </p>

        @else
            {{-- pending (por defecto) --}}
            <div class="w-16 h-16 mx-auto rounded-full bg-blue-50 flex items-center justify-center mb-6">
                <svg class="w-9 h-9 text-[#0300a3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Estamos procesando tu pago</h1>
            <p class="text-gray-500 mb-6">
                Tu pago está en proceso. En cuanto se confirme, verás tu pedido
                actualizado en tu historial.
            </p>
        @endif

        @if($orden)
            <div class="bg-gray-50 rounded-xl p-4 mb-6 inline-block">
                <p class="text-xs uppercase text-gray-400 font-semibold tracking-wide">Número de pedido</p>
                <p class="text-lg font-bold text-[#0300a3]">#{{ $orden }}</p>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row gap-3 justify-center mt-2">
            <a href="{{ route('profile.orders') }}"
               class="bg-[#0300a3] hover:bg-[#0200cc] text-white text-sm font-semibold px-6 py-3 rounded-xl transition">
                Ver mis pedidos
            </a>
            <a href="{{ route('catalogo.index') }}"
               class="border border-[#0300a3] text-[#0300a3] hover:bg-blue-50 text-sm font-semibold px-6 py-3 rounded-xl transition">
                Seguir comprando
            </a>
        </div>

    </div>
</div>
@endsection
