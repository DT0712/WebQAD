<?php include 'includes/header.php'; ?>

<?php
// Xử lý xóa khách hàng
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($conn->query("DELETE FROM khach_hang WHERE id_khach_hang = $id")) {
        echo "<div class='alert alert-success alert-dismissible fade show'>
                Đã xóa khách hàng thành công!
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
    }
}

// Lấy danh sách khách hàng từ bảng khach_hang (đúng tên cột)
$customers = $conn->query("
    SELECT id_khach_hang, ho_ten, email, dien_thoai, ngay_tao
    FROM khach_hang 
    ORDER BY ngay_tao DESC
");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold text-dark mb-0">
        Quản lý Khách hàng
        <span class="badge bg-primary fs-6 ms-2">
            <?= $customers ? $customers->num_rows : 0 ?> khách hàng
        </span>
    </h2>
    <a href="customer_add.php" class="btn btn-success shadow-sm">
        Thêm khách hàng mới
    </a>
</div>

<div class="card shadow border-0">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 text-primary fw-bold">Danh sách khách hàng</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="80" class="text-center">#</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Ngày đăng ký</th>
                        <th width="140" class="text-center">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($customers && $customers->num_rows > 0): ?>
                        <?php while ($c = $customers->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center fw-bold">#<?= $c['id_khach_hang'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center"
                                             style="width:42px;height:42px;font-weight:bold;font-size:14px;">
                                            <?= strtoupper(mb_substr($c['ho_ten'], 0, 2, 'UTF-8')) ?>
                                        </div>
                                        <div>
                                            <strong><?= htmlspecialchars($c['ho_ten']) ?></strong>
                                        </div>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($c['email']) ?></td>
                                <td><?= $c['dien_thoai'] ?: '<em class="text-muted">Chưa có</em>' ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($c['ngay_tao'])) ?></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="customer_edit.php?id=<?= $c['id_khach_hang'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Sửa">
                                            Sửa
                                        </a>
                                        <a href="?delete=1&id=<?= $c['id_khach_hang'] ?>" 
                                           class="btn btn-sm btn-outline-danger ms-1" title="Xóa"
                                           onclick="return confirm('XÓA khách hàng này? Không thể khôi phục!')">
                                            Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-person-x display-1 d-block mb-3 opacity-25"></i>
                                <h5>Chưa có khách hàng nào</h5>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>