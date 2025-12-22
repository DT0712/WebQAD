<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

// Đếm số lượng trong giỏ hàng
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += (int)($item['quantity'] ?? 0);
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blank Label</title>

    <!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/Css/style.css">
    <link rel="stylesheet" href="assets/Css/header.css">
</head>
<body>

    <!-- TOP HEADER -->
    <div class="top-header">
        <div class="container-limit d-flex justify-content-between align-items-center">
            <div class="logo py-2">Blank<span>Label</span></div>

            <div class="top-menu">
                <div><i class="bi bi-telephone"></i> 1900 6750</div>

                <form class="d-flex" method="GET" action="search.php">
                    <input class="form-control" type="text" id="searchInput" name="q" placeholder="Tìm kiếm..." autocomplete="off">
                    <div class="search-dropdown" id="searchDropdown"></div>
                </form>

                <!-- PHẦN ĐĂNG NHẬP / TÊN NGƯỜI DÙNG – ĐÃ BỎ HOÀN TOÀN MŨI TÊN -->
                <?php if (isset($_SESSION['khach_hang']) && !empty($_SESSION['khach_hang']['ho_ten'])):
                    $ten_kh = htmlspecialchars($_SESSION['khach_hang']['ho_ten']);
                ?>
                    <div class="user-dropdown dropdown">
                        <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if (!empty($_SESSION['khach_hang']['anh_dai_dien'])): ?>
                                <img src="<?= htmlspecialchars($_SESSION['khach_hang']['anh_dai_dien']) ?>" alt="Avatar" class="me-2" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-person-circle me-2"></i>
                            <?php endif; ?>
                            <span class="d-none d-md-inline"><?= $ten_kh ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Tài khoản</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php"><i class="bi bi-person"></i> Đăng nhập</a>
                    <a href="register.php"><i class="bi bi-lock"></i> Đăng ký</a>
                <?php endif; ?>

                <!-- Giỏ hàng -->
                <a href="cart.php" class="cart-box">
                    <i class="bi bi-cart"></i> Giỏ hàng
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN MENU -->
    <div class="main-nav">
        <div class="container-limit">
            <a href="index.php" class="home-link">TRANG CHỦ</a>

            <div class="menu-items">
                <a href="about.php">GIỚI THIỆU</a>

                <?php
                $queryCha = "SELECT * FROM danh_muc WHERE id_cha IS NULL ORDER BY ten_danh_muc";
                $resultCha = mysqli_query($conn, $queryCha);
                if (mysqli_num_rows($resultCha) > 0):
                ?>
                <div class="dropdown mega-menu" id="megaMenu">
                    <a href="#" class="dropdown-toggle text-white">SẢN PHẨM</a>
                    <div class="dropdown-menu mega-dropdown">
                        <?php while ($cha = mysqli_fetch_assoc($resultCha)): ?>
                            <div class="mega-column">
                                <h6><?= htmlspecialchars($cha['ten_danh_muc']) ?></h6>
                                <?php
                                $queryCon = "SELECT * FROM danh_muc WHERE id_cha = " . intval($cha['id']);
                                $resultCon = mysqli_query($conn, $queryCon);
                                ?>
                                <ul class="list-unstyled">
                                    <?php while ($con = mysqli_fetch_assoc($resultCon)): ?>
                                        <li><a href="index.php?id=<?= $con['id'] ?>"><?= htmlspecialchars($con['ten_danh_muc']) ?></a></li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>

                <a href="contact.php">LIÊN HỆ</a>
                <?php if (isset($_SESSION['khach_hang'])): ?>
                    <a href="thongke.php" class="text-white fw-bold"><i class="bi bi-graph-up me-2"></i>BẢNG THỐNG KÊ</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/JS/header.js"></script>
</body>
</html>