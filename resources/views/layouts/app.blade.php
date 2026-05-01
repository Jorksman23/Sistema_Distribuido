<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<nav class="bg-white shadow px-6 py-3 flex items-center gap-4">

    {{-- LOGO --}}
    <a href="/" class="text-xl font-bold text-blue-600 shrink-0">
        {{ config($empresaNombre) }}
    </a>

    {{-- LINKS --}}
    <div class="hidden md:flex gap-6 text-sm ml-4">
        <a href="/" class="text-blue-600 font-semibold">Home</a>
        <a href="#" class="text-gray-600 hover:text-blue-600">Categorías</a>
        <a href="#" class="text-gray-600 hover:text-blue-600">Tienda</a>
        <a href="#" class="text-gray-600 hover:text-blue-600">Sobre Nosotros</a>
    </div>

    {{-- BUSCADOR --}}
    <div class="flex-1 mx-4 max-w-xl">
        <div class="relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="search" type="text" placeholder="Search products..."
                   class="w-full pl-9 pr-4 py-2 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    {{-- ICONOS --}}
    <div class="flex items-center gap-4 ml-auto">

        {{-- WISHLIST --}}
        <button onclick="requireAuth(event, '/wishlist')" class="hover:scale-110 transition">
            <svg class="w-6 h-6 text-gray-700 hover:text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>

        {{-- CARRITO --}}
        <button onclick="requireAuth(event, '/carrito')" class="hover:scale-110 transition">
            <svg class="w-6 h-6 text-gray-700 hover:text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </button>

        {{-- USUARIO DROPDOWN --}}
        <div class="relative">
            <button onclick="toggleMenu()" class="hover:scale-110 transition">
                <svg class="w-6 h-6 text-gray-700 hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </button>

            <div id="userMenu" class="hidden absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-lg z-50 text-sm">
                @if(session('user_id'))
                    <div class="px-4 py-3 border-b text-gray-700 font-semibold truncate">
                        {{ session('nombre') }}
                    </div>
                    <a href="#" class="block px-4 py-2 hover:bg-gray-50">Mi perfil</a>
                    <a href="#" class="block px-4 py-2 hover:bg-gray-50">Mis pedidos</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-50">
                            Cerrar sesión
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block px-4 py-2 hover:bg-gray-50">Iniciar sesión</a>
                    <a href="{{ route('register') }}" class="block px-4 py-2 hover:bg-gray-50">Registrarse</a>
                @endif
            </div>
        </div>

    </div>
</nav>

<main class="flex-1">
    @yield('content')
</main>

<footer class="bg-white mt-16 py-6 text-center text-sm text-gray-400">
    © {{ date('Y') }} {{ config($empresaNombre) }}
</footer>

{{-- MODAL LOGIN REQUERIDO --}}
<div id="authModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl p-8 w-full max-w-sm text-center shadow-xl">
        <h2 class="text-xl font-bold mb-2">Inicia sesión</h2>
        <p class="text-gray-500 text-sm mb-6">Necesitas una cuenta para continuar.</p>
        <a href="{{ route('login') }}"
           class="block w-full bg-indigo-600 text-white py-2.5 rounded-full font-medium hover:bg-indigo-700 mb-3">
            Iniciar sesión
        </a>
        <a href="{{ route('register') }}"
           class="block w-full border border-indigo-600 text-indigo-600 py-2.5 rounded-full font-medium hover:bg-indigo-50">
            Registrarse
        </a>
        <button onclick="closeModal()" class="mt-4 text-sm text-gray-400 hover:underline">Cancelar</button>
    </div>
</div>

<script>
function toggleMenu() {
    document.getElementById('userMenu').classList.toggle('hidden');
}
window.addEventListener('click', function(e) {
    if (!e.target.closest('#userMenu') && !e.target.closest('button')) {
        document.getElementById('userMenu').classList.add('hidden');
    }
});

function closeModal() {
    document.getElementById('authModal').classList.add('hidden');
}
document.getElementById('search').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && this.value.trim()) {
        window.location.href = '/tienda?q=' + encodeURIComponent(this.value);
    }
});
</script>

@stack('scripts')
</body>
</html>
