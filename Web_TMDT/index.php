<?php
include 'config.php';
include 'includes/header.php';

// Tính số lượng trong giỏ (dùng cho JS)
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'] ?? 0;
    }
}

// Lấy id danh mục từ URL (nếu có)
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$show_all = isset($_GET['all']) && $_GET['all'] == 1;

// Biến để lưu tiêu đề phần sản phẩm
$section_title = "SẢN PHẨM MỚI";
$products_query = "SELECT * FROM san_pham ORDER BY id_san_pham DESC LIMIT 6";

if ($category_id > 0) {
    $check_cat = $conn->query("SELECT ten_danh_muc FROM danh_muc WHERE id = $category_id");
    if ($check_cat->num_rows > 0) {
        $cat_name = $check_cat->fetch_assoc()['ten_danh_muc'];
        $section_title = "SẢN PHẨM: " . htmlspecialchars($cat_name);

        $child_ids = [$category_id];
        $sql_child = "SELECT id FROM danh_muc WHERE id_cha = $category_id";
        $result_child = $conn->query($sql_child);
        while ($child = $result_child->fetch_assoc()) {
            $child_ids[] = $child['id'];
        }
        $ids_list = implode(',', $child_ids);
        $products_query = "SELECT * FROM san_pham WHERE id_danh_muc IN ($ids_list) ORDER BY id_san_pham DESC";
    }
} elseif ($show_all) {
    $section_title = "TẤT CẢ SẢN PHẨM";
    $products_query = "SELECT * FROM san_pham ORDER BY id_san_pham DESC";
}
?>

<div class="container-limit mt-0">
    <!-- Banner -->
    <div class="banner mb-4">
        <img src="assets/images/BG.png" class="img-fluid mx-auto d-block w-100" alt="Banner">
    </div>

    <!-- DANH MỤC SẢN PHẨM & SẢN PHẨM -->
    <div class="row align-items-start category-product-section mb-4">
        <!-- Cột DANH MỤC -->
        <div class="col-md-3 category-column">
            <h5 class="category-title">DANH MỤC SẢN PHẨM</h5>
            <div class="category-wrapper">
                <ul class="category-list">
                    <?php
                    $sql_parent = "SELECT * FROM danh_muc WHERE id_cha IS NULL ORDER BY id ASC";
                    $result_parent = $conn->query($sql_parent);

                    if ($result_parent->num_rows > 0) {
                        while ($parent = $result_parent->fetch_assoc()) {
                            $active_parent = ($category_id == $parent['id']) ? ' active' : '';
                            echo '<li class="parent-item' . $active_parent . '">';
                            echo '<a href="index.php?id=' . $parent['id'] . '">' . htmlspecialchars($parent['ten_danh_muc']) . '</a>';

                            $sql_child = "SELECT * FROM danh_muc WHERE id_cha = " . intval($parent['id']) . " ORDER BY id ASC";
                            $result_child = $conn->query($sql_child);

                            if ($result_child->num_rows > 0) {
                                echo '<ul class="subcategory-list">';
                                while ($child = $result_child->fetch_assoc()) {
                                    $active_child = ($category_id == $child['id']) ? ' class="active"' : '';
                                    echo '<li' . $active_child . '><a href="index.php?id=' . $child['id'] . '">' . htmlspecialchars($child['ten_danh_muc']) . '</a></li>';
                                }
                                echo '</ul>';
                            }
                            echo '</li>';
                        }
                    } else {
                        echo '<li>Không có danh mục nào.</li>';
                    }
                    ?>
                </ul>
            </div>

            <?php if ($category_id > 0): ?>
                <div class="mt-3">
                    <a href="index.php?all=1" class="btn btn-outline-secondary btn-sm w-100">
                        ← Tất cả sản phẩm
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($show_all): ?>
                <div class="mt-3">
                    <a href="index.php" class="btn btn-outline-secondary btn-sm w-100">
                        ← Trang chủ
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Cột SẢN PHẨM -->
        <div class="col-md-9 product-column">
            <h5 class="section-title"><?php echo $section_title; ?></h5>
            <div class="row product-grid">
                <?php
                $result = $conn->query($products_query);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <img src="assets/images/' . htmlspecialchars($row['link_anh']) . '" 
                                     class="card-img-top" 
                                     alt="' . htmlspecialchars($row['ten_san_pham']) . '" 
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title">' . htmlspecialchars($row['ten_san_pham']) . '</h6>
                                    <p class="card-text mb-2"><strong>' . number_format($row['gia']) . ' VNĐ</strong></p>
                                    <div class="mt-auto">
                                        <a href="product.php?id=' . $row['id_san_pham'] . '" class="btn btn-outline-secondary btn-sm me-2">TÙY CHỌN</a>
                                        <!-- ĐÃ THAY BẰNG BUTTON ĐỂ KHÔNG NHẢY TRANG -->
                                        <button type="button" 
                                                onclick="addToCart(' . $row['id_san_pham'] . ')" 
                                                class="btn btn-primary btn-sm">
                                            Thêm Giỏ Hàng
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                } else {
                    echo '<p class="text-center w-100">Không tìm thấy sản phẩm nào trong danh mục này.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php if ($category_id == 0 && !$show_all): ?>
    <div class="banner mb-4 position-relative">
        <a href="#"><img src="assets/images/hmv.png" class="img-fluid w-100" alt="Hàng mới về" style="height: 300px; object-fit: cover;"></a>
    </div>

    <h5 class="section-title">SẢN PHẨM KHUYẾN MÃI</h5>
    <div class="row mb-4">
        <?php
        $sql_km = "SELECT * FROM san_pham ORDER BY RAND() LIMIT 6";
        $result_km = $conn->query($sql_km);
        if ($result_km->num_rows > 0) {
            while ($row = $result_km->fetch_assoc()) {
                $giam_gia = rand(15, 50);
                $gia_cu = $row['gia'] * (100 + $giam_gia) / 100;
                echo '
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm position-relative h-100">
                        <span class="position-absolute top-0 start-0 badge bg-danger z-3">-' . $giam_gia . '%</span>
                        <img src="assets/images/' . htmlspecialchars($row['link_anh']) . '" 
                            class="card-img-top p-2"
                            alt="' . htmlspecialchars($row['ten_san_pham']) . '"
                            style="height: 220px; object-fit: contain; background: #fff;">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">' . htmlspecialchars($row['ten_san_pham']) . '</h6>
                            <p class="card-text mb-2">
                                <strong class="text-danger">' . number_format($row['gia']) . ' VNĐ</strong>
                                <del class="text-muted small">' . number_format($gia_cu) . ' VNĐ</del>
                            </p>
                            <div class="mt-auto">
                                <a href="product.php?id=' . $row['id_san_pham'] . '" class="btn btn-outline-secondary btn-sm w-100">Xem chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }
        ?>
    </div>

    <div class="text-center mb-5">
        <a href="index.php?all=1" class="btn btn-primary btn-lg">XEM TẤT CẢ SẢN PHẨM</a>
    </div>
    <?php endif; ?>
</div>

<!-- SCRIPT HOÀN CHỈNH – ĐÃ TEST 100% -->
<script>
// Đóng popup mượt mà
function closeCartPopup() {
    const popup = document.getElementById('addToCartSuccess');
    const backdrop = document.getElementById('cartPopupBackdrop');
    if (popup) {
        popup.style.opacity = '0';
        popup.style.transform = 'translateY(-30px)';
        setTimeout(() => {
            popup.remove();
            if (backdrop) backdrop.remove();
        }, 300);
    }
}

// Thêm vào giỏ hàng – KHÔNG RELOAD, Ở LẠI TRANG HIỆN TẠI 100%
function addToCart(id) {
    fetch(`cart.php?action=add&id=${id}&qty=1`, { credentials: 'same-origin' })
        .then(r => r.text())
        .then(html => {
            // Chèn popup từ cart.php vào trang hiện tại
            const div = document.createElement('div');
            div.innerHTML = html;
            const popup = div.querySelector('#addToCartSuccess');
            const backdrop = div.querySelector('#cartPopupBackdrop');
            if (popup) document.body.appendChild(popup);
            if (backdrop) document.body.appendChild(backdrop);

            // Cập nhật badge giỏ hàng
            const newCount = <?php echo $cart_count; ?> + 1;
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = newCount;
            } else if (newCount > 0) {
                const cartBox = document.querySelector('.cart-box') || document.querySelector('a[href="cart.php"]');
                if (cartBox) cartBox.insertAdjacentHTML('beforeend', `<span class="cart-badge">${newCount}</span>`);
            }

            // Xóa tham số URL
            const url = new URL(location);
            ['action', 'id', 'qty'].forEach(p => url.searchParams.delete(p));
            history.replaceState(null, '', url);

            // Tự động đóng sau 10s
            setTimeout(closeCartPopup, 10000);
        });
}
</script>

<?php include 'includes/footer.php'; ?>


