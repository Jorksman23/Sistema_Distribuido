<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color:#111; background:#f5f7fb; margin:0; padding:24px;">
    <table cellpadding="0" cellspacing="0" width="100%" style="max-width:560px; margin:0 auto; background:white; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="background:#4f46e5; color:white; padding:24px;">
                <h1 style="margin:0; font-size:20px;">Recupera tu contraseña</h1>
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <p>Hola {{ $cliente->nombre ?? '' }},</p>
                <p>Solicitaste restablecer la contraseña de tu cuenta en {{ config('app.name') }}.</p>
                <p style="text-align:center; margin:28px 0;">
                    <a href="{{ $url }}" style="background:#4f46e5; color:white; padding:12px 24px; border-radius:9999px; text-decoration:none; font-weight:600;">
                        Elegir nueva contraseña
                    </a>
                </p>
                <p style="font-size:12px; color:#64748b;">
                    Este enlace expira en 60 minutos. Si no solicitaste este cambio, ignora este correo.
                </p>
                <p style="font-size:12px; color:#64748b; word-break:break-all;">
                    Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
                    <a href="{{ $url }}">{{ $url }}</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
