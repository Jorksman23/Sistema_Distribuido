@extends('layouts.app')

@section('title', 'Resumen y Pago')

@section('content')
<div class="max-w-6xl mx-auto px-6 py-12 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto">
        {{-- STEPPER --}}
        <div class="flex items-center justify-center mb-10">

            {{-- Paso 1: Carrito --}}
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-[#0300a3] flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-[#0300a3] mt-2">Carrito</span>
            </div>

            {{-- Línea --}}
            <div class="h-0.5 w-24 bg-[#0300a3] mx-2 mb-5"></div>

            {{-- Paso 2: Facturación --}}
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-[#0300a3] flex items-center justify-center">
                    <span class="text-white font-bold text-sm">2</span>
                </div>
                <span class="text-xs font-semibold text-[#0300a3] mt-2">Facturación</span>
            </div>

            {{-- Línea --}}
            <div class="h-0.5 w-24 bg-gray-200 mx-2 mb-5"></div>

            {{-- Paso 3: Pago --}}
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                    <span class="text-gray-400 font-bold text-sm">3</span>
                </div>
                <span class="text-xs font-semibold text-gray-400 mt-2">Pago</span>
            </div>

        </div>
        <div class="grid lg:grid-cols-12 gap-8">
            <form id="payment-form" method="POST" action="{{ route('carrito.procesar.pago') }}" class="contents">
                @csrf

                <!-- Resumen + Datos de Facturación -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- Resumen del Carrito -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                        <h2 class="font-bold text-gray-900 text-2xl mb-6 flex items-center gap-2">
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
                            <p class="text-lg font-semibold text-[#003087]">
                                Subtotal:
                                <span class="font-medium">${{ $subtotal }}</span>
                            </p>
                            <p class="text-lg font-semibold text-[#003087]">
                                IVA:
                                <span class="font-medium">${{ $iva }}</span>
                            </p>
                            <p class="text-2xl font-bold text-[#003087]">
                                Total a pagar: ${{ $total }}
                            </p>
                        </div>
                    </div>

                    <!-- Datos de Facturación -->
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                        <h2 class="font-bold text-gray-900 text-2xl mb-6 text-center">Datos de Facturación</h2>

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
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
                        <h2 class="font-bold text-gray-900 text-2xl mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#0300a3]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            Forma de Pago
                        </h2>

                        <!-- Combo Box -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Selecciona método de pago
                            </label>
                            <select
                                id="tipo_pago"
                                name="tipo_pago"
                                required
                                class="w-full border-2 border-gray-800 rounded-xl px-4 py-3 bg-white text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3] focus:border-[#0300a3]">
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

            </form>
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
<script>
document.querySelector('input[name="cedula"]').addEventListener('blur', async (e) => {
    const cedula = e.target.value.trim();
    if (!cedula) return;

    try {
        const response = await fetch(`/cliente/datos?cedula=${cedula}`);
        const data = await response.json();

        if (data && !data.error) {
            document.querySelector('input[name="nombre"]').value = data.nombre || '';
            document.querySelector('input[name="direccion"]').value = data.direccion || '';
            document.querySelector('input[name="telefono"]').value = data.telefono || '';
            document.querySelector('input[name="email"]').value = data.email || '';
        } else {
            alert(data.error || 'No se encontraron datos para esta cédula.');
        }
    } catch (error) {
        console.error('Error al obtener datos del cliente:', error);
        alert('Hubo un problema al consultar los datos.');
    }
});

</script>
@endsection
