/**
 * Alterna el estado de favorito (agregar/quitar) de un producto vía AJAX,
 * sin recargar la página. Cambia el ícono entre relleno y outline.
 * Usado en catálogo y sección de productos relacionados.
 */
async function toggleWishlist(event, form) {
    event.preventDefault();

    const btn = form.querySelector('button[type="submit"]');
    if (!btn || btn.disabled) return;

    btn.disabled = true;

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
            const iconFilled  = btn.querySelector('.icon-filled');
            const iconOutline = btn.querySelector('.icon-outline');

            if (iconFilled && iconOutline) {
                iconFilled.classList.toggle('hidden', !data.en_wishlist);
                iconOutline.classList.toggle('hidden', data.en_wishlist);
            }

            const wishBadge = document.getElementById('wishCountBadge');
            if (wishBadge && data.wish_count !== undefined) {
                wishBadge.textContent = data.wish_count;
                wishBadge.classList.toggle('hidden', data.wish_count <= 0);
            }
        }
    } catch (error) {
        console.error('Error al actualizar wishlist:', error);
    } finally {
        btn.disabled = false;
    }
}

/**
 * Elimina un producto de la wishlist sin recargar la página.
 * Oculta el card completo del producto, y si era el último,
 * muestra el estado vacío (#wishlistEmptyState) y oculta el grid (#wishlistGrid).
 * Usado en la página Mi Lista de Deseos.
 */
async function eliminarDeWishlist(event, form) {
    event.preventDefault();

    const btn = form.querySelector('button[type="submit"]');
    if (!btn || btn.disabled) return;

    btn.disabled = true;

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
            const card = form.closest('.group');
            if (card) {
                card.style.transition = 'opacity 0.3s ease';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    // Si ya no quedan productos, mostrar el estado vacío
                    if (data.wish_count <= 0) {
                        const grid       = document.getElementById('wishlistGrid');
                        const emptyState = document.getElementById('wishlistEmptyState');
                        if (grid) grid.classList.add('hidden');
                        if (emptyState) emptyState.classList.remove('hidden');
                    }
                }, 300);
            }

            const wishBadge = document.getElementById('wishCountBadge');
            if (wishBadge && data.wish_count !== undefined) {
                wishBadge.textContent = data.wish_count;
                wishBadge.classList.toggle('hidden', data.wish_count <= 0);
            }

            const headerCount = document.querySelector('.bg-red-100.text-red-600');
            if (headerCount && data.wish_count !== undefined) {
                headerCount.textContent = `${data.wish_count} ${data.wish_count === 1 ? 'producto' : 'productos'}`;
            }
        }
    } catch (error) {
        console.error('Error al eliminar de wishlist:', error);
    } finally {
        btn.disabled = false;
    }
}
