<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Usa el nombre de la empresa --}}
    <title>@yield('title', $empresaNombre ?? config('app.name'))</title>

    {{-- Muestra el logo_tienda --}}
    @if(companyLogoUrl())
        <link rel="icon" type="image/webp" href="{{ companyLogoUrl() }}">
    @else
        {{-- Ícono por defecto --}}
        <link rel="icon" type="image/svg+xml" href="{{ asset('static/image/company/default_logo.svg') }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    @stack('scripts')
</head>

<body class="bg-[#f5f7fb] min-h-screen flex flex-col">

    @include('partials.navbar')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- BOTÓN FLOTANTE DE WHATSAPP CON MENÚ --}}
    @if(!empty($footerData['whatsappNumbers']))
    <div class="fixed bottom-5 right-5 z-50">
        {{-- Botón principal --}}
        <button id="whatsappButton"
            class="w-16 h-16 rounded-full bg-[#25D366] flex items-center justify-center shadow-lg hover:scale-110 transition-transform duration-300 cursor-pointer">
            {{-- Ícono principal de WhatsApp --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="w-9 h-9 fill-white">
                <path d="M16.02 3C8.83 3 3 8.82 3 16c0 2.53.74 4.99 2.13 7.09L3 29l6.09-2.09A12.94 12.94 0 0016.02 29C23.2 29 29 23.18 29 16S23.2 3 16.02 3zm0 23.8c-2.33 0-4.6-.68-6.54-1.97l-.47-.3-3.61 1.24 1.21-3.52-.31-.49A10.75 10.75 0 015.3 16c0-5.93 4.79-10.75 10.72-10.75 2.87 0 5.57 1.12 7.6 3.15a10.7 10.7 0 013.15 7.6c0 5.93-4.79 10.75-10.75 10.75zm5.09-7.57c-.28-.14-1.58-.78-1.82-.87-.24-.09-.41-.14-.58.14-.17.27-.67.87-.82 1.05-.15.18-.3.2-.56.07-.27-.14-1.12-.41-2.13-1.3-.79-.7-1.32-1.56-1.47-1.82-.15-.27-.02-.41.11-.55.12-.12.27-.3.41-.45.14-.15.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.58-1.39-.8-1.91-.21-.5-.43-.43-.58-.44l-.5-.01c-.18 0-.48.07-.73.34-.24.27-.95.93-.95 2.27 0 1.34.97 2.64 1.11 2.82.14.18 1.91 2.92 4.64 4.09.65.28 1.16.44 1.55.56.65.21 1.25.18 1.72.11.52-.08 1.58-.65 1.8-1.28.22-.63.22-1.17.15-1.28-.06-.11-.24-.18-.5-.32z"/>
            </svg>
        </button>

        {{-- Menú con los números --}}
        <div id="whatsappMenu"
            class="hidden absolute bottom-20 right-0 bg-white rounded-lg shadow-lg w-64 overflow-hidden">
            <div class="border-b p-3 text-center font-semibold text-gray-700">
                Elige el chat del servicio que te interesa
            </div>
            <ul class="divide-y divide-gray-200">
                @foreach($footerData['whatsappNumbers'] as $nombre => $numero)
                    <li>
                        <a href="https://wa.me/{{ $numero }}" target="_blank"
                        class="flex items-center gap-3 p-3 hover:bg-gray-100 transition-colors"
                        onclick="document.getElementById('whatsappMenu').classList.add('hidden')">
                            <div class="w-8 h-8 rounded-full bg-[#25D366] flex items-center justify-center">
                                {{-- Ícono de WhatsApp --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="w-5 h-5 fill-white">
                                    <path d="M16.02 3C8.83 3 3 8.82 3 16c0 2.53.74 4.99 2.13 7.09L3 29l6.09-2.09A12.94 12.94 0 0016.02 29C23.2 29 29 23.18 29 16S23.2 3 16.02 3zm0 23.8c-2.33 0-4.6-.68-6.54-1.97l-.47-.3-3.61 1.24 1.21-3.52-.31-.49A10.75 10.75 0 015.3 16c0-5.93 4.79-10.75 10.72-10.75 2.87 0 5.57 1.12 7.6 3.15a10.7 10.7 0 013.15 7.6c0 5.93-4.79 10.75-10.75 10.75zm5.09-7.57c-.28-.14-1.58-.78-1.82-.87-.24-.09-.41-.14-.58.14-.17.27-.67.87-.82 1.05-.15.18-.3.2-.56.07-.27-.14-1.12-.41-2.13-1.3-.79-.7-1.32-1.56-1.47-1.82-.15-.27-.02-.41.11-.55.12-.12.27-.3.41-.45.14-.15.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.58-1.39-.8-1.91-.21-.5-.43-.43-.58-.44l-.5-.01c-.18 0-.48.07-.73.34-.24.27-.95.93-.95 2.27 0 1.34.97 2.64 1.11 2.82.14.18 1.91 2.92 4.64 4.09.65.28 1.16.44 1.55.56.65.21 1.25.18 1.72.11.52-.08 1.58-.65 1.8-1.28.22-.63.22-1.17.15-1.28-.06-.11-.24-.18-.5-.32z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-gray-800">{{ $nombre }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Script para mostrar/ocultar --}}
        <script>
            document.getElementById('whatsappButton').addEventListener('click', function() {
                document.getElementById('whatsappMenu').classList.toggle('hidden');
            });
        </script>
    </div>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/carrito.js') }}?v={{ filemtime(public_path('js/carrito.js')) }}"></script>
    <script src="{{ asset('js/wishlist.js') }}?v={{ filemtime(public_path('js/wishlist.js')) }}"></script>
    <script src="{{ asset('js/cart.js') }}?v={{ filemtime(public_path('js/cart.js')) }}"></script>
    <script src="{{ asset('js/comprobante.js') }}?v={{ filemtime(public_path('js/comprobante.js')) }}"></script>
    <script src="{{ asset('js/checkout-pago.js') }}?v={{ filemtime(public_path('js/checkout-pago.js')) }}"></script>
</body>
</html>
