<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>
<body class="bg-gray-100 flex justify-center items-center h-screen">

<div class="bg-white p-6 rounded shadow w-96">

    <h2 class="text-xl font-bold mb-4 text-center">Login</h2>

    <input id="email" type="email" placeholder="Email"
        class="w-full mb-3 p-2 border rounded">

    <input id="password" type="password" placeholder="Password"
        class="w-full mb-3 p-2 border rounded">

    <button onclick="login()"
        class="w-full bg-blue-600 text-white p-2 rounded">
        Ingresar
    </button>

    <hr class="my-4">

    <h2 class="text-xl font-bold mb-4 text-center">Registro</h2>

    <input id="nombre" type="text" placeholder="Nombre"
        class="w-full mb-3 p-2 border rounded">

    <button onclick="register()"
        class="w-full bg-green-600 text-white p-2 rounded">
        Registrar
    </button>

</div>

<script>

function login(){

    fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        })
    })
    .then(res => res.json())
    .then(data => {

        if(data.error){
            alert(data.error);
            return;
        }

        // 🔑 guardar token
        localStorage.setItem('token', data.token);

        alert('Login correcto');

        // redirigir
        window.location.href = '/';
    });

}

function register(){

    fetch('/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            nombre: document.getElementById('nombre').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        })
    })
    .then(res => res.json())
    .then(data => {

        if(data.error){
            alert(data.error);
            return;
        }

        // 🔑 guardar token automáticamente
        localStorage.setItem('token', data.token);

        alert('Registrado correctamente');

        window.location.href = '/';
    });

}
</script>

</body>
</html>
