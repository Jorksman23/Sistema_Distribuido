<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stack('scripts')
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<nav class="bg-white shadow px-6 py-3 flex items-center gap-4">

    {{-- LOGO --}}
    <a href="/" class="text-xl font-bold text-blue-600 shrink-0">
        {{ config('app.name') }}
    </a>

    {{-- LINKS --}}
    {{-- Aqui deben ir los links para las demas vistas --}}
    <div class="hidden md:flex gap-6 text-base ml-4">
        <a href="/" class="text-gray-900 hover:text-blue-600 font-medium transition">Home</a>
        <a href="#" class="text-gray-900 hover:text-blue-600 font-medium transition">Categorías</a>
        <a href="#" class="text-gray-900 hover:text-blue-600 font-medium transition">Tienda</a>
        <a href="#" class="text-gray-900 hover:text-blue-600 font-medium transition">Sobre Nosotros</a>
    </div>

    {{-- BUSCADOR --}}
    <div class="flex-1 mx-4 max-w-xl">
        <div class="relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="search" type="text" placeholder="Buscar productos..."
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
        <button onclick="toggleCart()" class="hover:scale-110 transition relative">
            🛒
            <span id="carritoCount"
                class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                0
            </span>
        </button>

        {{-- USUARIO --}}
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

<script>
function toggleMenu() {
    document.getElementById('userMenu').classList.toggle('hidden');
}

function toggleCart() {
    const cart = document.getElementById('cartDrawer');
    cart.classList.toggle('translate-x-full');
    renderCart();
}

/* ===== CARRITO ===== */

function getCart() {
    try {
        return JSON.parse(localStorage.getItem('cart')) || [];
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function addToCart(productId) {
    let cart = getCart();
    // Evitar duplicados
    if (!cart.includes(productId)) {
        cart.push(productId);
        saveCart(cart);
    }
    updateCartCount();
    renderCart();
}

function removeFromCart(productId) {
    let cart = getCart().filter(id => id !== productId);
    saveCart(cart);
    updateCartCount();
    renderCart();
}

function updateCartCount() {
    const cart = getCart();
    const badge = document.getElementById('carritoCount');
    if (!badge) return;

    if (cart.length > 0) {
        badge.classList.remove('hidden');
        badge.textContent = cart.length;
    } else {
        badge.classList.add('hidden');
    }
}

function renderCart() {
    const cart = getCart();
    const container = document.getElementById('cartContent');
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (!container || !checkoutBtn) return;

    if (cart.length === 0) {
        container.innerHTML = `
            <span class="text-4xl">🛒</span>
            <p class="mt-4">Tu carrito está vacío</p>
        `;
        checkoutBtn.classList.add('hidden');
    } else {
        container.innerHTML = cart.map(id => `
            <div class="flex justify-between items-center border-b py-2">
                <span>Producto ${id}</span>
                <button onclick="removeFromCart('${id}')"
                        class="text-red-500 hover:text-red-700">Eliminar</button>
            </div>
        `).join('');
        checkoutBtn.classList.remove('hidden');
    }
}


document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
    renderCart();
});
</script>

<div id="cartDrawer"
    class="fixed top-0 right-0 w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50">

    <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-lg font-bold">Tu carrito</h2>
        <button onclick="toggleCart()" class="text-xl">&times;</button>
    </div>

    <div class="flex flex-col items-center justify-center h-full text-gray-500">

        @if(session('user_id'))

            <div id="cartContent" class="text-center">
                <span class="text-4xl">🛒</span>
                <p class="mt-4">Tu carrito está vacío</p>
            </div>

            <button id="checkoutBtn"
                    class="hidden mt-4 bg-orange-500 text-white px-5 py-2 rounded">
                Proceder al pago
            </button>

        @else

            <span class="text-4xl">🔒</span>
            <p class="mt-4">Debes iniciar sesión para usar el carrito</p>

            <a href="{{ route('login') }}" class="mt-4 bg-blue-600 text-white px-5 py-2 rounded">
                Iniciar sesión
            </a>

        @endif

    </div>
</div>

</body>
</html>
