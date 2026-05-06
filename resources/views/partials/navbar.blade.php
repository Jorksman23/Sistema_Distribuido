<div class="relative">

    <button onclick="toggleMenu()" class="hover:scale-110 transition">

        <svg class="w-6 h-6 text-gray-700 hover:text-blue-500"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>

        </svg>

    </button>

    <div id="userMenu"
         class="hidden absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-xl shadow-lg z-50 text-sm">

        @if(session('user_id'))

            <div class="px-4 py-3 border-b text-gray-700 font-semibold truncate">
                {{ session('nombre') }}
            </div>

            <a href="#"
               class="block px-4 py-2 hover:bg-gray-50">
                Mi perfil
            </a>

            <a href="#"
               class="block px-4 py-2 hover:bg-gray-50">
                Mis pedidos
            </a>

            <form method="POST"
                  action="{{ route('logout') }}">

                @csrf

                <button type="submit"
                        class="w-full text-left px-4 py-2 text-red-500 hover:bg-gray-50">

                    Cerrar sesión

                </button>

            </form>

        @else

            <a href="{{ route('login') }}"
               class="block px-4 py-2 hover:bg-gray-50">

                Iniciar sesión

            </a>

            <a href="{{ route('register') }}"
               class="block px-4 py-2 hover:bg-gray-50">

                Registrarse

            </a>

        @endif

    </div>

</div>
