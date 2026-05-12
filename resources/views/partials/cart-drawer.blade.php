<div id="cartDrawer"
    class="fixed top-0 right-0 w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 z-50">

    <div class="flex justify-between items-center p-4 border-b">
        <h2 class="text-lg font-bold">Tu carrito</h2>
        <button onclick="toggleCart()" class="text-xl">&times;</button>
    </div>

    <div class="flex flex-col items-center justify-center h-full text-gray-500">

        @if(session('user_id'))
            <div id="cartContent" class="text-center">
                <span class="text-4xl">🛒</span>
                <p class="mt-4">Tu carrito está vacío</p>
            </div>

            <button id="checkoutBtn"
                    class="hidden mt-4 bg-orange-500 text-white px-5 py-2 rounded">
                Proceder al pago
            </button>
        @else
            <span class="text-4xl">🔒</span>
            <p class="mt-4">Debes iniciar sesión para usar el carrito</p>
            <a href="{{ route('login') }}" class="mt-4 bg-blue-600 text-white px-5 py-2 rounded">
                Iniciar sesión
            </a>
        @endif

    </div>
</div>

<script>
function toggleCart() {
    document.getElementById('cartDrawer').classList.toggle('translate-x-full');
    renderCart();
}

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
    const cart  = getCart();
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
    const cart        = getCart();
    const container   = document.getElementById('cartContent');
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
