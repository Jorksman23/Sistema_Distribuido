<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Usa el nombre de la empresa--}}
    <title>@yield('title', $empresaNombre ?? config('app.name'))</title>

    {{-- Muestra el logo_tienda--}}
    @if(companyLogoUrl())
        <link rel="icon" type="image/webp" href="{{ companyLogoUrl() }}">
    @else
        {{-- Ícono por defecto--}}
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

    @if(!empty($footerData['whatsapp']))
    <a href="{{ $footerData['whatsapp'] }}"
    target="_blank"
    class="fixed bottom-5 right-5 z-50 group">

        <div class="w-16 h-16 rounded-full bg-[#25D366]
                    flex items-center justify-center
                    shadow-lg hover:scale-110
                    transition-all duration-300">

            <svg xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 32 32"
                class="w-9 h-9 fill-white">

                <path d="M19.11 17.23c-.27-.14-1.58-.78-1.82-.87-.24-.09-.41-.14-.58.14-.17.27-.67.87-.82 1.05-.15.18-.3.2-.56.07-.27-.14-1.12-.41-2.13-1.3-.79-.7-1.32-1.56-1.47-1.82-.15-.27-.02-.41.11-.55.12-.12.27-.3.41-.45.14-.15.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.58-1.39-.8-1.91-.21-.5-.43-.43-.58-.44l-.5-.01c-.18 0-.48.07-.73.34-.24.27-.95.93-.95 2.27 0 1.34.97 2.64 1.11 2.82.14.18 1.91 2.92 4.64 4.09.65.28 1.16.44 1.55.56.65.21 1.25.18 1.72.11.52-.08 1.58-.65 1.8-1.28.22-.63.22-1.17.15-1.28-.06-.11-.24-.18-.5-.32zM16.02 3C8.83 3 3 8.82 3 16c0 2.53.74 4.99 2.13 7.09L3 29l6.09-2.09A12.94 12.94 0 0016.02 29C23.2 29 29 23.18 29 16S23.2 3 16.02 3zm0 23.64c-2.11 0-4.18-.57-5.98-1.65l-.43-.26-3.61 1.24 1.18-3.52-.28-.45a10.6 10.6 0 01-1.63-5.64c0-5.88 4.78-10.66 10.67-10.66 2.85 0 5.52 1.11 7.53 3.12a10.57 10.57 0 013.12 7.53c0 5.88-4.78 10.66-10.67 10.66z"/>
            </svg>

        </div>

    </a>
@endif
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/carrito.js') }}?v={{ filemtime(public_path('js/carrito.js')) }}"></script>
<script src="{{ asset('js/wishlist.js') }}?v={{ filemtime(public_path('js/wishlist.js')) }}"></script>
</body>
</html>
