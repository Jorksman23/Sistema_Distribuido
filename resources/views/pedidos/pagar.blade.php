@extends('layouts.app')

@section('title', 'Resumen y Pago')

@section('content')
<style>
.btn-buscar,
.btn-borrar {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    border-radius: 0.75rem;
    padding: 0.65rem 1.2rem;
    line-height: 1.25rem;
    transition: background-color 0.2s ease;
}
.btn-buscar {
    background-color: #0F52BA;
    color: white;
}
.btn-buscar:hover {
    background-color: #003087;
}
.btn-borrar {
    background-color: #dc2626;
    color: white;
}
.btn-borrar:hover {
    background-color: #b91c1c;
}

</style>
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 lg:py-12 bg-gray-50 min-h-screen">

    <!-- STEPPER -->
    <div class="flex items-center justify-center mt-4 mb-8">

        <div class="flex flex-col items-center">
            <div class="w-10 h-10 rounded-full bg-[#0300a3] flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <span class="text-xs font-semibold text-[#0300a3] mt-2">Carrito</span>
        </div>

        <div class="h-0.5 w-24 bg-[#0300a3] mx-2 mb-5"></div>

        <div class="flex flex-col items-center">
            <div class="w-10 h-10 rounded-full bg-[#0300a3] flex items-center justify-center">
                <span class="text-white font-bold text-sm">2</span>
            </div>
            <span class="text-xs font-semibold text-[#0300a3] mt-2">Facturación</span>
        </div>

        <div class="h-0.5 w-24 bg-gray-200 mx-2 mb-5"></div>

        <div class="flex flex-col items-center">
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                <span class="text-gray-400 font-bold text-sm">3</span>
            </div>
            <span class="text-xs font-semibold text-gray-400 mt-2">Pago</span>
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-6 mt-4 items-start">
        <form id="payment-form" method="POST" action="{{ route('carrito.procesar.pago') }}" class="contents">
            @csrf

            {{-- BLOQUE 1: Resumen — primero en mobile y desktop --}}
            <div class="lg:col-span-7 order-1">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 lg:p-6">

                    <h2 class="font-bold text-gray-900 text-2xl mb-6 flex items-center gap-2">
                        <span class="text-[#0F52BA]">
                            <svg class="w-7 h-7 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </span> Resumen del Carrito
                    </h2>

                    @foreach($items as $item)
                    <div class="flex gap-5 py-5 border-b last:border-none">
                        <img src="{{ $item->imagen_url ?: 'https://placehold.co/96x96?text=Sin+imagen' }}"
                            alt="{{ $item->nombre }}"
                            class="w-20 h-20 object-cover rounded-xl border"
                            onerror="this.onerror=null;this.src='https://placehold.co/96x96?text=Sin+imagen'">
                        <div class="flex-1">
                            <p class="font-medium text-gray-800">{{ $item->nombre }}</p>
                            @if($item->nombre_presentacion)
                                <p class="text-sm text-gray-500">{{ $item->nombre_presentacion }}</p>
                            @endif
                            <p class="text-sm text-gray-600">Cantidad: {{ $item->cantidad }}</p>
                        </div>
                        <div class="text-right font-semibold text-lg">
                            ${{ number_format($item->pvp3 * $item->cantidad, 2) }}
                        </div>
                    </div>
                    @endforeach

                    <div class="mt-8 pt-6 border-t text-right">
                        @if($trabajaConIvaIncluido === 'S')
                            <p class="text-lg font-semibold text-[#003087]">
                                Subtotal: <span class="font-medium">${{ $subtotal }}</span>
                            </p>
                            <p class="text-lg font-semibold text-[#003087]">
                                IVA {{ $porcentajeIva }}%: <span class="font-medium">${{ $iva }}</span>
                            </p>
                            <p class="text-2xl font-bold text-[#003087]">
                                Total a pagar: ${{ $total }}
                            </p>
                        @else
                            <p class="text-lg font-semibold text-[#003087]">
                                Subtotal: <span class="font-medium">${{ $subtotal }}</span>
                            </p>
                            <p class="text-lg font-semibold text-[#003087]">
                                IVA incluido {{ $porcentajeIva }}%: <span class="font-medium">${{ $iva }}</span>
                            </p>
                            <p class="text-2xl font-bold text-[#003087]">
                                Total bruto: ${{ $totalBruto }}
                            </p>
                        @endif
                    </div>
                    {{-- Nota de envío --}}
                    <div class="mt-4 flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                        <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z"/>
                        </svg>
                        <p class="text-xs text-amber-700 leading-relaxed">
                            <span class="font-semibold">Nota:</span>
                            El costo de envío no está incluido en este total. Si seleccionas envío vehicular,
                            se aplicará un recargo adicional que será coordinado con nuestro equipo.
                        </p>
                    </div>
                </div>
            </div>

            {{-- BLOQUE 2: Datos de Facturación — segundo en mobile, segunda fila izquierda en desktop --}}
            <div class="lg:col-span-7 order-2">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 lg:p-6 space-y-6">

                    <div class="flex justify-between items-center">
                        <h2 class="font-bold text-gray-900 text-2xl">Datos de Facturación</h2>
                        <button type="button" id="usar-registro"
                                class="text-sm text-[#0F52BA] font-semibold hover:underline">
                            Usar datos del registro
                        </button>
                    </div>

                    <div class="flex items-end gap-3 mt-1">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">RUC o Cédula</label>
                            <input type="text" name="cedula" id="cedula" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                        </div>
                        <div class="flex gap-2 mb-[2px]">
                            <button type="button" id="buscar-cedula" class="btn-buscar">Buscar</button>
                            <button type="button" id="borrar-datos" class="btn-borrar">Borrar</button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                        <input type="text" name="nombre" id="nombre" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección exacta</label>
                        <input type="text" name="direccion" id="direccion"
                            class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                            <input type="email" name="email" id="email" required
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observación (opcional)</label>
                        <textarea name="observacion" id="observacion" rows="2"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-[#0F52BA]"></textarea>
                    </div>

                </div>
            </div>

            {{-- BLOQUE 3: Pasarela — último en mobile, columna derecha spanning 2 filas en desktop --}}
            <div class="lg:col-span-5 lg:row-span-2 lg:row-start-1 lg:col-start-8 order-3 self-start">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-5 lg:p-6">

                    <div class="mb-6">
                        <h2 class="font-bold text-gray-900 text-2xl mb-6 flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#0300a3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M5.223 2.25c-.497 0-.974.198-1.325.55l-1.3 1.298A3.75 3.75 0 0 0 7.5 9.75c.627.47 1.406.75 2.25.75.844 0 1.624-.28 2.25-.75.626.47 1.406.75 2.25.75.844 0 1.623-.28 2.25-.75a3.75 3.75 0 0 0 4.902-5.652l-1.3-1.299a1.875 1.875 0 0 0-1.325-.549H5.223Z" />
                                <path fill-rule="evenodd" d="M3 20.25v-8.755c1.42.674 3.08.673 4.5 0A5.234 5.234 0 0 0 9.75 12c.804 0 1.568-.182 2.25-.506a5.234 5.234 0 0 0 2.25.506c.804 0 1.567-.182 2.25-.506 1.42.674 3.08.675 4.5.001v8.755h.75a.75.75 0 0 1 0 1.5H2.25a.75.75 0 0 1 0-1.5H3Zm3-6a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-.75.75h-3a.75.75 0 0 1-.75-.75v-3Zm8.25-.75a.75.75 0 0 0-.75.75v5.25c0 .414.336.75.75.75h3a.75.75 0 0 0 .75-.75v-5.25a.75.75 0 0 0-.75-.75h-3Z" clip-rule="evenodd" />
                            </svg>
                            Método de entrega
                        </h2>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Selecciona método de entrega
                        </label>
                        <select id="metodo_entrega" name="metodo_entrega" required
                            class="w-full border-2 border-gray-800 rounded-xl px-4 py-3">
                            <option value="">Seleccione método de entrega</option>
                            <option value="R">Retiro en local</option>
                            <option value="E">Envío vehicular</option>
                        </select>
                    </div>

                    <h2 class="font-bold text-gray-900 text-2xl mb-6 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0300a3]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                            <path fill-rule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clip-rule="evenodd" />
                        </svg>

                        Forma de Pago
                    </h2>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Selecciona método de pago
                        </label>
                        <select id="tipo_pago" name="tipo_pago" required
                            class="w-full border-2 border-gray-800 rounded-xl px-4 py-3">
                            <option value="">Seleccione método de pago</option>
                            @foreach($formasPago as $forma)
                                <option value="{{ $forma->secuencia }}"
                                        data-tarjeta="{{ (int)$forma->secuencia === (int)config('payments.nuvei') ? '1' : '0' }}">
                                    {{ $forma->forma_pago }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit"
                            class="w-full mt-8 bg-[#0F52BA] hover:bg-[#003087] text-white font-semibold text-lg py-4 rounded-2xl">
                        Finalizar Pedido - ${{ $trabajaConIvaIncluido === 'S' ? $total : $totalBruto }}
                    </button>

                    <p class="text-center text-xs text-gray-500 mt-4">
                        🔒 Pago seguro y encriptado
                    </p>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
document.getElementById('usar-registro').addEventListener('click', () => {
    const usuario = {
        cedula: '{{ $usuario->cedula_ruc ?? "" }}',
        nombre: '{{ $usuario->nombre ?? "" }}',
        direccion: '{{ $usuario->direccion ?? "" }}',
        telefono: '{{ $usuario->telefono ?? "" }}',
        email: '{{ $usuario->email ?? "" }}'
    };


    for (const campo in usuario) {
        const input = document.querySelector(`input[name="${campo}"]`);
        if (input) input.value = usuario[campo];
    }

    document.querySelector('input[name="nombre"]').readOnly = true;
});

</script>


<script>
document.getElementById('buscar-cedula').addEventListener('click', async () => {
    const cedula = document.getElementById('cedula').value.trim();

    // 🔹 Validación inicial
    if (!cedula || cedula.length < 10) {
        return Swal.fire('Error', 'Ingrese una cédula válida.', 'error');
    }

    try {
        const response = await fetch(`/cliente/datos?cedula=${cedula}`);
        const data = await response.json();

        if (data && !data.error) {
            // 🔹 Llenar todos los campos si existen
            document.getElementById('nombre').value = data.nombre || '';
            document.getElementById('direccion').value = data.direccion || '';
            document.getElementById('telefono').value = data.telefono || '';
            document.getElementById('email').value = data.email || '';

            // 🔹 Bloquear y poner gris el campo nombre
            const nombreInput = document.getElementById('nombre');
            if (nombreInput.value) {
                nombreInput.readOnly = true;
                nombreInput.classList.add('bg-gray-100', 'text-gray-500');
            }
        } else {
            Swal.fire('Aviso', 'No se encontraron datos para esta cédula.', 'warning');
        }
    } catch (error) {
        console.error('Error al obtener datos del cliente:', error);
        Swal.fire('Error', 'Hubo un problema al consultar los datos.', 'error');
    }
});
</script>


<script>
document.getElementById('borrar-datos').addEventListener('click', () => {
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Quieres borrar todos los datos de facturación?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, borrar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const campos = ['cedula', 'nombre', 'direccion', 'telefono', 'email', 'observacion'];
            campos.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.value = '';
                    input.readOnly = false;
                    input.classList.remove('bg-gray-100', 'text-gray-500');
                }
            });

            Swal.fire({
                title: 'Datos borrados',
                text: 'Los datos de facturación se han eliminado correctamente.',
                icon: 'success',
                confirmButtonColor: '#0F52BA'
            });
        }
    });
});

</script>

@endsection

