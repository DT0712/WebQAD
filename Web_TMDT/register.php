<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $dien_thoai = trim($_POST['dien_thoai'] ?? '');
    $mat_khau   = $_POST['mat_khau'] ?? '';
    $nhap_lai   = $_POST['nhap_lai'] ?? '';

    $ho_ten = $first_name . ' ' . $last_name;

    // Validate
    if (empty($first_name) || empty($last_name) || empty($email) || empty($dien_thoai) || empty($mat_khau) || empty($nhap_lai)) {
        $error = 'Vui lòng nhập đầy đủ tất cả các trường.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ.';
    } elseif (!preg_match('/^0[3|5|7|8|9][0-9]{8}$/', $dien_thoai)) {
        $error = 'Số điện thoại không hợp lệ. Ví dụ đúng: 0901234567';
    } elseif ($mat_khau !== $nhap_lai) {
        $error = 'Mật khẩu nhập lại không khớp.';
    } elseif (strlen($mat_khau) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự.';
    } elseif (!isset($_POST['terms'])) {
        $error = 'Bạn phải đồng ý với Điều khoản dịch vụ.';
    } else {
        // Kiểm tra email đã tồn tại
        $check = $conn->prepare("SELECT id_khach_hang FROM khach_hang WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = 'Email này đã được đăng ký.';
        } else {
            $hash = password_hash($mat_khau, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO khach_hang (ho_ten, email, dien_thoai, mat_khau) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $ho_ten, $email, $dien_thoai, $hash);

            if ($stmt->execute()) {
                $success = 'Đăng ký thành công! Đang chuyển đến trang đăng nhập...';
                header("Refresh: 2; url=login.php");
                exit;
            } else {
                $error = 'Có lỗi xảy ra, vui lòng thử lại sau.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký - Blank Label</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
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
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: -1;
        }
        .auth-container {
            max-width: 900px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            display: flex;
            backdrop-filter: blur(10px);
        }
        .auth-left {
            flex: 1;
            background-image: url('assets/images/bg-login2.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }
        .auth-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
        }
        .auth-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
        .logo {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            letter-spacing: 2px;
        }
        .logo span { color: #00bcd4; font-family: cursive; }
        .tagline {
            font-size: 1.3rem;
            line-height: 1.6;
            font-weight: 300;
        }
        .auth-right {
            flex: 1;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .auth-subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .form-control {
            border-radius: 10px;
            padding: 1rem 3rem 1rem 1rem;
            font-size: 1rem;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 5;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .back-link {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: white;
            text-decoration: none;
            z-index: 1;
        }
        .link-alt {
            text-align: center;
            margin-top: 1.5rem;
            color: #6c757d;
        }
        .link-alt a {
            color: #667eea;
            font-weight: 500;
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .auth-container { flex-direction: column; border-radius: 0; }
            .auth-left, .auth-right { padding: 2rem; }
            .logo { font-size: 2.5rem; }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-left">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left me-1"></i>Quay lại trang chủ
            </a>
            <div class="auth-left-content">
                <div class="logo">>Bike<span>Market</span></div>
                <p class="tagline">Chụp lại những khoảnh khắc, Tạo nên những kỷ niệm</p>
            </div>
        </div>

        <div class="auth-right">
            <h1 class="auth-title">Tạo tài khoản</h1>
            <p class="auth-subtitle">Hãy tạo tài khoản để bắt đầu hành trình mua sắm của bạn.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="first_name" class="form-control" id="first_name" placeholder="Tên" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
                            <label for="first_name">Tên</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" name="last_name" class="form-control" id="last_name" placeholder="Họ" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
                            <label for="last_name">Họ</label>
                        </div>
                    </div>
                </div>

                <div class="form-floating">
                    <input type="email" name="email" class="form-control" id="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <label for="email">Email</label>
                </div>

                <div class="form-floating">
                    <input type="text" name="dien_thoai" class="form-control" id="dien_thoai" placeholder="Số điện thoại" required value="<?= htmlspecialchars($_POST['dien_thoai'] ?? '') ?>">
                    <label for="dien_thoai">Số điện thoại</label>
                </div>

                <div class="form-floating position-relative">
                    <input type="password" name="mat_khau" class="form-control" id="mat_khau" placeholder="Mật khẩu" required minlength="6">
                    <label for="mat_khau">Mật khẩu</label>
                    <i class="fas fa-eye-slash password-toggle" onclick="togglePassword('mat_khau')"></i>
                </div>

                <div class="form-floating position-relative">
                    <input type="password" name="nhap_lai" class="form-control" id="nhap_lai" placeholder="Nhập lại mật khẩu" required minlength="6">
                    <label for="nhap_lai">Nhập lại mật khẩu</label>
                    <i class="fas fa-eye-slash password-toggle" onclick="togglePassword('nhap_lai')"></i>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="terms" id="terms" required>
                    <label class="form-check-label" for="terms">
                        Tôi đồng ý với <a href="#" class="text-primary">Điều khoản dịch vụ</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Tạo tài khoản</button>
            </form>

            <div class="link-alt">
                Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
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