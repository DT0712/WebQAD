<?php
// Kiểm tra nếu session chưa bắt đầu thì mới start (tránh lỗi start 2 lần)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối CSDL (Lùi ra 1 cấp thư mục để tìm file config.php)
include '../Web_TMDT/config.php';

// --- BẢO VỆ TRANG ADMIN ---
// Nếu KHÔNG tìm thấy session 'admin' (chưa đăng nhập) -> Đá về trang admin_login.php
if (!isset($_SESSION['admin'])) {
    header('Location: admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - BlankLabel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root { --primary-color: #00bcd4; --dark-bg: #222; }
        body { background-color: #f4f6f9; }
        
        /* Sidebar */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0; left: 0;
            background-color: var(--dark-bg);
            color: #fff;
            padding-top: 20px;
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar-header { font-size: 20px; font-weight: bold; text-align: center; margin-bottom: 30px; }
        .sidebar-header span { color: var(--primary-color); }
        
        .nav-link { color: #bbb; padding: 12px 20px; font-size: 15px; display: block; text-decoration: none; }
        .nav-link:hover, .nav-link.active { background-color: var(--primary-color); color: #fff; }
        .nav-link i { margin-right: 10px; width: 20px; text-align: center; }

        /* Main Content Wrapper */
        .main-content { margin-left: 250px; padding: 20px; }
        
        /* Card Thống kê */
        .stat-card { border: none; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { font-size: 30px; opacity: 0.3; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">Blank<span>Label</span> Admin</div>
    <nav class="nav flex-column">
        <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF'])=='index.php'?'active':''; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="admin_products.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF'])=='admin_products.php' || basename($_SERVER['PHP_SELF'])=='product_add.php' || basename($_SERVER['PHP_SELF'])=='product_edit.php')?'active':''; ?>">
            <i class="bi bi-box-seam"></i> Quản lý Sản phẩm
        </a>

        <a href="admin_categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_categories.php' ? 'active' : ''; ?>">
            <i class="bi bi-tags"></i> Quản lý Danh mục
        </a>

        <a href="admin_users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> Quản lý Người dùng
        </a>

        <a href="admin_contacts.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF'])=='admin_contacts.php' || basename($_SERVER['PHP_SELF'])=='admin_contact_detail.php')?'active':''; ?>">
            <i class="bi bi-chat-dots"></i> Liên hệ Khách hàng
        </a>

        <a href="admin_orders.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF'])=='admin_orders.php' || basename($_SERVER['PHP_SELF'])=='admin_order_detail.php')?'active':''; ?>">
            <i class="bi bi-cart-check"></i> Đơn hàng
        </a>

        <div class="border-top my-3 border-secondary"></div>
        
        <a href="../Web_TMDT/index.php" target="_blank" class="nav-link text-warning"><i class="bi bi-globe"></i> Xem Website</a>
        
        <a href="admin_logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
    </nav>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
        <h4 class="m-0">Quản trị hệ thống</h4>
        <div class="d-flex align-items-center">
            <span class="me-2">Xin chào, <strong><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></strong></span>
            <img src="../Web_TMDT/assets/images/admin_avatar.jpg" class="rounded-circle" width="40" height="40" style="object-fit: cover; border: 2px solid #00bcd4;" alt="Admin">
        </div>
    </div>