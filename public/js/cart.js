document.addEventListener('DOMContentLoaded', function () {

    /**
     * Actualizar cantidad (+/-) vía AJAX.
     * Muestra spinner en el número mientras se procesa.
     * Reutiliza el SweetAlert2 de error cuando no hay stock.
     */
    async function actualizarCantidad(event, form) {
        event.preventDefault();

        const btn       = event.submitter;
        const container = form.querySelector('.cantidad-display');
        if (!container) return;

        const valorAnterior = parseInt(container.textContent.trim()) || 1;
        container.innerHTML = '<svg class="animate-spin w-4 h-4 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>';

        try {
            const formData = new FormData(form);
            formData.set('cantidad', btn.value);
            formData.append('_method', 'PUT');

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
                // Actualizar número
                container.textContent = data.cantidad;

                // Actualizar values de botones + y -
                const btns = form.querySelectorAll('button[name="cantidad"]');
                if (btns.length === 2) {
                    btns[0].value = Math.max(1, parseInt(data.cantidad) - 1);
                    btns[1].value = parseInt(data.cantidad) + 1;
                }

                // Actualizar subtotal de la fila
                const subtotalFila = form.querySelector('.subtotal-fila');
                if (subtotalFila) {
                    const precio = parseFloat(form.dataset.precio);
                    subtotalFila.textContent = '$' + (precio * data.cantidad).toFixed(2);
                }

                // Actualizar resumen
                actualizarResumen(data);

            } else {
                // Restaurar número y botones
                container.textContent = valorAnterior;
                const btns = form.querySelectorAll('button[name="cantidad"]');
                if (btns.length === 2) {
                    btns[0].value = Math.max(1, valorAnterior - 1);
                    btns[1].value = valorAnterior + 1;
                }

                // Reutilizar la misma alerta de error del catálogo
                Swal.fire({
                    title: data.message || 'No hay más stock disponible',
                    icon: 'error',
                    confirmButtonColor: '#0300a3',
                    draggable: true,
                });
            }
        } catch (error) {
            container.textContent = valorAnterior;
            console.error('Error al actualizar cantidad:', error);
        }
    }

    /**
     * Eliminar producto del carrito vía AJAX.
     * La fila desaparece con fundido suave.
     */
    async function eliminarProducto(event, form) {
        event.preventDefault();

        try {
            const formData = new FormData(form);
            formData.append('_method', 'DELETE');

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
                // Desvanecer y eliminar la fila
                const fila = form.closest('.item-carrito');
                if (fila) {
                    fila.style.transition = 'opacity 0.3s ease';
                    fila.style.opacity    = '0';
                    setTimeout(() => {
                        fila.remove();
                        // Si no quedan productos, mostrar estado vacío
                        const lista = document.getElementById('listaProductos');
                        if (lista && lista.querySelectorAll('.item-carrito').length === 0) {
                            document.getElementById('carritoContenido').classList.add('hidden');
                            document.getElementById('carritoVacio').classList.remove('hidden');
                        }
                    }, 300);
                }

                actualizarResumen(data);
            }
        } catch (error) {
            console.error('Error al eliminar producto:', error);
        }
    }

    /**
     * Vaciar todo el carrito vía AJAX.
     */
    async function vaciarCarrito(event, form) {
        event.preventDefault();

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
                document.getElementById('carritoContenido').classList.add('hidden');
                document.getElementById('carritoVacio').classList.remove('hidden');

                const badge = document.getElementById('carritoCountBadge');
                if (badge) {
                    badge.textContent = '0';
                    badge.classList.add('hidden');
                }
            }
        } catch (error) {
            console.error('Error al vaciar carrito:', error);
        }
    }

    /**
     * Actualiza el resumen del carrito (subtotal, IVA, total, badge navbar).
     */
    function actualizarResumen(data) {
        const subtotal = document.getElementById('resumen-subtotal');
        const iva      = document.getElementById('resumen-iva');
        const total    = document.getElementById('resumen-total');
        const badge    = document.getElementById('carritoCountBadge');
        const header   = document.getElementById('carritoHeaderCount');

        if (subtotal) subtotal.textContent = '$' + data.subtotal;
        if (iva)      iva.textContent      = '$' + data.iva;
        if (total)    total.textContent    = '$' + data.total;

        if (badge && data.carrito_count !== undefined) {
            badge.textContent = data.carrito_count;
            badge.classList.toggle('hidden', data.carrito_count <= 0);
        }

        if (header && data.carrito_count !== undefined) {
            if (data.carrito_count <= 0) {
                header.classList.add('hidden');
            } else {
                header.textContent = data.carrito_count + ' ' + (data.carrito_count === 1 ? 'producto' : 'productos');
                header.classList.remove('hidden');
            }
        }
    }
    // Exponer funciones globalmente para los onsubmit del HTML
    window.actualizarCantidad = actualizarCantidad;
    window.eliminarProducto   = eliminarProducto;
    window.vaciarCarrito      = vaciarCarrito;
});
