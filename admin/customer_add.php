<?php include 'includes/header.php'; ?>

<?php
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten     = trim($_POST['ho_ten'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $dien_thoai = trim($_POST['dien_thoai'] ?? '');
    $mat_khau   = $_POST['mat_khau'] ?? '';
    $nhap_lai   = $_POST['nhap_lai'] ?? '';

    // Kiểm tra bắt buộc
    if (empty($ho_ten) || empty($email) || empty($mat_khau)) {
        $error = "Vui lòng điền đầy đủ các trường bắt buộc!";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    }
    elseif ($mat_khau !== $nhap_lai) {
        $error = "Mật khẩu nhập lại không khớp!";
    }
    elseif (strlen($mat_khau) < 6) {
        $error = "Mật khẩu phải từ 6 ký tự trở lên!";
    }
    else {
        // Kiểm tra email đã tồn tại chưa
        $check = $conn->prepare("SELECT id_khach_hang FROM khach_hang WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email này đã được sử dụng!";
        } else {
            // Thêm khách hàng mới
            $hash = password_hash($mat_khau, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO khach_hang (ho_ten, email, dien_thoai, mat_khau) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $ho_ten, $email, $dien_thoai, $hash);

            if ($stmt->execute()) {
                $success = "Thêm khách hàng thành công!";
                // Reset form sau khi thành công
                $_POST = [];
            } else {
                $error = "Có lỗi xảy ra khi thêm khách hàng.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-gradient bg-primary text-white text-center py-4">
                <h3 class="mb-0 fw-bold">
                    Thêm Khách hàng Mới
                </h3>
            </div>
            <div class="card-body p-5 bg-light">

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show rounded-3">
                        <i class="bi bi-check-circle-fill me-2"></i><?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark">
                                Họ và tên <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="ho_ten" class="form-control form-control-lg shadow-sm" 
                                   placeholder="Nhập họ và tên" required
                                   value="<?= htmlspecialchars($ho_ten ?? '') ?>">
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold text-dark">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" name="email" class="form-control form-control-lg shadow-sm" 
                                   placeholder="example@gmail.com" required
                                   value="<?= htmlspecialchars($email ?? '') ?>">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold text-dark">Số điện thoại</label>
                            <input type="text" name="dien_thoai" class="form-control form-control-lg shadow-sm" 
                                   placeholder="0901234567"
                                   value="<?= htmlspecialchars($dien_thoai ?? '') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">
                                Mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="mat_khau" class="form-control form-control-lg shadow-sm" 
                                   placeholder="Tối thiểu 6 ký tự" required minlength="6">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">
                                Nhập lại mật khẩu <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="nhap_lai" class="form-control form-control-lg shadow-sm" 
                                   placeholder="Nhập lại mật khẩu" required minlength="6">
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-5">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                            Thêm khách hàng
                        </button>
                        <a href="admin_users.php" class="btn btn-outline-secondary btn-lg px-5">
                            Quay lại
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>