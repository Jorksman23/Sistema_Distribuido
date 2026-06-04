@extends('layouts.app')

@section('title', 'Subir comprobante')

@section('content')

<div class="max-w-4xl mx-auto px-4 py-10">

    {{-- Título --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">
            Subir comprobante
        </h1>

        <p class="text-gray-500 mt-2">
            Adjunta tu comprobante de transferencia para validar tu pedido.
        </p>
    </div>

    {{-- Estado --}}
    <div class="mb-6">
        <span class="inline-flex items-center px-4 py-2 rounded-full bg-yellow-100 text-yellow-700 font-semibold">
            ⏳ Pendiente de Pago
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- DATOS BANCARIOS --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">

            <h2 class="text-xl font-bold text-gray-800 mb-5">
                Datos para transferencia
            </h2>

            @if($formaPago)
                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <p class="text-sm text-gray-500">
                        Forma de pago
                    </p>

                    <p class="font-semibold">
                        {{ $formaPago->forma_pago }}
                    </p>
                </div>
            @endif

            @if($cuentaBanco)

                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <p class="text-sm text-gray-500">
                        Banco
                    </p>

                    <p class="font-semibold">
                        {{ $cuentaBanco->descripcion }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-4 mb-4">
                    <p class="text-sm text-gray-500">
                        Número de cuenta
                    </p>

                    <p class="font-semibold">
                        {{ $cuentaBanco->cuenta }}
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm text-gray-500">
                        Tipo de cuenta
                    </p>

                    <p class="font-semibold">
                        {{ $cuentaBanco->tipo == 'C'
                            ? 'Cuenta Corriente'
                            : 'Cuenta Ahorros'
                        }}
                    </p>
                </div>

            @endif

        </div>

        {{-- SUBIR COMPROBANTE --}}
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">

            <form
                method="POST"
                action="{{ route('pedidos.comprobante.guardar') }}"
                enctype="multipart/form-data">

                @csrf

                <label
                    for="comprobante"
                    class="border-2 border-dashed border-gray-300 rounded-2xl p-8 flex flex-col items-center justify-center cursor-pointer hover:border-[#0300a3] transition">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-14 h-14 text-gray-400 mb-4"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.5"
                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>

                    <span class="font-semibold text-gray-700">
                        Selecciona tu comprobante
                    </span>

                    <span class="text-sm text-gray-500 mt-1">
                        JPG, PNG o PDF
                    </span>

                    <input
                        type="file"
                        id="comprobante"
                        name="comprobante"
                        required
                        class="hidden">
                </label>

                {{-- Información archivo --}}
                <div id="fileInfo"
                     class="hidden mt-4 relative p-3 rounded-xl bg-blue-50 border border-blue-200">

                     <button
                        type="button"
                        id="btnEliminarArchivo"
                        class="absolute top-2 right-2
                            w-8 h-8
                            rounded-full
                            bg-red-500
                            hover:bg-red-600
                            text-white
                            font-bold
                            shadow
                            transition">
                        ✕
                    </button>

                    <p id="fileName"
                       class="font-semibold text-blue-700">
                    </p>

                    <p id="fileSize"
                       class="text-sm text-blue-500">
                    </p>
                </div>
                <div id="previewContainer"
                    class="hidden mt-4">
                    <img id="previewImage"
                        class="rounded-xl border border-gray-200 w-full">
                    <div id="pdfPreview"
                        class="hidden p-4 rounded-xl bg-red-50 border border-red-200 text-center">
                        📄 Archivo PDF seleccionado
                    </div>

                </div>

                <button
                    type="submit"
                    class="w-full mt-6 bg-[#0300a3] hover:bg-[#0200cc] text-white font-bold py-4 rounded-2xl transition">

                    Enviar comprobante
                </button>

            </form>

        </div>

    </div>

</div>

<script>

document.getElementById('comprobante').addEventListener('change', function(){

    const file = this.files[0];
    if(!file) return;
    document.getElementById('fileInfo').classList.remove('hidden');
    document.getElementById('fileName').innerText = file.name;
    document.getElementById('fileSize').innerText = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('previewImage');
    const pdfPreview = document.getElementById('pdfPreview');

    previewContainer.classList.remove('hidden');

    if(file.type === 'application/pdf'){
        previewImage.classList.add('hidden');
        pdfPreview.classList.remove('hidden');
    }else{
        pdfPreview.classList.add('hidden');
        previewImage.classList.remove('hidden');
        const reader = new FileReader();
        reader.onload = function(e){
            previewImage.src = e.target.result;
        };

        reader.readAsDataURL(file);
    }
});

    document.getElementById('btnEliminarArchivo').addEventListener('click', function(){
    document.getElementById('comprobante').value = '';
    document.getElementById('fileInfo').classList.add('hidden');
    document.getElementById('previewContainer').classList.add('hidden');
    document.getElementById('previewImage').src = '';
    document.getElementById('pdfPreview').classList.add('hidden');
    });
</script>

@endsection
