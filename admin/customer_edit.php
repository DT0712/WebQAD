<?php include 'includes/header.php'; ?>

<?php
if (!isset($_GET['id'])) {
    header('Location: admin_users.php');
    exit;
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM khach_hang WHERE id_khach_hang = $id");
if ($result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Khách hàng không tồn tại!</div>";
    exit;
}
$customer = $result->fetch_assoc();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho_ten     = trim($_POST['ho_ten']);
    $email      = trim($_POST['email']);
    $dien_thoai = trim($_POST['dien_thoai']);
    $mat_khau   = $_POST['mat_khau'];

    if (empty($ho_ten) || empty($email)) {
        $error = "Họ tên và email không được để trống!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không hợp lệ!";
    } else {
        // Kiểm tra email trùng (trừ chính nó)
        $check = $conn->prepare("SELECT id_khach_hang FROM khach_hang WHERE email = ? AND id_khach_hang != ?");
        $check->bind_param("si", $email, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $error = "Email đã được sử dụng bởi tài khoản khác!";
        } else {
            if (!empty($mat_khau)) {
                if (strlen($mat_khau) < 6) {
                    $error = "Mật khẩu mới phải từ 6 ký tự!";
                } else {
                    $hash = password_hash($mat_khau, PASSWORD_DEFAULT);
                    $sql = "UPDATE khach_hang SET ho_ten=?, email=?, dien_thoai=?, mat_khau=? WHERE id_khach_hang=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssi", $ho_ten, $email, $dien_thoai, $hash, $id);
                }
            } else {
                $sql = "UPDATE khach_hang SET ho_ten=?, email=?, dien_thoai=? WHERE id_khach_hang=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $ho_ten, $email, $dien_thoai, $id);
            }

            if ($stmt->execute()) {
                $success = "Cập nhật thông tin thành công!";
                $customer['ho_ten'] = $ho_ten;
                $customer['email'] = $email;
                $customer['dien_thoai'] = $dien_thoai;
            } else {
                $error = "Có lỗi xảy ra!";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-header bg-gradient bg-warning text-dark text-center py-4 rounded-top-4">
                <h3 class="mb-0">Sửa Khách hàng #<?= $id ?></h3>
            </div>
            <div class="card-body p-5">

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="bi bi-check-circle-fill"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-bold">Họ và tên</label>
                            <input type="text" name="ho_ten" class="form-control form-control-lg" required
                                   value="<?= htmlspecialchars($customer['ho_ten']) ?>">
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg" required
                                   value="<?= htmlspecialchars($customer['email']) ?>">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" name="dien_thoai" class="form-control form-control-lg"
                                   value="<?= htmlspecialchars($customer['dien_thoai'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-warning">
                                Để trống nếu không muốn đổi mật khẩu
                            </label>
                            <input type="password" name="mat_khau" class="form-control form-control-lg"
                                   placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)">
                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-5">
                        <button type="submit" class="btn btn-warning btn-lg px-5 shadow">
                            Cập nhật thông tin
                        </button>
                        <a href="admin_users.php