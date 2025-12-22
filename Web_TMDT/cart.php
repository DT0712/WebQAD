<?php
include 'config.php';
include 'includes/header.php';

// === TÍNH SỐ LƯỢNG TRONG GIỎ HÀNG ===
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'] ?? 0;
    }
}

// === XỬ LÝ THÊM SẢN PHẨM ===
$success_message = '';
$product_name = '';

if (isset($_GET['action']) && $_GET['action'] === 'add' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $qty = isset($_GET['qty']) ? max(1, intval($_GET['qty'])) : 1;

    $sql = "SELECT ten_san_pham, gia, link_anh FROM san_pham WHERE id_san_pham = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity'] += $qty;
        } else {
            $_SESSION['cart'][$id] = [
                'id' => $id,
                'name' => $product['ten_san_pham'],
                'price' => $product['gia'],
                'image' => $product['link_anh'],
                'quantity' => $qty
            ];
        }

        $product_name = $product['ten_san_pham'];
        $success_message = "Đã thêm <strong>\"$product_name\"</strong> vào giỏ hàng!";
    }
    $stmt->close();
}

// === CẬP NHẬT & XÓA GIỎ HÀNG ===
if (isset($_POST['update_cart']) && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $qty = intval($qty);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
}
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][intval($_GET['remove'])]);
}
?>

<?php if ($success_message): ?>
<!-- POPUP ĐƠN GIẢN - SANG TRỌNG - SIÊU ĐẸP -->
<div id="addToCartSuccess" class="position-fixed top-50 start-50 translate-middle" style="z-index: 9999;">
    <div class="bg-white rounded-3 shadow-lg border-0 text-center" style="width: 400px; max-width: 92vw; animation: fadeInUp 0.35s ease-out;">
        <div class="py-4">
            <!-- Icon check lớn, màu xanh lá nhạt sang trọng -->
            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem; opacity: 0.9;"></i>
        </div>

        <div class="px-4 pb-4">
            <h5 class="fw-bold text-dark mb-2">Thêm vào giỏ hàng thành công!</h5>
            <p class="text-muted small mb-4"><?php echo $success_message; ?></p>

            <!-- 2 NÚT NẰM NGANG TRÁI - PHẢI, ĐƠN GIẢN & ĐẸP -->
            <div class="d-flex gap-3">
                <button type="button" 
                        onclick="closeCartPopup()" 
                        class="btn btn-lg btn-outline-secondary flex-fill rounded-pill fw-medium">
                    Tiếp tục mua sắm
                </button>
                <a href="cart.php" 
                   class="btn btn-lg btn-success flex-fill rounded-pill fw-medium position-relative">
                    <i class="bi bi-cart3 me-1"></i>
                    Xem giỏ hàng
                    <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                            <?php echo $cart_count; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Nền mờ nhẹ nhàng -->
<div id="cartPopupBackdrop" class="position-fixed top-0 start-0 w-100 h-100" 
     style="background: rgba(0,0,0,0.45); backdrop-filter: blur(6px); z-index: 9998;"></div>

<!-- Hiệu ứng + đóng mượt -->
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    #addToCartSuccess .btn {
        min-height: 48px;
        font-size: 1rem;
        transition: all 0.25s ease;
    }
    #addToCartSuccess .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }
</style>

<script>
function closeCartPopup() {
    const popup = document.getElementById('addToCartSuccess');
    const backdrop = document.getElementById('cartPopupBackdrop');
    if (popup) {
        popup.style.animation = 'fadeInUp 0.25s ease-in reverse';
        setTimeout(() => {
            popup.remove();
            backdrop?.remove();
        }, 230);
    }
}

// Tự động đóng sau 10 giây
setTimeout(closeCartPopup, 10000);
</script>
<?php endif; ?>

<!-- NỘI DUNG GIỎ HÀNG -->
<div class="container-limit my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang chủ</a></li>
            <li class="breadcrumb-item active">Giỏ hàng</li>
        </ol>
    </nav>

    <h3 class="mb-4 text-center text-uppercase fw-bold">Giỏ hàng của bạn</h3>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="text-center py-5">
            <img src="assets/images/empty-cart.png" alt="Giỏ hàng trống" style="max-width: 200px;">
            <p class="text-muted fs-4">Giỏ hàng trống</p>
            <a href="index.php" class="btn btn-primary btn-lg">Tiếp tục mua sắm</a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                            <th>Xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; ?>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <?php $subtotal = $item['price'] * $item['quantity']; $total += $subtotal; ?>
                            <tr>
                                <td><img src="assets/images/<?php echo htmlspecialchars($item['image']); ?>" style="width:80px; height:80px; object-fit:cover;"></td>
                                <td class="text-start fw-bold"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo number_format($item['price']); ?>₫</td>
                                <td>
                                    <input type="number" name="quantity[<?php echo $item['id']; ?>]" value="<?php echo $item['quantity']; ?>" min="1" class="form-control w-75 mx-auto">
                                </td>
                                <td class="fw-bold text-danger"><?php echo number_format($subtotal); ?>₫</td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Xóa sản phẩm này?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end mt-4">
                <div class="col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="fs-5 fw-bold">Tổng tiền:</span>
                                <span class="fs-4 fw-bold text-danger"><?php echo number_format($total); ?>₫</span>
                            </div>
                            <button type="submit" name="update_cart" class="btn btn-outline-secondary w-100 mb-3">
                                Cập nhật giỏ hàng
                            </button>
                            <a href="checkout.php" class="btn btn-success w-100 btn-lg">Thanh toán</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-primary">← Tiếp tục mua sắm</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>