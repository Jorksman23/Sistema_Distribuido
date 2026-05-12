<nav class="bg-white shadow-sm border-b border-gray-100">

    {{-- FILA PRINCIPAL --}}
    <div class="px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3">

        {{-- Nombre Empresa --}}
        <a href="/" class="text-xl font-bold text-blue-600 shrink-0">
            {{ $empresaNombre }}
        </a>

        {{-- LINKS — escritorio --}}
        <div class="hidden md:flex gap-6 text-sm ml-4">
            <a href="/" class="text-gray-900 hover:text-blue-600 font-medium transition">Home</a>
            <a href="{{ route('catalogo.index') }}" class="text-gray-900 hover:text-blue-600 font-medium transition">Categorías</a>
            <a href="#" class="text-gray-900 hover:text-blue-600 font-medium transition">Sobre Nosotros</a>
        </div>

        {{-- BUSCADOR escritorio --}}
        <div class="flex-1 mx-2 md:mx-4 max-w-xl hidden sm:block">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="search" type="text" placeholder="Buscar productos..."
                       value="{{ request('q') }}"
                       class="w-full pl-9 pr-4 py-2 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- ICONOS --}}
        <div class="flex items-center gap-3 ml-auto">

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
                        <a href="{{ route('profile.show') }}" class="block px-4 py-2 hover:bg-gray-50">Mi perfil</a>
                        <a href="#" class="block px-4 py-2 hover:bg-gray-50">Agregar método de pago</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium
                                       text-red-400 hover:bg-red-50 hover:text-red-600 transition">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Cerrar sesión
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block px-4 py-2 hover:bg-gray-50">Iniciar sesión</a>
                        <a href="{{ route('register') }}" class="block px-4 py-2 hover:bg-gray-50">Registrarse</a>
                    @endif
                </div>
            </div>

            {{-- HAMBURGUESA — solo móvil --}}
            <button id="menuToggle" onclick="toggleMobileMenu()" class="md:hidden hover:scale-110 transition">
                <svg id="iconHamburger" class="w-6 h-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg id="iconClose" class="w-6 h-6 text-gray-700 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

        </div>
    </div>

    {{-- BUSCADOR móvil --}}
    <div class="sm:hidden px-4 pb-3">
        <div class="relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input id="search-mobile" type="text" placeholder="Buscar productos..."
                   value="{{ request('q') }}"
                   class="w-full pl-9 pr-4 py-2 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    {{-- MENÚ MÓVIL desplegable --}}
    <div id="mobileMenu" class="hidden md:hidden border-t border-gray-100 bg-white px-4 py-3 flex flex-col gap-1">
        <a href="/" class="py-2 text-gray-800 hover:text-blue-600 font-medium transition border-b border-gray-50">Home</a>
        <a href="{{ route('catalogo.index') }}" class="py-2 text-gray-800 hover:text-blue-600 font-medium transition border-b border-gray-50">Categorías</a>
        <a href="#" class="py-2 text-gray-800 hover:text-blue-600 font-medium transition">Sobre Nosotros</a>
    </div>

</nav>

<script>
function toggleMenu() {
    document.getElementById('userMenu').classList.toggle('hidden');
}

function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    const iconH = document.getElementById('iconHamburger');
    const iconC = document.getElementById('iconClose');
    menu.classList.toggle('hidden');
    iconH.classList.toggle('hidden');
    iconC.classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    const searchDesktop = document.getElementById('search');
    const searchMobile  = document.getElementById('search-mobile');

    if (searchDesktop) {
        searchDesktop.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && this.value.trim()) {
                window.location.href = '{{ route("catalogo.index") }}?q=' + encodeURIComponent(this.value.trim());
            }
        });
    }

    if (searchMobile) {
        searchMobile.addEventListener('keypress', function (e) {
            if (e.key === 'Enter' && this.value.trim()) {
                window.location.href = '{{ route("catalogo.index") }}?q=' + encodeURIComponent(this.value.trim());
            }
        });
    }
});
</script>
