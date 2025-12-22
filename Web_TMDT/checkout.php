<?php
include 'config.php';
include 'includes/header.php';

// Kiểm tra đăng nhập (bắt buộc để thanh toán)
if (!isset($_SESSION['khach_hang'])) {
    header("Location: login.php?return=checkout.php");
    exit;
}

$kh = $_SESSION['khach_hang'];
$cart_count = 0;
$cart_items = [];
$total_price = 0;

if (isset($_SESSION['cart']) && count($_SESSION['cart']) > 0) {
    foreach ($_SESSION['cart'] as $id => $item) {
        $id_sp = $item['id'];
        $qty = $item['quantity'];
        $sql = "SELECT id_san_pham, ten_san_pham, gia, link_anh FROM san_pham WHERE id_san_pham = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_sp);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $row['quantity'] = $qty;
            $row['subtotal'] = $row['gia'] * $qty;
            $cart_items[] = $row;
            $total_price += $row['subtotal'];
            $cart_count += $qty;
        }
    }
}

// Nếu giỏ trống → quay về
if (empty($cart_items)) {
    header("Location: cart.php");
    exit;
}
?>

<div class="container py-5">
    <div class="row g-5">
        <!-- CỘT TRÁI: Form thông tin giao hàng -->
        <div class="col-lg-8">
            <div class="bg-white rounded-4 shadow-sm p-4 p-md-5">
                <h4 class="fw-bold text-success mb-4">
                    Thông tin giao hàng
                </h4>

                <form method="POST" action="process_checkout.php" id="checkoutForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Họ và tên</label>
                            <input type="text" name="ho_ten" class="form-control form-control-lg" 
                                   value="<?= htmlspecialchars($kh['ho_ten']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại</label>
                            <input type="text" name="dien_thoai" class="form-control form-control-lg" 
                                   value="<?= htmlspecialchars($kh['dien_thoai']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg" 
                                   value="<?= htmlspecialchars($kh['email']) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Địa chỉ nhận hàng</label>
                            <input type="text" name="dia_chi" class="form-control form-control-lg" 
                                   placeholder="Ví dụ: 123 Đường Láng, Đống Đa" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tỉnh/Thành phố</label>
                            <select name="tinh_thanh" class="form-select form-select-lg" required>
                                <option value="">Chọn tỉnh/thành</option>
                                <option value="Hà Nội" selected>Hà Nội</option>
                                <option value="TP.HCM">TP.HCM</option>
                                <!-- Thêm các tỉnh khác nếu cần -->
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Quận/Huyện</label>
                            <input type="text" name="quan_huyen" class="form-control form-control-lg" 
                                   placeholder="Ví dụ: Đống Đa" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Phường/Xã</label>
                            <input type="text" name="phuong_xa" class="form-control form-control-lg" 
                                   placeholder="Ví dụ: Láng Thượng" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Ghi chú (tùy chọn)</label>
                            <textarea name="ghi_chu" rows="3" class="form-control" 
                                      placeholder="Ví dụ: Giao giờ hành chính, để trước cửa..."></textarea>
                        </div>

                        <div class="col-12 mt-4">
                            <h5 class="fw-bold">Phương thức thanh toán</h5>
                            <div class="border rounded-3 p-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="phuong_thuc" value="cod" id="cod" checked>
                                    <label class="form-check-label fw-semibold" for="cod">
                                        Thanh toán khi nhận hàng (COD)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="phuong_thuc" value="bank" id="bank">
                                    <label class="form-check-label fw-semibold" for="bank">
                                        Chuyển khoản ngân hàng
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- CỘT PHẢI: Tóm tắt đơn hàng -->
        <div class="col-lg-4">
            <div class="bg-white rounded-4 shadow-sm p-4 sticky-top" style="top: 20px;">
                <h4 class="fw-bold mb-4">Đơn hàng của bạn</h4>

                <div class="border-bottom pb-3 mb-3">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex mb-3">
                        <img src="assets/images/<?= htmlspecialchars($item['link_anh']) ?>" 
                             class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <p class="mb-1 fw-semibold small"><?= htmlspecialchars($item['ten_san_pham']) ?></p>
                            <small class="text-muted">x<?= $item['quantity'] ?></small>
                        </div>
                        <strong class="text-danger"><?= number_format($item['subtotal']) ?>₫</strong>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính</span>
                        <strong><?= number_format($total_price) ?>₫</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Phí vận chuyển</span>
                        <strong class="text-success">Miễn phí</strong>
                    </div>
                    <div class="d-flex justify-content-between fw-bold fs-5 text-danger border-top pt-3">
                        <span>Tổng cộng</span>
                        <span><?= number_format($total_price) ?>₫</span>
                    </div>
                </div>

                <button type="submit" form="checkoutForm" 
                        class="btn btn-success btn-lg w-100 mt-4 fw-bold rounded-pill shadow">
                    HOÀN TẤT ĐẶT HÀNG
                </button>

                <div class="text-center mt-3">
                    <small class="text-muted">
                        Bằng việc đặt hàng, bạn đồng ý với <a href="#" class="text-decoration-underline">Điều khoản dịch vụ</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CSS ĐẸP CHO CHECKOUT -->
<style>
    body { background: #f8f9fa; }
    .form-control, .form-select {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 12px 16px;
    }
    .form-control:focus, .form-select:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
    .btn-success {
        background: linear-gradient(135deg, #28a745, #20c997);
        border: none;
        padding: 14px;
    }
    .btn-success:hover {
        background: linear-gradient(135deg, #218838, #1baa8e);
        transform: translateY(-2px);
    }
    @media (max-width: 768px) {
        #addToCartSuccess { bottom: 10px; right: 10px; left: 10px; max-width: none; }
    }
</style>

<?php include 'includes/footer.php'; ?>