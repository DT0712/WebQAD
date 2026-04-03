<?php
session_start();
include 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $mat_khau = $_POST['mat_khau'];

    if (empty($email) || empty($mat_khau)) {
        $error = 'Vui lòng nhập đầy đủ thông tin';
    } else {
        $sql = "SELECT * FROM khach_hang WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($mat_khau, $user['mat_khau'])) {
                $_SESSION['khach_hang'] = [
                    'id_khach_hang' => $user['id_khach_hang'],
                    'ho_ten' => $user['ho_ten'],
                    'email' => $user['email'],
                    'dien_thoai' => $user['dien_thoai'],
                    'anh_dai_dien' => $user['anh_dai_dien'],  // Thêm dòng này
                    'anh_nen' => $user['anh_nen']
                ];
                // Quay lại trang trước đó (nếu có)
                $return = $_GET['return'] ?? 'index.php';
                header("Location: $return");
                exit;
            } else {
                $error = 'Mật khẩu không đúng';
            }
        } else {
            $error = 'Email không tồn tại';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập - Blank Label</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            /* Ảnh nền lớn toàn màn hình */
            background-image: url('assets/images/bg-login-large.jpg'); /* Thay bằng ảnh nền lớn của bạn */
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
            /* Overlay nhẹ cho toàn màn hình để form nổi bật */
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3); /* Điều chỉnh độ tối nếu cần */
            z-index: -1;
        }
        .auth-container {
            max-width: 900px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95); /* Nền trắng mờ để nổi trên background lớn */
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            display: flex;
            backdrop-filter: blur(10px); /* Hiệu ứng mờ cho hiện đại */
        }
        .auth-left {
            flex: 1;
            background-image: url('assets/images/bg-login2.jpg'); /* Ảnh nền bên trái riêng biệt */
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
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4); /* Overlay để chữ nổi bật */
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
        .logo span {
            color: #00bcd4;
            font-family: cursive;
        }
        .tagline {
            font-size: 1.3rem;
            opacity: 1;
            line-height: 1.6;
            font-weight: 300;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
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
            color: var(--dark);
        }
        .auth-subtitle {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #e9ecef;
            padding: 1rem;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 1rem;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .link-alt {
            text-align: center;
            margin-top: 1.5rem;
            color: #6c757d;
        }
        .link-alt a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: white;
            text-decoration: none;
            font-size: 0.95rem;
            opacity: 0.9;
            transition: opacity 0.2s ease;
            z-index: 1;
        }
        .back-link:hover {
            opacity: 1;
        }
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
                border-radius: 0;
            }
            .auth-left, .auth-right {
                padding: 2rem;
            }
            .logo {
                font-size: 2.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Panel: Background Image & Tagline -->
        <div class="auth-left">
            <a href="index.php" class="back-link">
                <i class="fas fa-arrow-left me-1"></i>Quay lại trang chủ
            </a>
            <div class="auth-left-content">
                <div class="logo">Bike<span>Market</span></div>
                <p class="tagline">BikeMarket — Khi phong cách không cần phô trương để nổi bật.</p>
            </div>
        </div>

        <!-- Right Panel: Form -->
        <div class="auth-right">
            <h1 class="auth-title">Đăng nhập</h1>
            <p class="auth-subtitle">Chào mừng trở lại! Vui lòng đăng nhập để tiếp tục.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-floating">
                    <input type="email" name="email" class="form-control" id="email" placeholder="Email" required autofocus value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    <label for="email">Email</label>
                </div>

                <div class="form-floating position-relative">
                    <input type="password" name="mat_khau" class="form-control" id="password" placeholder="Mật khẩu" required>
                    <label for="password">Mật khẩu</label>
                    <i class="fas fa-eye-slash password-toggle" onclick="togglePassword('password')"></i>
                </div>

                <button type="submit" class="btn btn-primary mb-3">Đăng nhập</button>
            </form>

            <div class="link-alt">
                Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
            </div>
            <div class="link-alt">
                <a href="forgot.php">Quên mật khẩu?</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const icon = input.nextElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>