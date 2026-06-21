@extends('layouts.app')
@section('title', $empresaNombre)

@section('content')
{{-- ===== CARRUSEL INFINITO ===== --}}
@if(isset($carrusel) && count($carrusel) > 0)
<div class="mt-12 mb-4">
    <div class="max-w-7xl mx-auto px-4 mb-4 flex items-center justify-between">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">Productos Destacados</h2>
    </div>

    <div class="overflow-hidden relative">

        <div class="carrusel-track flex gap-4 py-4 px-2">

            {{-- Primera pasada --}}
            @foreach($carrusel as $prod)

                <div class="shrink-0 w-48 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col">

                    <div class="relative h-40 bg-gray-50 overflow-hidden">

                        <img src="{{ $prod->imagen_url }}"
                             alt="{{ $prod->descripcion1 }}"
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                             onerror="this.onerror=null;this.src='https://placehold.co/200x160?text=Sin+imagen'">

                        @if($prod->categoria)
                            <span class="absolute top-2 left-2 bg-white/90 text-[#0300a3] text-xs font-bold px-2 py-0.5 rounded-full uppercase">
                                {{ $prod->categoria }}
                            </span>
                        @endif

                    </div>

                    <div class="p-3 flex flex-col flex-1">

                        <h3 class="text-sm font-bold text-gray-900 leading-snug mb-2 line-clamp-2 min-h-[2.8rem]">
                            {{ $prod->descripcion1 }}
                        </h3>

                        <p class="text-sm font-bold text-gray-900">
                            ${{ $prod->pvp1 }}
                        </p>

                        <a href="{{ route('products.show', $prod->codigo) }}"
                           class="mt-auto block text-center text-xs text-white bg-gray-800 hover:bg-gray-900 py-2 rounded-lg transition font-medium">
                            Ver detalle
                        </a>

                    </div>
                </div>

            @endforeach

            {{-- Segunda pasada (duplicado para loop infinito) --}}
            @foreach($carrusel as $prod)

                <div class="shrink-0 w-48 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-300 flex flex-col">

                    <div class="relative h-40 bg-gray-50 overflow-hidden">

                        <img src="{{ $prod->imagen_url }}"
                             alt="{{ $prod->descripcion1 }}"
                             class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                             onerror="this.onerror=null;this.src='https://placehold.co/200x160?text=Sin+imagen'">

                        @if($prod->categoria)
                            <span class="absolute top-2 left-2 bg-white/90 text-[#0300a3] text-xs font-bold px-2 py-0.5 rounded-full uppercase">
                                {{ $prod->categoria }}
                            </span>
                        @endif

                    </div>

                    <div class="p-3 flex flex-col flex-1">

                        <h3 class="text-sm font-bold text-gray-900 leading-snug mb-2 line-clamp-2 min-h-[2.8rem]">
                            {{ $prod->descripcion1 }}
                        </h3>

                        <p class="text-sm font-bold text-gray-900">
                            ${{ $prod->pvp1 }}
                        </p>

                        <a href="{{ route('products.show', $prod->codigo) }}"
                           class="mt-auto block text-center text-xs text-white bg-gray-800 hover:bg-gray-900 py-2 rounded-lg transition font-medium">
                            Ver detalle
                        </a>

                    </div>
                </div>

            @endforeach

        </div>{{-- fin carrusel-track --}}

    </div>{{-- fin overflow-hidden --}}
</div>{{-- fin carrusel --}}
@endif


    {{-- 🔹 Título general --}}
    <h2 class="text-center text-[#3E7CB4] text-2xl font-bold mt-10 mb-6">
        {{ $homeData['publicidad'] }}
    </h2>

    {{-- Banner grande --}}
    @if(!empty($homeData['slider'][0]))
    <div class="w-full mb-10">
        <figure class="relative w-full h-72 bg-gray-100 rounded-lg shadow-lg overflow-hidden flex items-center justify-center">
            <img src="{{ productImageUrl($homeData['slider'][0]) }}"
                alt="Banner grande"
                class="w-full h-full object-cover">
            <figcaption class="absolute bottom-4 left-6 text-black text-xl font-semibold drop-shadow-lg">
                {{ $homeData['tituloSeccion'] }}
            </figcaption>
        </figure>
    </div>
    @endif
     {{-- Contenedor --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10 bg-gray-50 p-6 rounded-lg shadow-md">

        {{--Banners medianos}--}}
        <div class="col-span-3 grid grid-cols-2 gap-6">
            @foreach(['MD','MD2'] as $param)
                @php $img = productImageUrl($parametros[$param] ?? null); @endphp
                <figure class="relative h-56 bg-gray-100 rounded-lg shadow-lg overflow-hidden flex items-center justify-center">
                    @if($img)
                        <img src="{{ $img }}" alt="Banner medio" class="w-full h-full object-cover">
                    @else
                        <span class="text-gray-400 text-lg font-semibold">Banner medio</span>
                    @endif
                    <figcaption class="absolute bottom-3 left-4 text-black text-lg font-semibold drop-shadow-md">
                        {{ $homeData['masVendidos'] }}
                    </figcaption>
                </figure>
            @endforeach

            {{--Banner medio inferior --}}
            @php $imgMedio3 = productImageUrl($parametros['MD3'] ?? null); @endphp
            <figure class="relative h-56 bg-gray-100 rounded-lg shadow-lg overflow-hidden flex items-center justify-center col-span-2">
                @if($imgMedio3)
                    <img src="{{ $imgMedio3 }}" alt="Banner medio" class="w-full h-full object-cover">
                @else
                    <span class="text-gray-400 text-lg font-semibold">Banner medio</span>
                @endif
                <figcaption class="absolute bottom-3 left-4 text-black text-lg font-semibold drop-shadow-md">
                    {{ $homeData['masVendidos'] }}
                </figcaption>
            </figure>
        </div>

        {{--Cuatro banners pequeños al lado derecho --}}
        <div class="grid grid-cols-2 gap-4 h-full">
            @foreach(['BF1','BF2','BP1','BP2'] as $param)
                @php $img = productImageUrl($parametros[$param] ?? null); @endphp
                <figure class="relative h-56 bg-gray-100 rounded-lg shadow-lg overflow-hidden flex items-center justify-center">
                    @if($img)
                        <img src="{{ $img }}" alt="Banner pequeño" class="w-full h-full object-cover">
                    @else
                        <span class="text-gray-400 text-sm font-semibold">Banner pequeño</span>
                    @endif
                    <figcaption class="absolute bottom-2 left-3 text-black text-sm font-semibold drop-shadow-md">
                        {{ $homeData['ofertas'] }}
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>

@push('scripts')
<style>
    .carrusel-track {
        animation: scrollCarrusel 30s linear infinite;
        width: max-content;
    }
    .carrusel-track:hover {
        animation-play-state: paused;
    }
    @keyframes scrollCarrusel {
        0%   { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
</style>
@endpush

@endsection
