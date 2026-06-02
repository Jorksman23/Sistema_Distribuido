<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación de correo</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f7fb; margin:0; padding:24px;">
    <table cellpadding="0" cellspacing="0" width="100%" style="max-width:560px; margin:0 auto; background:white; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="background:#4f46e5; color:white; padding:24px;">
                <h1 style="margin:0; font-size:20px;">Confirma tu correo electrónico</h1>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <p>Hola {{ $user->nombre ?? 'Usuario' }},</p>
                <p>Gracias por registrarte en <strong>{{ config('app.name') }}</strong>.
                   Haz clic en el botón de abajo para validar tu correo electrónico:</p>

                <p style="text-align:center; margin:28px 0;">
                    <a href="{{ $verificationUrl }}"
                       style="background:#4f46e5; color:white; padding:12px 24px; border-radius:9999px; text-decoration:none; font-weight:600;">
                        Verificar correo
                    </a>
                </p>

                <p style="font-size:12px; color:#64748b;">
                    Si no solicitaste esta cuenta, puedes ignorar este correo.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
