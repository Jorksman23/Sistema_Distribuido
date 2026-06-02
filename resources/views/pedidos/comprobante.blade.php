<h1>Subir comprobante</h1>

<p>
    Estado:
    Pendiente de Pago
</p>

@if($formaPago)
    <p>
        Forma de pago:
        {{ $formaPago->forma_pago }}
    </p>
@endif

@if($cuentaBanco)

    <p>
        Banco:
        {{ $cuentaBanco->descripcion }}
    </p>

    <p>
        Cuenta:
        {{ $cuentaBanco->cuenta }}
    </p>

    <p>
        Tipo:
        {{ $cuentaBanco->tipo == 'C'
            ? 'Cuenta Corriente'
            : 'Cuenta Ahorros'
        }}
    </p>

@endif
<form method="POST"
      action="{{ route('pedidos.comprobante.guardar') }}"
      enctype="multipart/form-data">

    @csrf

    <input type="file" name="comprobante" required>

    <button type="submit">
        Enviar comprobante
    </button>

</form>
