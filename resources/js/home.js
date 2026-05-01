fetch('http://127.0.0.1:8000/home')
.then(res => res.json())
.then(data => {
    function goDetail(id) {
        window.location.href = `/product.html?id=${id}`;
    }

    // ❤️ wishlist
    function addWishlist(producto) {

        let token = localStorage.getItem('token');

        fetch('http://127.0.0.1:8000/wishlist/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({ producto })
        })
        .then(res => res.json())
        .then(data => {
            alert('Agregado a favoritos ❤️');
        });
    }
    // CARRUSEL
    let carousel = document.getElementById('carousel');
    data.banners.forEach(b => {
        carousel.innerHTML += `
            <img src="/images/${b.imagen}">
        `;
    });

    // PRODUCTOS
    let products = document.getElementById('products');

    data.productos_populares.forEach(p => {

        let precio = p.precio_promocion
            ? `<span class="old-price">$${p.precio}</span> <span class="price">$${p.precio_promocion}</span>`
            : `<span class="price">$${p.precio}</span>`;

        products.innerHTML += `
            <div class="card">
                <span class="heart" onclick="addWishlist(${p.codigo})">❤️</span>

                <img src="/images/${p.foto}" onclick="goDetail(${p.codigo})">

                <h4>${p.nombre}</h4>

                <p>${precio}</p>

                <button onclick="goDetail(${p.codigo})">Ver</button>
            </div>
        `;
    });

});
