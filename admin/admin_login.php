<?php
session_start();
include '../Web_TMDT/config.php';

// Nếu đã đăng nhập thì chuyển thẳng vào admin_index.php
if (isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM admins WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['admin'] = $row['username'];
            $_SESSION['admin_name'] = $row['ho_ten'];
            $_SESSION['admin_id'] = $row['id'];
            
            // Đã sửa: Chuyển hướng vào admin_index.php
            header('Location: index.php');
            exit();
        } else {
            $error = "Mật khẩu không đúng!";
        }
    } else {
        $error = "Tên đăng nhập không tồn tại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #d3d3d3a2; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 10px; }
        .brand-text span { color: #00bcd4; }
    </style>
</head>
<body>
    <div class="card login-card shadow-lg">
        <div class="card-body p-5">
            <h3 class="text-center mb-4 fw-bold brand-text">Blank<span>Label</span> Admin</h3>
            
            <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Tên đăng nhập</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Mật khẩu</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-info text-white w-100 fw-bold">ĐĂNG NHẬP</button>
            </form>
            <div class="text-center mt-3">
                <a href="../index.php" class="text-muted small text-decoration-none"><i class="bi bi-arrow-left"></i> Về trang chủ Website</a>
            </div>
        </div>
    </div>
</body>
</html>