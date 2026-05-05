<script>
function getCart() {
    try {
        return JSON.parse(localStorage.getItem('cart')) || [];
    } catch (e) {
        return [];
    }
}

function saveCart(cart) {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function addToCart(productId) {
    let cart = getCart();
    // Evitar duplicados
    if (!cart.includes(productId)) {
        cart.push(productId);
        saveCart(cart);
    }
    updateCartCount();
    renderCart();
}

function removeFromCart(productId) {
    let cart = getCart().filter(id => id !== productId);
    saveCart(cart);
    updateCartCount();
    renderCart();
}

function updateCartCount() {
    const cart = getCart();
    const badge = document.getElementById('carritoCount');
    if (!badge) return;

    if (cart.length > 0) {
        badge.classList.remove('hidden');
        badge.textContent = cart.length;
    } else {
        badge.classList.add('hidden');
    }
}

function renderCart() {
    const cart = getCart();
    const container = document.getElementById('cartContent');
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (!container || !checkoutBtn) return;

    if (cart.length === 0) {
        container.innerHTML = `
            <span class="text-4xl">🛒</span>
            <p class="mt-4">Tu carrito está vacío</p>
        `;
        checkoutBtn.classList.add('hidden');
    } else {
        container.innerHTML = cart.map(id => `
            <div class="flex justify-between items-center border-b py-2">
                <span>Producto ${id}</span>
                <button onclick="removeFromCart('${id}')"
                        class="text-red-500 hover:text-red-700">Eliminar</button>
            </div>
        `).join('');
        checkoutBtn.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    updateCartCount();
    renderCart();
});
</script>
