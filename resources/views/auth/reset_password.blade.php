@extends('layouts.app')
@section('title', 'Nueva contraseña')

@section('content')
    <div class="max-w-md mx-auto mt-11 bg-white border border-slate-200 rounded-2xl p-8 sm:p-7 shadow-md">
        <h1 class="text-2xl font-bold mb-8 text-center">Elige una nueva contraseña</h1>

        @if($errors->any())
            <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 p-4 text-rose-800 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Formulario de reseteo -->
        <form method="POST" action="{{ route('password.reset.submit') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label class="block">
                <span class="text-sm text-slate-600">Email</span>
                <input type="email" name="email" value="{{ old('email', $email) }}" required
                       class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </label>

            {{-- <label class="block">
                <span class="text-sm text-slate-600">Nueva contraseña (mín. 8)</span>
                <input type="password" name="password" required
                       class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </label>

            <label class="block">
                <span class="text-sm text-slate-600">Confirmar</span>
                <input type="password" name="password_confirmation" required
                       class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </label> --}}
            <label class="block">
                <span class="text-sm text-slate-600">Nueva contraseña (mín. 8)</span>
                <div class="relative mt-2">
                    <input type="password" name="password" id="newPassword" required
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-11 focus:ring-2 focus:ring-indigo-500 focus:outline-none">

                    <button type="button" onclick="togglePassword('newPassword','eyeOpen1','eyeClosed1')"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 transition">

                        <!-- Ojo abierto -->
                        <svg id="eyeOpen1" xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>

                        <!-- Ojo cerrado -->
                        <svg id="eyeClosed1" xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.1-2.407A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.132 5.411M15 12a3 3 0 00-3-3m0 0a2.99 2.99 0 00-2.12.879M12 9l9 9M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
            </label>

            <label class="block">
    <span class="text-sm text-slate-600">Confirmar</span>
    <div class="relative mt-2">
        <input type="password" name="password_confirmation" id="confirmPassword" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-11 focus:ring-2 focus:ring-indigo-500 focus:outline-none">

        <button type="button" onclick="togglePassword('confirmPassword','eyeOpen2','eyeClosed2')"
                class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-slate-600 transition">

            <!-- Ojo abierto -->
            <svg id="eyeOpen2" xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>

            <!-- Ojo cerrado -->
            <svg id="eyeClosed2" xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.1-2.407A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.132 5.411M15 12a3 3 0 00-3-3m0 0a2.99 2.99 0 00-2.12.879M12 9l9 9M3 3l18 18"/>
            </svg>
        </button>
    </div>
            </label>
            <div class="pt-4">
                <button type="submit"
                        class="w-full rounded-full bg-indigo-600 text-white py-3 font-medium hover:bg-indigo-700 transition-colors">
                    Restablecer contraseña
                </button>
            </div>
        </form>
    </div>
    <script>
function togglePassword(inputId, eyeOpenId, eyeClosedId) {
    const input = document.getElementById(inputId);
    const eyeOpen = document.getElementById(eyeOpenId);
    const eyeClosed = document.getElementById(eyeClosedId);

    if (input.type === 'password') {
        input.type = 'text';
        eyeOpen.classList.add('hidden');
        eyeClosed.classList.remove('hidden');
    } else {
        input.type = 'password';
        eyeClosed.classList.add('hidden');
        eyeOpen.classList.remove('hidden');
    }
}
</script>
@endsection
