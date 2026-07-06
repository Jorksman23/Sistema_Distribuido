<footer class="bg-[#020817] text-gray-300 mt-16">
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

            {{-- EMPRESA --}}
            <div>
                <h2 class="text-2xl font-bold text-white">
                    {{ \App\Models\Empresa::getNombre() }}
                </h2>
            </div>

            {{-- ENLACES EMPRESA --}}
            <div>
                <h3 class="text-white font-semibold mb-4 uppercase text-sm">Empresa</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">Nosotros</a></li>
                    <li><a href="#" class="hover:text-white transition">Progreso</a></li>
                    <li><a href="#" class="hover:text-white transition">Términos y condiciones</a></li>
                </ul>
            </div>

            {{-- SERVICIO AL CLIENTE --}}
            <div>
                <h3 class="text-white font-semibold mb-4 uppercase text-sm">Servicio al cliente</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-white transition">Preguntas frecuentes</a></li>
                    <li><a href="#" class="hover:text-white transition">Envíos</a></li>
                    <li><a href="#" class="hover:text-white transition">Soporte</a></li>
                </ul>
            </div>

            {{-- CONTACTO --}}
            <div>
                <h3 class="text-white font-semibold mb-4 uppercase text-sm">Contacto</h3>

                {{-- CORREO --}}
                @if(!empty($footerData['correo']))
                    <div class="flex items-center gap-2 text-gray-300 mb-2">
                        <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4h16v16H4z M22 6l-10 7L2 6"/>
                        </svg>
                        <a href="mailto:{{ $footerData['correo'] }}" class="hover:text-sky-400 transition">
                            {{ $footerData['correo'] }}
                        </a>
                    </div>
                @endif

                {{-- UBICACIÓN --}}
                @if(!empty($footerData['direccion']))
                    <div class="flex items-center gap-2 text-gray-300 mb-2">
                        <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <a href="{{ $footerData['direccion'] }}" target="_blank" class="hover:text-sky-400 transition">
                            Ver ubicación
                        </a>
                    </div>
                @endif
                     {{-- REDES SOCIALES --}}
                <div class="flex items-center gap-4 mt-4">
                    @if(!empty($footerData['facebook']))
                        <a href="{{ $footerData['facebook'] }}" target="_blank"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-900 hover:bg-sky-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22 12.07C22 6.49 17.52 2 12 2S2 6.49 2 12.07c0 5.02 3.66 9.18 8.44 9.93v-7.03H7.9v-2.9h2.54V9.84c0-2.52 1.49-3.92 3.78-3.92 1.1 0 2.25.2 2.25.2v2.48h-1.27c-1.25 0-1.64.78-1.64 1.58v1.9h2.79l-.45 2.9h-2.34V22c4.78-.75 8.44-4.91 8.44-9.93z"/>
                            </svg>
                        </a>
                    @endif

                    @if(!empty($footerData['instagram']))
                        <a href="{{ $footerData['instagram'] }}" target="_blank"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-900 hover:bg-sky-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="20" rx="5"/>
                                <path d="M16 11.37a4 4 0 1 1-7.75 1.26 4 4 0 0 1 7.75-1.26z"/>
                                <line x1="17.5" y1="6.5" x2="17.5" y2="6.5"/>
                            </svg>
                        </a>
                    @endif

                    @if(!empty($footerData['twitter']))
                        <a href="{{ $footerData['twitter'] }}" target="_blank"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-900 hover:bg-sky-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2H21l-6.56 7.5L22.5 22h-6.67l-5.22-6.82L4.5 22H1.74l7.02-8.02L1.5 2h6.84l4.72 6.23L18.244 2zm-2.34 18h1.85L7.1 3.9H5.14L15.904 20z"/>
                            </svg>
                        </a>
                    @endif

                    @if(!empty($footerData['youtube']))
                        <a href="{{ $footerData['youtube'] }}" target="_blank"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-gray-900 hover:bg-sky-500 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10 15l5.19-3L10 9v6zm12-3c0-1.66-.34-3.16-.94-4.57-.6-1.41-1.41-2.65-2.43-3.67C17.61 2.74 15.66 2 13.5 2h-3C8.34 2 6.39 2.74 4.37 3.76 3.35 4.78 2.54 6.02 1.94 7.43 1.34 8.84 1 10.34 1 12c0 1.66.34 3.16.94 4.57.6 1.41 1.41 2.65 2.43 3.67C6.39 21.26 8.34 22 10.5 22h3c2.16 0 4.11-.74 6.13-1.76 1.02-1.02 1.83-2.26 2.43-3.67.6-1.41.94-2.91.94-4.57z"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>


    <div class="border-t border-gray-800 mt-10 pt-6 text-center text-sm text-gray-500">
        © {{ date('Y') }} {{ \App\Models\Empresa::getNombre() }}. Todos los derechos reservados.
    </div>
</footer>
