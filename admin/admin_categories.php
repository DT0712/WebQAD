<?php include 'includes/header.php'; ?>

<?php
// Xử lý xóa danh mục
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    // Kiểm tra có danh mục con không
    $has_child = $conn->query("SELECT COUNT(*) FROM danh_muc WHERE id_cha = $id")->fetch_row()[0] > 0;
    // Kiểm tra có sản phẩm thuộc danh mục này không
    $has_product = $conn->query("SELECT COUNT(*) FROM san_pham WHERE id_danh_muc = $id")->fetch_row()[0] > 0;

    if ($has_child) {
        $msg = "Không thể xóa! Danh mục này đang chứa danh mục con.";
        $type = "warning";
    } elseif ($has_product) {
        $msg = "Không thể xóa! Có sản phẩm đang thuộc danh mục này.";
        $type = "warning";
    } else {
        if ($conn->query("DELETE FROM danh_muc WHERE id = $id")) {
            $msg = "Xóa danh mục thành công!";
            $type = "success";
        } else {
            $msg = "Lỗi khi xóa: " . $conn->error;
            $type = "danger";
        }
    }

    echo "<div class='alert alert-$type alert-dismissible fade show mt-3'>
            $msg
            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
          </div>";
}

// Hàm hiển thị danh mục dạng cây (đệ quy)
function showCategories($parent_id = null, $level = 0) {
    global $conn;

    $sql = $parent_id === null 
        ? "SELECT * FROM danh_muc WHERE id_cha IS NULL ORDER BY ten_danh_muc"
        : "SELECT * FROM danh_muc WHERE id_cha = $parent_id ORDER BY ten_danh_muc";

    $result = $conn->query($sql);

    if ($result->num_rows === 0) return;

    $indent = str_repeat("├── ", $level);

    while ($cat = $result->fetch_assoc()) {
        $id = $cat['id'];
        $name = htmlspecialchars($cat['ten_danh_muc']);
        $badge = $level == 0 ? '<span class="badge bg-primary ms-2">Danh mục gốc</span>' : '';

        echo "<tr>
                <td class='text-center fw-bold'>#$id</td>
                <td>
                    <strong>$indent$name</strong> $badge
                </td>
                <td class='text-center'>
                    <a href='category_edit.php?id=$id' class='btn btn-sm btn-warning text-white' title='Sửa'>
                        <i class='bi bi-pencil-square'></i>
                    </a>
                    <a href='?delete_id=$id' class='btn btn-sm btn-danger ms-1' title='Xóa'
                       onclick=\"return confirm('Xóa danh mục \\\"$name\\\"?\\nCẩn thận: hành động này không thể hoàn tác!');\">
                        <i class='bi bi-trash'></i>
                    </a>
                </td>
              </tr>";

        // Đệ quy hiển thị danh mục con
        showCategories($id, $level + 1);
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold text-dark mb-0">
        <i class="bi bi-tags text-primary"></i> Quản lý Danh mục
    </h2>
    <a href="category_add.php" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Thêm danh mục mới
    </a>
</div>

<div class="card shadow border-0">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 text-primary fw-bold">Danh sách danh mục (<?= $conn->query("SELECT COUNT(*) FROM danh_muc")->fetch_row()[0]; ?> danh mục)</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="100" class="text-center">ID</th>
                        <th>Tên danh mục</th>
                        <th width="150" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php showCategories(); ?>
                    
                    <?php if ($conn->query("SELECT 1 FROM danh_muc LIMIT 1")->num_rows == 0): ?>
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x display-1 d-block mb-3 opacity-25"></i>
                            Chưa có danh mục nào
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>