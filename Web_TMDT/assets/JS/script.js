function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Đã thêm sản phẩm vào giỏ hàng!');
            document.querySelector('.cart-icon').innerText = `🛒 Giỏ hàng (${data.cart_count})`;
        } else {
            alert('Lỗi khi thêm sản phẩm!');
        }
    });
}