    //Si tiene una sola ubicación: agrega directo sin interrumpir al usuario.
    //Si tiene más de una: muestra modal SweetAlert2 para que el cliente elija.
async function agregarConUbicacion(btn, codigoProducto) {
    const form = btn.closest('form');
    const inputUbicacion = form.querySelector('.input-ubicacion');

    // Consultar ubicaciones del producto
    const response = await fetch(`/producto/ubicaciones/${codigoProducto}`);
    const ubicaciones = await response.json();

    if (ubicaciones.length <= 1) {
        // Una sola ubicación o ninguna — agregar directo sin mostrar modal
        if (ubicaciones.length === 1) {
            inputUbicacion.value = ubicaciones[0].ubicacion;
        }
        form.submit();
        return;
    }

    // Más de una ubicación — mostrar selector con SweetAlert2
    const opciones = {};
    ubicaciones.forEach(u => {
        opciones[u.ubicacion] = `${u.nombre_ubicacion} — ${Math.floor(u.stock)} unidades`;
    });

    const { value: ubicacionElegida } = await Swal.fire({
        title: 'Elige una ubicación',
        input: 'select',
        inputOptions: opciones,
        inputPlaceholder: 'Selecciona una ubicación',
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Agregar al carrito',
        confirmButtonColor: '#0300a3',
        inputValidator: (value) => {
            if (!value) return 'Debes elegir una ubicación';
        }
    });
    // Guardar ubicación elegida en el input hidden y enviar el formulario
    if (ubicacionElegida) {
        inputUbicacion.value = ubicacionElegida;
        form.submit();
    }
}
