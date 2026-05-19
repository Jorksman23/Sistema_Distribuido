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

    @include('partials.navbar')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer')


</body>
</html>
