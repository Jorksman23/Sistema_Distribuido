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
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #0300a3;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header-left { display: table-cell; vertical-align: middle; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; }
        .empresa-nombre { font-size: 18px; font-weight: bold; color: #0300a3; }
        .empresa-subtitulo { font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px; margin-top: 2px; }
        .numero-pedido-label { font-size: 9px; text-transform: uppercase; color: #888; letter-spacing: 1px; }
        .numero-pedido { font-size: 22px; font-weight: bold; color: #0300a3; }
        .secciones { display: table; width: 100%; margin-bottom: 20px; }
        .seccion { display: table-cell; width: 48%; vertical-align: top; padding: 16px; border: 1px solid #e5e7eb; border-radius: 6px; }
        .spacer { display: table-cell; width: 4%; }
        .seccion-titulo { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: bold; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #f0f0f0; }
        .fila { display: table; width: 100%; margin-bottom: 8px; }
        .fila-label { display: table-cell; color: #888; font-size: 12px; width: 40%; }
        .fila-valor { display: table-cell; font-weight: bold; font-size: 12px; text-align: right; color: #333; }
        .badge-reservado { display: inline-block; background: #eff6ff; color: #0300a3; border: 1px solid #bfdbfe; border-radius: 20px; padding: 2px 10px; font-size: 11px; font-weight: bold; }
        .total-valor { font-size: 20px; font-weight: bold; color: #0300a3; }
        .importante { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 6px; padding: 14px 16px; margin-top: 4px; }
        .importante-titulo { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: bold; color: #92400e; margin-bottom: 6px; }
        .importante p { font-size: 12px; color: #78350f; line-height: 1.6; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; color: #aaa; border-top: 1px solid #f0f0f0; padding-top: 12px; }
        .tabla-productos { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .tabla-productos thead tr { background: #f3f4f6; border-bottom: 1px solid #ddd; }
        .tabla-productos th { padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: bold; }
        .tabla-productos td { padding: 8px 10px; font-size: 12px; border-bottom: 1px solid #f0f0f0; }
        .tabla-productos tfoot td { border-top: 2px solid #ddd; border-bottom: none; padding: 10px; }
        .badge-iva { display: inline-block; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; border-radius: 10px; padding: 1px 6px; font-size: 9px; font-weight: bold; }
        .productos-titulo { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: bold; margin-bottom: 10px; padding-bottom: 6px; border-bottom: 1px solid #f0f0f0; }
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

        <div class="seccion">
            <div class="seccion-titulo">🧾 Detalles de Transacción</div>
            <div class="fila">
                <div class="fila-label">Forma de pago:</div>
                <div class="fila-valor">{{ strtoupper($formaPago->forma_pago ?? 'N/A') }}</div>
            </div>
            <div class="fila">
                <div class="fila-label">Estado:</div>
                <div class="fila-valor">
                    @if($orden->estatus == '2')
                        <span style="display:inline-block; background:#f0fdf4; color:#16a34a; border:1px solid #bbf7d0; border-radius:20px; padding:2px 10px; font-size:11px; font-weight:bold;">
                            PAGADO
                        </span>
                    @else
                        <span class="badge-reservado">RESERVADO</span>
                    @endif
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

    {{-- TABLA DE PRODUCTOS --}}
    <div style="border:1px solid #e5e7eb; border-radius:6px; padding:16px; margin-bottom:20px;">
        <div class="productos-titulo">📦 Detalle de Productos</div>
        <table class="tabla-productos">
            <thead>
                <tr>
                    <th style="text-align:left;">Producto</th>
                    <th style="text-align:center;">Cant.</th>
                    <th style="text-align:right;">Precio base</th>
                    <th style="text-align:center;">IVA</th>
                    <th style="text-align:right;">Valor IVA</th>
                    <th style="text-align:right;">Total línea</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td style="text-align:left;">{{ $item['nombre'] }}</td>
                    <td style="text-align:center;">{{ $item['cantidad'] }}</td>
                    <td style="text-align:right;">${{ $item['precio_base'] }}</td>
                    <td style="text-align:center;">
                        @if($item['iva'] === 'S')
                            <span class="badge-iva">IVA</span>
                        @else
                            <span style="color:#ccc; font-size:11px;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        @if($item['iva_linea'] !== null)
                            ${{ $item['iva_linea'] }}
                        @else
                            <span style="color:#ccc; font-size:11px;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right; font-weight:bold;">
                        ${{ $item['total_linea'] }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                {{-- ESCENARIO S: precio sin IVA, se suma encima --}}
                @if($trabajaConIvaIncluido === 'S')
                <tr>
                    <td colspan="5" style="text-align:right; color:#888; font-size:12px;">
                        Subtotal:
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:bold;">
                        ${{ $subtotal }}
                    </td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right; color:#888; font-size:12px;">
                        IVA {{ $porcentajeIva }}%:
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:bold;">
                        ${{ $ivaTotal }}
                    </td>
                </tr>
                <tr style="border-top:2px solid #ddd;">
                    <td colspan="5" style="text-align:right; color:#888; font-size:12px; font-weight:bold; padding-top:8px;">
                        Total:
                    </td>
                    <td style="text-align:right; font-size:16px; font-weight:bold; color:#0300a3; padding-top:8px;">
                        ${{ number_format($orden->gran_total, 2) }}
                    </td>
                </tr>

                {{-- ESCENARIO N: precio incluye IVA, se desglosa --}}
                @else
                <tr>
                    <td colspan="5" style="text-align:right; color:#888; font-size:12px;">
                        Subtotal:
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:bold;">
                        ${{ $subtotal }}
                    </td>
                </tr>
                <tr>
                    <td colspan="5" style="text-align:right; color:#888; font-size:12px;">
                        IVA incluido {{ $porcentajeIva }}%:
                    </td>
                    <td style="text-align:right; font-size:12px; font-weight:bold;">
                        ${{ $ivaTotal }}
                    </td>
                </tr>
                <tr style="border-top:2px solid #ddd;">
                    <td colspan="5" style="text-align:right; color:#888; font-size:12px; font-weight:bold; padding-top:8px;">
                        Total bruto:
                    </td>
                    <td style="text-align:right; font-size:16px; font-weight:bold; color:#0300a3; padding-top:8px;">
                        ${{ $totalBruto }}
                    </td>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>

    {{-- IMPORTANTE --}}
    <div class="importante">
        <div class="importante-titulo">Importante</div>
        <p>
            Su pedido ha sido procesado correctamente. Conserve esta factura como comprobante oficial
        </p>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Documento generado el {{ now()->format('d/m/Y H:i') }} — {{ $orden->codigo }}
    </div>

</div>
</body>
</html>
