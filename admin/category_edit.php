<?php include 'includes/header.php'; ?>

<?php
if (!isset($_GET['id'])) {
    header('Location: admin_categories.php');
    exit;
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM danh_muc WHERE id = $id");
if ($result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Danh mục không tồn tại!</div>";
    exit;
}
$category = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten = trim($_POST['ten_danh_muc']);
    $parent = !empty($_POST['id_cha']) ? intval($_POST['id_cha']) : null;

    // Không cho chọn chính nó làm cha
    if ($parent == $id) {
        $error = "Không thể chọn chính danh mục này làm danh mục cha!";
    } elseif (empty($ten)) {
        $error = "Tên danh mục không được để trống!";
    } else {
        $stmt = $conn->prepare("UPDATE danh_muc SET ten_danh_muc = ?, id_cha = ? WHERE id = ?");
        $stmt->bind_param("sii", $ten, $parent, $id);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success alert-dismissible fade show'>
                    <i class='bi bi-check-circle'></i> Cập nhật thành công!
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
            // Cập nhật lại dữ liệu hiển thị
            $category['ten_danh_muc'] = $ten;
            $category['id_cha'] = $parent;
        } else {
            echo "<div class='alert alert-danger'>Lỗi: " . $stmt->error . "</div>";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow border-0">
            <div class="card-header bg-warning text-dark py-3">
                <h4 class="mb-0"><i class="bi bi-pencil-square"></i> Sửa Danh mục #<?= $id ?></h4>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tên danh mục</label>
                        <input type="text" name="ten_danh_muc" class="form-control form-control-lg"
                               value="<?= htmlspecialchars($category['ten_danh_muc']) ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Danh mục cha</label>
                        <select name="id_cha" class="form-select form-select-lg">
                            <option value="">Danh mục gốc</option>
                            <?php
                            $parents = $conn->query("SELECT * FROM danh_muc WHERE id != $id AND id_cha IS NULL ORDER BY ten_danh_muc");
                            while ($p = $parents->fetch_assoc()) {
                                $selected = ($p['id'] == $category['id_cha']) ? 'selected' : '';
                                echo "<option value='{$p['id']}' $selected>" . htmlspecialchars($p['ten_danh_muc']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-warning btn-lg px-5">
                            <i class="bi bi-save"></i> Cập nhật
                        </button>
                        <a href="admin_categories.php" class="btn btn-secondary btn-lg px-5">
                            <i class="bi bi-x-circle"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>