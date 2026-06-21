<nav class="bg-white/95 backdrop-blur-md shadow-sm border-b border-gray-100 sticky top-0 z-50">

    {{-- FILA PRINCIPAL --}}
    <div class="px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-3">

       {{-- Logo + Nombre Empresa --}}
        <a href="/" class="flex items-center gap-2 text-lg sm:text-xl lg:text-2xl font-extrabold tracking-tight text-[#3E7CB4] min-w-0 hover:opacity-90 transition">
            {{-- Mostrar logo si existe --}}
            @if(companyLogoUrl())
                <div class="flex items-center justify-center w-12 h-12 overflow-hidden rounded-full border-2 border-[#3E7CB4] bg-white">
                    <img src="{{ companyLogoUrl() }}" alt="Logo {{ $empresaNombre }}" class="object-contain w-full h-full">
                </div>
            @else
                {{-- Ícono por defecto: el mismo que ya tenías --}}
                <svg class="w-7 h-7 text-[#3E7CB4]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" d="M5.535 7.677c.313-.98.687-2.023.926-2.677H17.46c.253.63.646 1.64.977 2.61.166.487.312.953.416 1.347.11.42.148.675.148.779 0 .18-.032.355-.09.515-.06.161-.144.3-.243.412-.1.111-.21.192-.324.245a.809.809 0 0 1-.686 0 1.004 1.004 0 0 1-.324-.245c-.1-.112-.183-.25-.242-.412a1.473 1.473 0 0 1-.091-.515 1 1 0 1 0-2 0 1.4 1.4 0 0 1-.333.927.896.896 0 0 1-.667.323.896.896 0 0 1-.667-.323A1.401 1.401 0 0 1 13 9.736a1 1 0 1 0-2 0 1.4 1.4 0 0 1-.333.927.896.896 0 0 1-.667.323.896.896 0 0 1-.667-.323A1.4 1.4 0 0 1 9 9.74v-.008a1 1 0 0 0-2 .003v.008a1.504 1.504 0 0 1-.18.712 1.22 1.22 0 0 1-.146.209l-.007.007a1.01 1.01 0 0 1-.325.248.82.82 0 0 1-.316.08.973.973 0 0 1-.563-.256 1.224 1.224 0 0 1-.102-.103A1.518 1.518 0 0 1 5 9.724v-.006a2.543 2.543 0 0 1 .029-.207c.024-.132.06-.296.11-.49.098-.385.237-.85.395-1.344Z" clip-rule="evenodd"/>
                </svg>
            @endif

            <span class="truncate max-w-[170px] sm:max-w-none">
                {{ $empresaNombre }}
            </span>
        </a>


        {{-- LINKS — escritorio --}}
        <div class="hidden md:flex gap-6 text-base ml-4 ">
            <a href="/" class="text-gray-700 hover:text-[#0300a3] font-bold transition relative after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-[#0300a3] after:transition-all">Home</a>
            <a href="{{ route('catalogo.index') }}" class="text-gray-700 hover:text-[#0300a3] font-bold transition relative after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-[#0300a3] after:transition-all">Tienda</a>
            <a href="#" class="whitespace-nowrap text-gray-700 hover:text-[#0300a3] font-bold transition relative after:absolute after:left-0 after:-bottom-1 after:h-[2px] after:w-0 hover:after:w-full after:bg-[#0300a3] after:transition-all">Sobre Nosotros</a>
        </div>

        {{-- BUSCADOR escritorio --}}
        <div class="flex-1 mx-2 md:mx-4 max-w-xl hidden sm:block">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="search" type="text" placeholder="Buscar productos..."
                       value="{{ request('q') }}"
                       class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-[#0300a3]/30 focus:border-[#0300a3] transition">
            </div>
        </div>

        {{-- ICONOS --}}
        <div class="flex items-center gap-2 sm:gap-3 ml-auto shrink-0">

            {{-- WISHLIST --}}
            <a href="{{ route('wishlist.index') }}" class="hover:scale-110 transition-all duration-200 relative">
            <svg class="w-6 h-6 text-gray-600 hover:text-red-500 transition-all duration-200"
                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
            <span id="wishCountBadge"
                class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center {{ (session('user_id') && isset($wishCount) && $wishCount > 0) ? '' : 'hidden' }}">
                {{ session('user_id') && isset($wishCount) ? $wishCount : 0 }}
            </span>
            </a>

            {{-- CARRITO  AQUI VAMOS A HACER EL CAMBIO--}}
            <a href="{{ route('carrito.index') }}" class="hover:scale-110 transition-all duration-200 relative">
                <svg class="w-6 h-6 text-gray-600 hover:text-black transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span id="carritoCountBadge"
                    class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center {{ (session('user_id') && isset($carritoCount) && $carritoCount > 0) ? '' : 'hidden' }}">
                    {{ session('user_id') && isset($carritoCount) ? $carritoCount : 0 }}
                </span>
            </a>

            {{-- USUARIO --}}
            <div class="relative flex items-center">
                <button onclick="toggleMenu()" class="hover:scale-110 transition-all duration-200 relative">
                    <svg class="w-6 h-6 text-gray-600 hover:text-black transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0a8.949 8.949 0 0 0 4.951-1.488A3.987 3.987 0 0 0 13 16h-2a3.987 3.987 0 0 0-3.951 3.512A8.948 8.948 0 0 0 12 21Zm3-11a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </button>

                <div id="userMenu" class="hidden absolute right-0 top-9 w-44 sm:w-52 bg-white border border-gray-100 rounded-xl shadow-lg overflow-hidden z-50 text-sm">
                    @if(session('user_id'))
                        <div class="px-4 py-3 border-b text-gray-700 font-semibold truncate">
                            {{ session('nombre') }}
                        </div>
                        <a href="{{ route('profile.show') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium
                                text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Mi perfil
                        </a>
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
                        <a href="{{ route('login') }}"
                        class="block px-4 py-3 text-gray-700 text-sm font-medium hover:bg-[#f3f7ff] hover:text-[#0300a3] transition border-b border-gray-100">
                            Iniciar sesión
                        </a>

                        <a href="{{ route('register') }}"
                        class="block px-4 py-3 text-gray-700 text-sm font-medium hover:bg-[#f3f7ff] hover:text-[#0300a3] transition">
                            Registrarse
                        </a>
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
        <a href="/" class="py-2 text-gray-800 hover:text-blue-600 font-bold transition border-b border-gray-50">Home</a>
        <a href="{{ route('catalogo.index') }}" class="py-2 text-gray-800 hover:text-blue-600 font-bold transition border-b border-gray-50">Tienda</a>
        <a href="#" class="whitespace-nowrap py-2 text-gray-800 hover:text-blue-600 font-bold transition">Sobre Nosotros</a>
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
document.addEventListener('click', function(e) {
    const menu   = document.getElementById('userMenu');
    const button = e.target.closest('[onclick="toggleMenu()"]');
    if (!button && !menu.contains(e.target)) {
        menu.classList.add('hidden');
    }
});
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
