@extends('layouts.app')

@section('title', 'Resumen y Pago')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-12 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-3xl font-bold text-[#003087] mb-8 text-center md:text-left">
            Resumen y Pago
        </h1>

        <div class="grid lg:grid-cols-12 gap-8">

            <!-- Resumen del Carrito + Datos de Facturación -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Resumen del Carrito -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <span class="text-[#0F52BA]">🛒</span> Resumen del Carrito
                    </h2>

                    @foreach($items as $item)
                    <div class="flex gap-5 py-5 border-b last:border-none">
                        <img src="{{ $item->imagen_url }}"
                             alt="{{ $item->nombre }}"
                             class="w-20 h-20 object-cover rounded-xl border">
                        <div class="flex-1">
                            <p class="font-medium text-gray-800 leading-tight">{{ $item->nombre }}</p>
                            @if($item->nombre_presentacion)
                                <p class="text-sm text-gray-500">{{ $item->nombre_presentacion }}</p>
                            @endif
                            <p class="text-sm text-gray-600 mt-1">Cantidad: <span class="font-semibold">{{ $item->cantidad }}</span></p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-lg text-gray-800">
                                ${{ number_format($item->pvp3 * $item->cantidad, 2) }}
                            </p>
                        </div>
                    </div>
                    @endforeach

                    <div class="mt-8 pt-6 border-t">
                        <div class="flex justify-between text-lg">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">${{ $total }}</span>
                        </div>
                        <div class="flex justify-between text-2xl font-bold mt-3 text-[#003087]">
                            <span>Total a pagar</span>
                            <span>${{ $total }}</span>
                        </div>
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
                                <input type="text" name="cedula" required
                                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA] focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                                <input type="text" name="nombre" required
                                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección exacta</label>
                                <input type="text" name="direccion" required
                                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input type="text" name="telefono" required
                                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                                <input type="email" name="email" required
                                       class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observación (opcional)</label>
                            <textarea name="observacion" rows="2"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]"></textarea>
                        </div>
                </div>
            </div>

            <!-- Pasarela de Pago -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 sticky top-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6">Método de Pago</h2>

                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <button type="button" onclick="selectPayment(this)" data-value="payphone"
                            class="payment-btn border-2 border-gray-200 hover:border-[#0F52BA] rounded-2xl py-4 text-center transition-all">
                            PayPhone
                        </button>
                        <button type="button" onclick="selectPayment(this)" data-value="transferencia"
                            class="payment-btn border-2 border-gray-200 hover:border-[#0F52BA] rounded-2xl py-4 text-center transition-all">
                            Transferencia
                        </button>
                        <button type="button" onclick="selectPayment(this)" data-value="contraentrega"
                            class="payment-btn border-2 border-gray-200 hover:border-[#0F52BA] rounded-2xl py-4 text-center transition-all">
                            Contra Entrega
                        </button>
                    </div>

                    <input type="hidden" name="tipo_pago" id="tipo_pago" value="payphone">

                    <button type="submit"
                            class="w-full bg-[#0F52BA] hover:bg-[#003087] text-white font-semibold text-lg py-4 rounded-2xl transition-all duration-200 shadow-lg shadow-blue-500/30">
                        Finalizar Pedido - ${{ $total }}
                    </button>

                    <p class="text-center text-xs text-gray-500 mt-4 flex items-center justify-center gap-1">
                        <span class="text-green-600">🔒</span> Pago encriptado y seguro
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectPayment(btn) {
    document.querySelectorAll('.payment-btn').forEach(b => {
        b.classList.remove('border-[#0F52BA]', 'bg-blue-50');
    });
    btn.classList.add('border-[#0F52BA]', 'bg-blue-50');
    document.getElementById('tipo_pago').value = btn.dataset.value;
}
</script>
@endsection
