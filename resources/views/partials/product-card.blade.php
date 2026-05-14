<div class="min-w-0 bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden border border-gray-100 group">

    <div class="relative overflow-hidden bg-gray-50">
        <img src="{{ $product->imagen_url }}"
             alt="{{ $product->nombre }}"
             loading="lazy"
             class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-300"
             onerror="this.onerror=null;this.src='https://placehold.co/300x300?text=Sin+imagen'">

        @if($product->categoria ?? null)
            <span class="absolute top-2 left-2 bg-white/90 text-blue-600 text-xs font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">
                {{ $product->categoria }}
            </span>
        @endif

        {{-- CORAZÓN WISHLIST --}}
        <form method="POST" action="{{ route('wishlist.toggle') }}"
              class="absolute top-2 right-2">
            @csrf
            <input type="hidden" name="codigo_item" value="{{ $product->codigo }}">
            <input type="hidden" name="nombre"      value="{{ $product->nombre }}">
            <input type="hidden" name="pvp3"        value="{{ $product->precio }}">
            <input type="hidden" name="imagen"      value="{{ $product->imagen }}">
            <input type="hidden" name="redirect_to" value="{{ $redirect_to ?? '' }}">
            <button type="submit"
                    class="w-8 h-8 bg-white/90 rounded-full flex items-center justify-center hover:bg-white transition shadow-sm hover:scale-110">
                @if(in_array($product->codigo, $wishCodes ?? []))
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                @else
                    <svg class="w-4 h-4 text-gray-400 hover:text-red-500 transition" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                @endif
            </button>
        </form>
    </div>

    <div class="p-3">
        <h3 class="text-xs font-semibold text-gray-800 leading-snug mb-2 line-clamp-2 min-h-[2rem]">
            {{ $product->nombre }}
        </h3>
        <p class="text-base font-bold text-gray-900">${{ $product->precio }}</p>

        <div class="mt-2 flex gap-1.5">
            {{-- VER DETALLE --}}
            <a href="{{ route('products.show', $product->codigo) }}"
               class="flex-1 text-center text-xs text-white bg-gray-800 px-2 py-1.5 rounded-lg hover:bg-gray-900 transition font-medium">
                Ver detalle
            </a>

            {{-- AGREGAR AL CARRITO --}}
            <form method="POST" action="{{ route('carrito.add') }}">
                @csrf
                <input type="hidden" name="codigo_item"  value="{{ $product->codigo }}">
                <input type="hidden" name="nombre"       value="{{ $product->nombre }}">
                <input type="hidden" name="pvp3"         value="{{ $product->precio }}">
                <input type="hidden" name="imagen"       value="{{ $product->imagen }}">
                <input type="hidden" name="presentacion" value="0">
                <button type="submit"
                        class="text-xs text-white bg-[#0300a3] px-2 py-1.5 rounded-lg hover:bg-[#0200cc] transition font-medium">
                    + Carrito
                </button>
            </form>
        </div>
    </div>
</div>
