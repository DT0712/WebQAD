<?php
session_start();
include 'config.php';

$error = '';
$success = '';
$step = 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step']) && $_POST['step'] == 1) {
        $email = trim($_POST['email']);
        $dien_thoai = trim($_POST['dien_thoai']);

        if (empty($email) || empty($dien_thoai)) {
            $error = 'Vui lòng nhập đầy đủ email và số điện thoại.';
        } else {
            $sql = "SELECT id_khach_hang FROM khach_hang WHERE email = ? AND dien_thoai = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $email, $dien_thoai);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $_SESSION['reset_user_id'] = $row['id_khach_hang'];
                $step = 2;
            } else {
                $error = 'Email hoặc số điện thoại không đúng.';
            }
            $stmt->close();
        }
    }
    elseif (isset($_POST['step']) && $_POST['step'] == 2 && isset($_SESSION['reset_user_id'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error = 'Mật khẩu xác nhận không khớp.';
        } elseif (strlen($new_password) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);

            $sql = "UPDATE khach_hang SET mat_khau = ? WHERE id_khach_hang = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed, $_SESSION['reset_user_id']);

            if ($stmt->execute()) {
                unset($_SESSION['reset_user_id']); // Xóa session

                // Tự động chuyển về trang đăng nhập sau 1.5 giây
                $success = 'Đặt lại mật khẩu thành công! Đang chuyển về trang đăng nhập...';
                echo '<meta http-equiv="refresh" content="1.5;url=login.php">';
                
                // Hoặc dùng JavaScript (tùy thích)
                // echo '<script>setTimeout(() => { window.location.href = "login.php"; }, 1500);</script>';
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại.';
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quên mật khẩu - BikeMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Giữ nguyên toàn bộ CSS như login.php (đã tối ưu) */
        body {
            background-image: url('assets/images/bg-login-large.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        body::before {
            content: ''; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.3); z-index: -1;
        }
        .auth-container {
            max-width: 900px; width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px; overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            display: flex; backdrop-filter: blur(10px);
        }
        .auth-left {
            flex: 1;
            background-image: url('assets/images/bg-login2.jpg');
            background-size: cover; background-position: center;
            color: white; padding: 3rem;
            display: flex; flex-direction: column; justify-content: center;
            position: relative;
        }
        .auth-left::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.4); }
        .auth-left-content { position: relative; z-index: 1; text-align: center; }
        .logo { font-size: 3rem; font-weight: 800; margin-bottom: 1.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .logo span { color: #00bcd4; font-family: cursive; }
        .tagline { font-size: 1.3rem; opacity: 1; line-height: 1.6; font-weight: 300; }
        .auth-right { flex: 1; padding: 3rem; display: flex; flex-direction: column; justify-content: center; }
        .auth-title { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .auth-subtitle { color: #6c757d; margin-bottom: 2rem; }
        .form-control { border-radius: 10px; border: 1px solid #e9ecef; padding: 1rem; font-size: 1rem; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none; border-radius: 10px; padding: 1rem;
            font-size: 1rem; font-weight: 600; width: 100%;
            transition: transform 0.2s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102,126,234,0.3); }
        .link-alt { text-align: center; margin-top: 1.5rem; color: #6c757d; }
        .link-alt a { color: #667eea; text-decoration: none; font-weight: 500; }
        .back-link { position: absolute; top: 1rem; left: 1rem; color: white; font-size: 0.95rem; opacity: 0.9; z-index: 1; }
        .back-link:hover { opacity: 1; }
        .password-toggle { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #6c757d; }
        @media (max-width: 768px) {
            .auth-container { flex-direction: column; border-radius: 0; }
            .auth-left, .auth-right { padding: 2rem; }
            .logo { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

<div class="auth-container">
    <!-- Left Panel -->
    <div class="auth-left">
        <a href="index.php" class="back-link">
            Quay lại trang chủ
        </a>
        <div class="auth-left-content">
            <div class="logo">Blank<span>Label</span></div>
            <p class="tagline">Chụp lại những khoảnh khắc, Tạo nên những kỷ niệm</p>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="auth-right">
        <h1 class="auth-title">Quên mật khẩu</h1>
        <p class="auth-subtitle">
            <?= $step == 1 ? 'Nhập email và số điện thoại đã đăng ký.' : 'Nhập mật khẩu mới.' ?>
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success text-center">
                <i class="fas fa-check-circle fa-2x mb-3"></i><br>
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Bước 1 -->
        <?php if ($step == 1 && !$success): ?>
        <form method="POST">
            <input type="hidden" name="step" value="1">
            <div class="form-floating mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <label>Email</label>
            </div>
            <div class="form-floating mb-4">
                <input type="tel" name="dien_thoai" class="form-control" placeholder="Số điện thoại" required value="<?= htmlspecialchars($_POST['dien_thoai'] ?? '') ?>">
                <label>Số điện thoại</label>
            </div>
            <button type="submit" class="btn btn-primary">Tiếp tục</button>
        </form>
        <?php endif; ?>

        <!-- Bước 2 -->
        <?php if ($step == 2 && !$success): ?>
        <form method="POST">
            <input type="hidden" name="step" value="2">
            <div class="form-floating position-relative mb-3">
                <input type="password" name="new_password" class="form-control" id="new_password" placeholder="Mật khẩu mới" required>
                <label>Mật khẩu mới</label>
                <i class="fas fa-eye-slash password-toggle" onclick="togglePassword('new_password')"></i>
            </div>
            <div class="form-floating position-relative mb-4">
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" placeholder="Xác nhận mật khẩu" required>
                <label>Xác nhận mật khẩu</label>
                <i class="fas fa-eye-slash password-toggle" onclick="togglePassword('confirm_password')"></i>
            </div>
            <button type="submit" class="btn btn-primary">Đặt lại mật khẩu</button>
        </form>
        <?php endif; ?>

        <div class="link-alt">
            <a href="login.php">Quay lại đăng nhập</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const icon = input.parentElement.querySelector('.password-toggle');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        }
    }
</script>
</body>
</html>