<?php
include 'config.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    echo "<div class='container mt-5 text-center'><h4>Không tìm thấy sản phẩm.</h4></div>";
    exit;
}

$id = intval($_GET['id']);

// Lấy sản phẩm
$sql = "SELECT * FROM san_pham WHERE id_san_pham = $id";
$product = $conn->query($sql)->fetch_assoc();

if (!$product) {
    echo "<div class='container mt-5 text-center'><h4>Sản phẩm không tồn tại.</h4></div>";
    exit;
}

// Lấy chi tiết sản phẩm
$sql_detail = "SELECT * FROM chi_tiet_san_pham WHERE id_san_pham = $id";
$detail = $conn->query($sql_detail)->fetch_assoc();

$mau_sac = json_decode($detail['mau_sac'] ?? '[]', true);
$hinh_anh_phu = json_decode($detail['hinh_anh_phu'] ?? '[]', true);

// LẤY SẢN PHẨM LIÊN QUAN
$id_danh_muc = $product['id_danh_muc'];
$parent = $conn->query("SELECT id_cha FROM danh_muc WHERE id = $id_danh_muc")->fetch_assoc();
$id_cha = $parent['id_cha'] ?? $id_danh_muc;

$sql_related = "SELECT sp.* FROM san_pham sp
                JOIN danh_muc dm ON sp.id_danh_muc = dm.id
                WHERE (dm.id = $id_cha OR dm.id_cha = $id_cha)
                  AND sp.id_san_pham != $id
                LIMIT 12";
$related = $conn->query($sql_related);

// Tính số lượng giỏ hàng hiện tại (dùng cho JS)
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'] ?? 0;
    }
}
?>

<style>
:root {
    --primary: #1a1a1a;
    --secondary: #f8f9fa;
    --accent: #007bff;
    --success: #28a745;
    --danger: #dc3545;
    --warning: #ffc107;
    --info: #17a2b8;
    --light: #f8f9fa;
    --dark: #343a40;
    --gray: #6c757d;
    --border-radius: 8px;
    --box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    --transition: all 0.3s ease-in-out;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    line-height: 1.6;
    color: var(--primary);
    background-color: var(--secondary);
}

.container { max-width: 1200px; padding: 2rem 1rem; margin: auto; }

.product-detail {
    background: #fff;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    margin-bottom: 3rem;
}

.product-header {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    padding: 3rem;
    border-bottom: 1px solid #e9ecef;
}

@media (max-width: 992px) {
    .product-header { grid-template-columns: 1fr; gap: 2rem; padding: 2rem; }
}

/* ====================== ẢNH SẢN PHẨM ====================== */
.product-image-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.main-image-wrapper {
    position: relative;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.main-image-wrapper:hover { box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15); }

.mainImage {
    width: 100%;
    height: 400px;
    object-fit: cover;
    display: block;
    transition: var(--transition);
}

.main-image-wrapper:hover .mainImage { transform: scale(1.02); }

.thumbnails {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
    gap: 0.75rem;
}

.thumb-img {
    width: 100%;
    height: auto;
    aspect-ratio: 1 / 1;
    object-fit: cover;
    border: 3px solid transparent;
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: var(--transition);
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.thumb-img:hover,
.thumb-img.active {
    border-color: var(--accent);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .thumbnails { grid-template-columns: repeat(4, 1fr); gap: 0.5rem; }
}

/* ====================== THÔNG TIN SẢN PHẨM ====================== */
.product-info { padding: 1rem 0; display: flex; flex-direction: column; gap: 1.5rem; }

.product-title { font-size: 2rem; font-weight: 700; color: var(--primary); margin: 0 0 0.5rem; line-height: 1.2; }
.product-price { font-size: 2.5rem; font-weight: 700; color: var(--danger); margin: 0; }

.product-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    background: var(--light);
    padding: 1.5rem;
    border-radius: var(--border-radius);
    border: 1px solid #e9ecef;
}

.feature-label { font-size: 0.875rem; font-weight: 600; color: var(--gray); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem; }
.feature-value { font-size: 1rem; font-weight: 500; color: var(--primary); }

.product-form { display: flex; flex-direction: column; gap: 1rem; }

.form-select, .form-control {
    border: 1px solid #ced4da;
    border-radius: var(--border-radius);
    padding: 0.75rem 1rem;
    font-size: 1rem;
    transition: var(--transition);
}

.quantity-input {
    display: flex;
    align-items: center;
    border: 1px solid #ced4da;
    border-radius: var(--border-radius);
    overflow: hidden;
    max-width: 140px;
}

.quantity-btn {
    background: #f8f9fa;
    border: none;
    width: 40px;
    height: 45px;
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray);
    cursor: pointer;
}

.quantity-btn:hover { background: #e9ecef; color: var(--primary); }

.quantity-input input {
    border: none;
    text-align: center;
    width: 60px;
    height: 45px;
    font-weight: 600;
}

.add-to-cart {
    background: var(--accent);
    color: #fff;
    border: none;
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.add-to-cart:hover {
    background: #0056b3;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
}

.tags-section .label { font-weight: 600; margin-bottom: 0.5rem; display: block; }
.tags-list { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.tag { background: var(--light); color: var(--gray); padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; border: 1px solid #e9ecef; }
.tag:hover { background: var(--accent); color: #fff; border-color: var(--accent); }

.tabs-section { background: #fff; border-radius: var(--border-radius); box-shadow: var(--box-shadow); overflow: hidden; margin-bottom: 3rem; }
.tabs-nav { display: flex; background: #f8f9fa; border-bottom: 1px solid #e9ecef; }
.tab-btn { flex: 1; padding: 1rem; border: none; background: transparent; font-weight: 600; color: var(--gray); cursor: pointer; }
.tab-btn:hover { color: var(--accent); background: #e9ecef; }
.tab-btn.active { color: var(--accent); border-bottom: 2px solid var(--accent); }
.tab-panel { display: none; padding: 2rem; }
.tab-panel.active { display: block; }

.related-products { background: #fff; border-radius: var(--border-radius); box-shadow: var(--box-shadow); padding: 3rem; }
.related-title { font-size: 1.5rem; font-weight: 700; text-align: center; margin-bottom: 2rem; }
.related-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
.related-item { border: 1px solid #e9ecef; border-radius: var(--border-radius); overflow: hidden; transition: var(--transition); }
.related-item:hover { box-shadow: var(--box-shadow); transform: translateY(-4px); }
.related-img { width: 100%; height: 200px; object-fit: cover; }
.related-name { font-size: 1rem; font-weight: 600; margin: 0.5rem 0; padding: 0 1rem; }
.related-price { font-size: 1.2rem; font-weight: 700; color: var(--danger); padding: 0 1rem 1rem; margin: 0; }

@media (max-width: 768px) {
    .quantity-input { max-width: 120px; }
    .related-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
}
</style>

<div class="container">
    <div class="product-detail">
        <div class="product-header">
            <!-- Ảnh sản phẩm -->
            <div class="product-image-section">
                <div class="main-image-wrapper">
                    <img id="mainImage" src="assets/images/<?php echo htmlspecialchars($product['link_anh']); ?>" class="mainImage" alt="<?php echo htmlspecialchars($product['ten_san_pham']); ?>">
                </div>

                <?php if (!empty($hinh_anh_phu)): ?>
                <div class="thumbnails">
                    <?php foreach ($hinh_anh_phu as $img): ?>
                        <img src="<?php echo htmlspecialchars($img); ?>" class="thumb-img" onclick="changeMainImage(this.src)" alt="Ảnh phụ">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Thông tin sản phẩm -->
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['ten_san_pham']); ?></h1>
                <p class="product-price"><?php echo number_format($product['gia']); ?>₫</p>

                <div class="product-features">
                    <div><span class="feature-label">Bảo hành: </span><span class="feature-value"><?php echo htmlspecialchars($detail['bao_hanh'] ?? ''); ?></span></div>
                    <div><span class="feature-label">Xuất xứ: </span><span class="feature-value"><?php echo htmlspecialchars($detail['xuat_xu'] ?? ''); ?></span></div>
                    <div><span class="feature-label">Chất liệu: </span><span class="feature-value"><?php echo htmlspecialchars($detail['chat_lieu'] ?? ''); ?></span></div>
                </div>

                <div class="product-form">
                    <div class="form-group">
                        <label>Màu sắc</label>
                        <select class="form-select">
                            <?php foreach ($mau_sac as $mau): ?>
                                <option><?php echo htmlspecialchars($mau); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Số lượng</label>
                        <div class="quantity-input">
                            <button type="button" class="quantity-btn" onclick="updateQuantity(-1)">−</button>
                            <input type="number" id="qty" value="1" min="1" readonly>
                            <button type="button" class="quantity-btn" onclick="updateQuantity(1)">+</button>
                        </div>
                    </div>

                    <button type="button" class="add-to-cart" onclick="addToCart(<?php echo $id; ?>)">
                        Thêm vào giỏ hàng
                    </button>
                </div>

                <div class="tags-section">
                    <span class="label">Tags:</span>
                    <div class="tags-list">
                        <?php foreach (explode(',', $detail['tags'] ?? '') as $tag):
                            $tag = trim($tag);
                            if ($tag): ?>
                                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
                            <?php endif;
                        endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs-section">
        <nav class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('info')">THÔNG TIN SẢN PHẨM</button>
            <button class="tab-btn" onclick="switchTab('ship')">THANH TOÁN VẬN CHUYỂN</button>
            <button class="tab-btn" onclick="switchTab('review')">ĐÁNH GIÁ SẢN PHẨM</button>
        </nav>
        <div id="info" class="tab-panel active"><?php echo nl2br(htmlspecialchars($detail['mo_ta_chi_tiet'] ?? '')); ?></div>
        <div id="ship" class="tab-panel">
            <ul>
                <li>Miễn phí vận chuyển cho đơn từ 300.000₫</li>
                <li>Giao hàng toàn quốc từ 2 - 5 ngày</li>
                <li>Thanh toán khi nhận hàng hoặc qua ví điện tử</li>
            </ul>
        </div>
        <div id="review" class="tab-panel">
            <p>Chưa có đánh giá nào. <a href="#">Hãy là người đầu tiên đánh giá!</a></p>
        </div>
    </div>

    <!-- Sản phẩm liên quan -->
    <div class="related-products">
        <h2 class="related-title">Sản phẩm liên quan</h2>
        <div class="related-grid">
            <?php while($item = $related->fetch_assoc()): ?>
                <a href="product.php?id=<?php echo $item['id_san_pham']; ?>" class="related-item">
                    <img src="assets/images/<?php echo htmlspecialchars($item['link_anh']); ?>" class="related-img" alt="">
                    <h3 class="related-name"><?php echo htmlspecialchars($item['ten_san_pham']); ?></h3>
                    <p class="related-price"><?php echo number_format($item['gia']); ?>₫</p>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<script>
// Cập nhật số lượng
function updateQuantity(change) {
    const input = document.getElementById('qty');
    let value = parseInt(input.value) + change;
    if (value >= 1) input.value = value;
}

// Đổi ảnh
function changeMainImage(src) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.thumb-img').forEach(img => img.classList.remove('active'));
    event.target.classList.add('active');
}

// Chuyển tab
function switchTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    event.target.classList.add('active');
    document.getElementById(tab).classList.add('active');
}

// HÀM ĐÓNG POPUP - BẮT BUỘC PHẢI CÓ Ở ĐÂY ĐỂ NÚT "TIẾP TỤC MUA SẮM" HOẠT ĐỘNG
function closeCartPopup() {
    const popup = document.getElementById('addToCartSuccess');
    const backdrop = document.getElementById('cartPopupBackdrop');
    if (popup) popup.remove();
    if (backdrop) backdrop.remove();
}

// THÊM VÀO GIỎ HÀNG MƯỢT NHƯ SHOPEE
function addToCart(id) {
    const qty = document.getElementById('qty').value;

    fetch(`cart.php?action=add&id=${id}&qty=${qty}`, { credentials: 'same-origin' })
        .then(res => res.text())
        .then(html => {
            // Chèn popup vào trang
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const popup = doc.querySelector('#addToCartSuccess');
            const backdrop = doc.querySelector('#cartPopupBackdrop');

            if (popup) document.body.appendChild(popup);
            if (backdrop) document.body.appendChild(backdrop);

            // Cập nhật badge giỏ hàng ngay
            const newCount = <?php echo $cart_count; ?> + parseInt(qty);
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.textContent = newCount;
            } else if (newCount > 0) {
                document.querySelector('.cart-box').insertAdjacentHTML('beforeend', `<span class="cart-badge">${newCount}</span>`);
            }

            // Làm sạch URL
            const url = new URL(window.location);
            ['action', 'id', 'qty'].forEach(p => url.searchParams.delete(p));
            history.replaceState({}, '', url);
        });
}
</script>

<?php include 'includes/footer.php'; ?>