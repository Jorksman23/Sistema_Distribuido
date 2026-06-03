@extends('layouts.app')
@section('title', 'Verificación de correo')

@section('content')
<div class="fixed inset-0 bg-black/40 z-40"></div>

<div class="fixed inset-0 z-50 flex items-center justify-center pointer-events-none">
    <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl p-8 shadow-xl pointer-events-auto text-center">

        <h1 class="text-2xl font-bold mb-4 text-indigo-700">
            Verifica tu correo electrónico
        </h1>

        <p class="text-slate-600 mb-6">
            Te hemos enviado un enlace de confirmación a tu correo.
            Haz clic en el botón de abajo si necesitas que te reenviemos el enlace.
        </p>

        @if(session('message'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-3 text-green-800 text-sm">
                {{ session('message') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 p-3 text-rose-800 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button id="resendBtn" type="submit"
                class="w-full rounded-full bg-indigo-600 text-white py-2.5 font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Reenviar enlace de verificación
            </button>
        </form>

        <p id="countdown" class="text-sm text-slate-500 mt-2 hidden"></p>

        <p class="text-sm text-slate-500 mt-4">
            Si ya verificaste tu correo, <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">inicia sesión aquí</a>.
        </p>
    </div>
</div>

<script>
    const resendBtn = document.getElementById('resendBtn');
    const countdown = document.getElementById('countdown');

    // Si se envió el formulario, activar el contador
    resendBtn.addEventListener('click', function() {
        let seconds = 60;
        resendBtn.disabled = true;
        countdown.classList.remove('hidden');
        countdown.textContent = `Puedes reenviar otro correo en ${seconds} segundos`;

        const interval = setInterval(() => {
            seconds--;
            countdown.textContent = `Puedes reenviar otro correo en ${seconds} segundos`;

            if (seconds <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
                countdown.classList.add('hidden');
            }
        }, 1000);
    });
</script>
@endsection
