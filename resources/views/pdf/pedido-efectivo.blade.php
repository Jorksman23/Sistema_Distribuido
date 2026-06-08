<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido {{ $orden->codigo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #333;
            background: #f8f9fa;
            padding: 30px;
        }

        .documento {
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            max-width: 700px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
        }

        /* HEADER */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #0300a3;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header-left { display: table-cell; vertical-align: middle; }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }
        .empresa-nombre {
            font-size: 18px;
            font-weight: bold;
            color: #0300a3;
        }
        .empresa-subtitulo {
            font-size: 10px;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 1px;
            margin-top: 2px;
        }
        .numero-pedido-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #888;
            letter-spacing: 1px;
        }
        .numero-pedido {
            font-size: 22px;
            font-weight: bold;
            color: #0300a3;
        }

        /* SECCIONES */
        .secciones {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .seccion {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        .seccion-derecha { padding-left: 16px; }
        .spacer { display: table-cell; width: 4%; }

        .seccion-titulo {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            font-weight: bold;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #f0f0f0;
        }

        .fila {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .fila-label {
            display: table-cell;
            color: #888;
            font-size: 12px;
            width: 40%;
        }
        .fila-valor {
            display: table-cell;
            font-weight: bold;
            font-size: 12px;
            text-align: right;
            color: #333;
        }

        /* BADGE ESTADO */
        .badge-reservado {
            display: inline-block;
            background: #eff6ff;
            color: #0300a3;
            border: 1px solid #bfdbfe;
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: bold;
        }

        /* TOTAL */
        .total-valor {
            font-size: 20px;
            font-weight: bold;
            color: #0300a3;
        }

        /* IMPORTANTE */
        .importante {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 6px;
            padding: 14px 16px;
            margin-top: 4px;
        }
        .importante-titulo {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 6px;
        }
        .importante p {
            font-size: 12px;
            color: #78350f;
            line-height: 1.6;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #aaa;
            border-top: 1px solid #f0f0f0;
            padding-top: 12px;
        }
    </style>
</head>
<body>
<div class="documento">

    {{-- HEADER --}}
    <div class="header">
        <div class="header-left">
            <div class="empresa-nombre">Eli Importadora</div>
            <div class="empresa-subtitulo">Comprobante de Pedido</div>
        </div>
        <div class="header-right">
            <div class="numero-pedido-label">Número de Pedido</div>
            <div class="numero-pedido">#{{ $orden->codigo }}</div>
        </div>
    </div>

    {{-- DOS COLUMNAS --}}
    <div class="secciones">

        {{-- DATOS DEL CLIENTE --}}
        <div class="seccion">
            <div class="seccion-titulo">👤 Datos del Cliente</div>

            <div class="fila">
                <div class="fila-label">Cliente:</div>
                <div class="fila-valor">{{ $orden->nombre_cliente }}</div>
            </div>
            <div class="fila">
                <div class="fila-label">Cédula:</div>
                <div class="fila-valor">{{ $orden->cedula_cliente }}</div>
            </div>
            <div class="fila">
                <div class="fila-label">Email:</div>
                <div class="fila-valor">{{ $orden->email_cliente }}</div>
            </div>
            <div class="fila">
                <div class="fila-label">Teléfono:</div>
                <div class="fila-valor">{{ $orden->telefono_cliente }}</div>
            </div>
        </div>

        <div class="spacer"></div>

        {{-- DETALLES DE TRANSACCIÓN --}}
        <div class="seccion">
            <div class="seccion-titulo">🧾 Detalles de Transacción</div>

            <div class="fila">
                <div class="fila-label">Forma de pago:</div>
                <div class="fila-valor">EFECTIVO</div>
            </div>
            <div class="fila">
                <div class="fila-label">Estado:</div>
                <div class="fila-valor">
                    <span class="badge-reservado">RESERVADO</span>
                </div>
            </div>
            <div class="fila" style="margin-top:10px;">
                <div class="fila-label">Total:</div>
                <div class="fila-valor total-valor">
                    ${{ number_format($orden->gran_total, 2) }}
                </div>
            </div>
        </div>

    </div>

    {{-- IMPORTANTE --}}
    <div class="importante">
        <div class="importante-titulo">ⓘ Importante</div>
        <p>
            Presente este comprobante en tienda para realizar el pago y retirar su
            pedido. Este documento es válido únicamente para la transacción
            especificada y garantiza la reserva de sus productos por un período limitado.
        </p>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }} — {{ $orden->codigo }}
    </div>

</div>
</body>
</html>
