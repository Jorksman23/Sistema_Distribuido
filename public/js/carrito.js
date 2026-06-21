/**
 * Intercepta el submit de un formulario "+ Carrito" para enviarlo vía AJAX,
 * evitando recarga de página y bloqueando el botón mientras se procesa
 * (previene que clics múltiples generen incrementos duplicados de cantidad).
 */
async function agregarAlCarrito(event, form) {
    event.preventDefault();

    const btn = form.querySelector('button[type="submit"], button[type="button"]');
    if (!btn || btn.disabled) return;

    const textoOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span>Agregando...</span>';

    try {
        const formData = new FormData(form);

        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Actualizar contador del carrito en el navbar
            const badge = document.getElementById('carritoCountBadge');
            if (badge && data.carrito_count !== undefined) {
                badge.textContent = data.carrito_count;
                badge.classList.toggle('hidden', data.carrito_count <= 0);
            }

            Swal.fire({
                title: data.message || '¡Producto agregado al carrito!',
                icon: 'success',
                draggable: true,
                timer: 1800,
                showConfirmButton: false,
            });
        } else {
            Swal.fire({
                title: 'No se pudo agregar',
                text: data.message || 'Ocurrió un error inesperado.',
                icon: 'error',
                confirmButtonColor: '#0300a3',
            });
        }
    } catch (error) {
        console.error('Error al agregar al carrito:', error);
        Swal.fire({
            title: 'Error de conexión',
            text: 'Hubo un problema al procesar tu solicitud.',
            icon: 'error',
            confirmButtonColor: '#0300a3',
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = textoOriginal;
    }
}
