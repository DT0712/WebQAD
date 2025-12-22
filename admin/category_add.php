<?php include 'includes/header.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten_danh_muc']);
    $parent = !empty($_POST['id_cha']) ? intval($_POST['id_cha']) : null;

    if (empty($ten)) {
        $error = "Vui lòng nhập tên danh mục!";
    } else {
        $stmt = $conn->prepare("INSERT INTO danh_muc (ten_danh_muc, id_cha) VALUES (?, ?)");
        $stmt->bind_param("si", $ten, $parent);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success alert-dismissible fade show'>
                    <i class='bi bi-check-circle'></i> Thêm danh mục thành công!
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
        } else {
            echo "<div class='alert alert-danger'>Lỗi: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white py-3">
                <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Thêm Danh mục Mới</h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Tên danh mục <span class="text-danger">*</span></label>
                        <input type="text" name="ten_danh_muc" class="form-control form-control-lg" 
                               placeholder="Ví dụ: Áo thun nữ, Quần jeans nam..." required autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Danh mục cha</label>
                        <select name="id_cha" class="form-select form-select-lg">
                            <option value="">Danh mục gốc (không có cha)</option>
                            <?php
                            $result = $conn->query("SELECT * FROM danh_muc WHERE id_cha IS NULL ORDER BY ten_danh_muc");
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['ten_danh_muc']) . "</option>";
                            }
                            ?>
                        </select>
                        <div class="form-text">Chọn nếu muốn tạo danh mục con</div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-check2"></i> Lưu danh mục
                        </button>
                        <a href="admin_categories.php" class="btn btn-light btn-lg px-5">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>