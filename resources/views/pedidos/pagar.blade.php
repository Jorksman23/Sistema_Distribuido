@extends('layouts.app')

@section('title', 'Resumen y Pago')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-12 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold text-[#003087] mb-8 text-center md:text-left">
            Resumen y Pago
        </h1>

        <div class="grid lg:grid-cols-12 gap-8">

            <!-- Resumen + Datos de Facturación -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Resumen del Carrito -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <span class="text-[#0F52BA]">🛒</span> Resumen del Carrito
                    </h2>

                    @foreach($items as $item)
                    <div class="flex gap-5 py-5 border-b last:border-none">
                        <img src="{{ $item->imagen_url }}" alt="{{ $item->nombre }}"
                             class="w-20 h-20 object-cover rounded-xl border">
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $item->nombre }}</p>
                            @if($item->nombre_presentacion)
                                <p class="text-sm text-gray-500">{{ $item->nombre_presentacion }}</p>
                            @endif
                            <p class="text-sm text-gray-600">Cantidad: <span class="font-semibold">{{ $item->cantidad }}</span></p>
                        </div>
                        <div class="text-right font-semibold text-lg">
                            ${{ number_format($item->pvp3 * $item->cantidad, 2) }}
                        </div>
                    </div>
                    @endforeach

                    <div class="mt-8 pt-6 border-t text-right">
                        <p class="text-lg text-gray-600">Subtotal: <span class="font-medium">${{ $total }}</span></p>
                        <p class="text-2xl font-bold text-[#003087]">Total a pagar: ${{ $total }}</p>
                    </div>
                </div>

                <!-- Datos de Facturación -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Datos de Facturación</h2>

                    <form id="payment-form" method="POST" action="{{ route('carrito.procesar.pago') }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">RUC o Cédula</label>
                                <input type="text" name="cedula" required class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                                <input type="text" name="nombre" required class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección exacta</label>
                                <input type="text" name="direccion" required class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input type="text" name="telefono" required class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                                <input type="email" name="email" required class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observación (opcional)</label>
                            <textarea name="observacion" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]"></textarea>
                        </div>
                </div>
            </div>

            <!-- === PASARELA DE PAGO === -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sticky top-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Forma de Pago</h2>

                    <!-- Combo Box -->
                    <div class="mb-6" >
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Selecciona método de pago
                        </label>
                        <select
                            id="tipo_pago"
                            name="tipo_pago"
                            required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            <option value="">Seleccione método de pago</option>

                            @foreach($formasPago as $forma)
                                <option value="{{ $forma->secuencia }}">
                                    {{ $forma->forma_pago }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Campos de Tarjeta (se muestran solo para PayPhone) -->
                    <div id="card-fields">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre en la tarjeta</label>
                                <input type="text" name="nombre_tarjeta" class="w-full border border-gray-300 rounded-xl px-4 py-3">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Número de tarjeta</label>
                                <input type="text" name="numero_tarjeta" maxlength="19"
                                       class="w-full border border-gray-300 rounded-xl px-4 py-3" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de expiración</label>
                                    <input type="text" name="expiracion" maxlength="5" placeholder="MM/YY"
                                           class="w-full border border-gray-300 rounded-xl px-4 py-3">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CVC</label>
                                    <input type="text" name="cvc" maxlength="4"
                                           class="w-full border border-gray-300 rounded-xl px-4 py-3">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full mt-8 bg-[#0F52BA] hover:bg-[#003087] text-white font-semibold text-lg py-4 rounded-2xl transition-all">
                        Finalizar Pedido - ${{ $total }}
                    </button>

                    <p class="text-center text-xs text-gray-500 mt-4 flex items-center justify-center gap-1">
                        <span class="text-green-600">🔒</span> Pago seguro y encriptado
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('tipo_pago').addEventListener('change', function() {
    const cardFields = document.getElementById('card-fields');
    if (this.value === 'payphone') {
        cardFields.style.display = 'block';
    } else {
        cardFields.style.display = 'none';
    }
});

// Inicializar
document.getElementById('tipo_pago').dispatchEvent(new Event('change'));
</script>
@endsection
