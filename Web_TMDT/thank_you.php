<?php
session_start();
include 'config.php';
include 'includes/header.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['khach_hang'])) {
    header("Location: login.php");
    exit;
}

// Lấy mã đơn hàng từ URL
$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;
if ($order_id <= 0) {
    header("Location: index.php");
    exit;
}

// Lấy thông tin đơn hàng của chính người dùng
$stmt = $conn->prepare("
    SELECT dh.*, kh.ho_ten 
    FROM don_hang dh 
    JOIN khach_hang kh ON dh.id_khach_hang = kh.id_khach_hang 
    WHERE dh.id_don_hang = ? AND dh.id_khach_hang = ? 
    LIMIT 1
");
$stmt->bind_param("ii", $order_id, $_SESSION['khach_hang']['id_khach_hang']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$don_hang = $result->fetch_assoc();
$ma_don_hang = str_pad($order_id, 6, '0', STR_PAD_LEFT);
$stmt->close();
?>

<div class="min-vh-100 d-flex align-items-center" style="background: linear-gradient(135deg, #f0f7ff 0%, #e6f7f0 100%);">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">

                <!-- Icon thành công + Tiêu đề -->
                <div class="text-center mb-5">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success text-white mb-4" 
                         style="width: 120px; height: 120px; font-size: 60px; box-shadow: 0 15px 35px rgba(40,167,69,0.3);">
                        <i class="fas fa-check"></i>
                    </div>
                    <h1 class="display-4 fw-bold text-success mb-3">ĐẶT HÀNG THÀNH CÔNG!</h1>
                    <p class="lead text-dark">
                        Cảm ơn <strong class="text-success"><?= htmlspecialchars($don_hang['ho_ten']) ?></strong> đã tin tưởng mua sắm tại <strong>BlankLabel</strong>
                    </p>
                </div>

                <!-- Card chính -->
                <div class="card border-0 shadow-xl rounded-4 overflow-hidden" style="box-shadow: 0 25px 60px rgba(0,0,0,0.12);">
                    
                    <!-- Header xanh lá -->
                    <div class="card-header text-white py-5 position-relative overflow-hidden" 
                        style="background: linear-gradient(135deg, #28a745, #20c997);">

                        <!-- Icon xe chạy -->
                        <div class="position-absolute top-0 start-0 w-100 h-100">
                            <i class="fas fa-shipping-fast position-absolute shipping-bg-icon"></i>
                        </div>

                        <!-- Tiêu đề căn phải -->
                        <h2 class="mb-0 position-relative text-end pe-4 order-title">
                            <i class="fas fa-receipt me-3"></i>
                            ĐƠN HÀNG #<?= $ma_don_hang ?>
                        </h2>
                    </div>

                    <div class="card-body p-5 p-lg-6">

                        <!-- Thông tin tổng quan -->
                        <div class="row g-5 mb-5">
                            <div class="col-md-6 text-center text-md-start">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="bg-success bg-opacity-10 rounded-circle p-3 me-4">
                                        <i class="fas fa-money-bill-wave-alt text-success fs-3"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-1 fw-semibold">Tổng thanh toán</p>
                                        <h3 class="text-danger fw-bold mb-0"><?= number_format($don_hang['tong_tien']) ?>₫</h3>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 text-center text-md-start">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-4">
                                        <i class="fas fa-truck text-primary fs-3"></i>
                                    </div>
                                    <div>
                                        <p class="text-muted mb-1 fw-semibold">Phương thức thanh toán</p>
                                        <h5 class="fw-bold text-dark mb-0">
                                            <?= $don_hang['phuong_thuc_thanh_toan'] === 'cod' 
                                                ? '<span class="text-warning">Thanh toán khi nhận hàng (COD)</span>' 
                                                : 'Chuyển khoản ngân hàng' ?>
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5 border-secondary-subtle">

                        <!-- Box thông báo -->
                        <div class="bg-gradient rounded-4 p-5 text-center mb-5" 
                            style="background: linear-gradient(135deg, #f8f9ff 0%, #f0fff4 100%); border: 1px solid #d1fae5;">
                            <i class="fas fa-bell text-primary fs-1 mb-3"></i>
                            <h5 class="fw-bold text-success mb-3">Chúng tôi sẽ liên hệ xác nhận trong vòng 30 phút - 2 giờ</h5>
                            <p class="text-muted mb-0">
                                Thông tin đơn hàng đã được gửi về email và số điện thoại của bạn.<br>
                                Nếu quá thời gian trên chưa nhận được tin nhắn, vui lòng gọi hotline: 
                                <strong class="text-danger">1900 6750</strong>
                            </p>
                        </div>

                        <!-- Nút hành động -->
                        <div class="text-center">
                            <a href="index.php" class="btn btn-success btn-lg px-5 py-4 rounded-pill shadow-lg me-3 mb-3">
                                <i class="fas fa-shopping-bag me-2"></i>
                                <strong>TIẾP TỤC MUA SẮM</strong>
                            </a>
                            <a href="orders.php" class="btn btn-outline-dark btn-lg px-5 py-4 rounded-pill border-2 mb-3">
                                <i class="fas fa-list-alt me-2"></i>
                                <strong>XEM ĐƠN HÀNG CỦA TÔI</strong>
                            </a>
                        </div>

                        <!-- Footer -->
                        <div class="text-center mt-5 pt-5 border-top border-light">
                            <p class="text-muted mb-2">
                                Cảm ơn bạn đã ủng hộ <strong>BlankLabel</strong> 
                                <i class="fas fa-heart text-danger ms-2"></i>
                            </p>
                            <small class="text-muted">
                                © 2025 BlankLabel - Thương hiệu thời trang cao cấp
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- CSS -->
<style>
    .bg-gradient { background: linear-gradient(135deg, #f8f9ff 0%, #f0fff4 100%); }
    .shadow-xl { box-shadow: 0 25px 60px rgba(0,0,0,0.12) !important; }

    /* Animation xe chạy */
    @keyframes drive {
        0%   { transform: translateX(0px); }
        50%  { transform: translateX(40px); }
        100% { transform: translateX(0px); }
    }

    /* Icon xe màu trắng + chạy */
    .shipping-bg-icon {
        font-size: 300px;
        top: -50px;
        left: -40px;
        color: #ffffff !important;
        opacity: 1 !important;
        animation: drive 5s ease-in-out infinite;
        filter: none !important;
    }

    /* Tiêu đề nghiêng */
    .order-title {
        font-style: italic;
        transform: skewX(-10deg);
        letter-spacing: 1px;
        font-weight: 700;
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745, #20c997) !important;
        border: none !important;
        transition: all 0.3s ease;
    }
    .btn-success:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 15px 30px rgba(40,167,69,0.4) !important;
    }
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<?php include 'includes/footer.php'; ?>
