// Mensaje de aviso al pagar con tarjeta de crédito (Nuvei).
// Antes de enviar el formulario a Nuvei, avisa al cliente que deberá subir
// su comprobante de pago después. Solo aplica cuando elige "Tarjetas de crédito".

document.addEventListener('DOMContentLoaded', function () {
    const form     = document.getElementById('payment-form');
    const tipoPago = document.getElementById('tipo_pago');

    // Si no estamos en la página de pago, no hacemos nada.
    if (!form || !tipoPago) return;

    form.addEventListener('submit', function (e) {
        const opcionSeleccionada = tipoPago.options[tipoPago.selectedIndex];
        const esTarjeta = opcionSeleccionada && opcionSeleccionada.dataset.tarjeta === '1';

        // Si NO es tarjeta, dejamos que el formulario se envíe normal.
        if (!esTarjeta) return;

        // Si es tarjeta, interceptamos para mostrar el aviso primero.
        e.preventDefault();

        Swal.fire({
            title: 'Pago con tarjeta de crédito',
            html: 'Serás redirigido a la pasarela de pago segura.<br><br>' +
                  '<strong>Importante:</strong> Después de pagar, deberás subir una ' +
                  '<strong>captura de tu comprobante de pago</strong> para validar tu pedido.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#0F52BA',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Entendido, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // El cliente aceptó: enviamos el formulario (irá a Nuvei).
                form.submit();
            }
            // Si cancela, no hacemos nada: se queda en el checkout.
        });
    });
});
