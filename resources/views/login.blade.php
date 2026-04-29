<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Maremy</title>
</head>
<body>
    <h2>Iniciar Sesión</h2>

    @if ($errors->any())
        <p style="color:red">{{ $errors->first() }}</p>
    @endif

    <form method="POST" action="/login">
        @csrf
        <label>Usuario:</label>
        <input type="text" name="usuario" required><br><br>

        <label>Contraseña:</label>
        <input type="password" name="contrasena" required><br><br>

        <button type="submit">Entrar</button>
    </form>
</body>
</html>
