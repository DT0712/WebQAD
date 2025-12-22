<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'config.php';

if (!isset($_SESSION['khach_hang'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['khach_hang'];
$id_kh = $user['id_khach_hang'];

/* ======================== XỬ LÝ CẬP NHẬT AVATAR ======================== */
// 1. Tải lên từ máy
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar_upload'])) {
    $target_dir = "uploads/avatars/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

    $file = $_FILES['avatar_upload'];
    $fileName = time() . '_' . basename($file["name"]);
    $target_file = $target_dir . $fileName;
    $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        echo "<script>alert('File không phải là ảnh!');</script>";
    } elseif ($file["size"] > 5000000) {
        echo "<script>alert('File quá lớn (tối đa 5MB)');</script>";
    } elseif (!in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        echo "<script>alert('Chỉ chấp nhận JPG, PNG, GIF, WEBP');</script>";
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $sql = "UPDATE khach_hang SET anh_dai_dien = ? WHERE id_khach_hang = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $target_file, $id_kh);
            $stmt->execute();

            $user['anh_dai_dien'] = $target_file;
            $_SESSION['khach_hang'] = $user;
            echo "<script>alert('Cập nhật avatar thành công!'); location.reload();</script>";
        }
    }
}

// 2. Chọn từ Unsplash (đã sửa lỗi)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_unsplash_avatar'])) {
    $url = filter_var($_POST['avatar_url'], FILTER_SANITIZE_URL);
    $sql = "UPDATE khach_hang SET anh_dai_dien = ? WHERE id_khach_hang = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $url, $id_kh);
    $stmt->execute();

    $user['anh_dai_dien'] = $url;
    $_SESSION['khach_hang'] = $user;
    echo "<script>alert('Đã thay đổi avatar!'); location.reload();</script>";
}

/* ======================== DỮ LIỆU CŨ ======================== */
$dob = '01/01/1990';
$address = ['country'=>'Việt Nam','city'=>'Hà Nội','street'=>'123 Đường ABC','postal'=>'10000'];

/* Đổi mật khẩu – giữ nguyên */
$password_success = $password_error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'change_password') {
    // (code đổi mật khẩu của bạn – giữ nguyên 100%)
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = 'Vui lòng nhập đầy đủ thông tin.';
    } elseif ($new_password !== $confirm_password) {
        $password_error = 'Mật khẩu mới và xác nhận không khớp.';
    } elseif (strlen($new_password) < 6) {
        $password_error = 'Mật khẩu mới phải ít nhất 6 ký tự.';
    } else {
        $sql_old = "SELECT mat_khau FROM khach_hang WHERE id_khach_hang = ?";
        $stmt_old = $conn->prepare($sql_old);
        $stmt_old->bind_param("i", $id_kh);
        $stmt_old->execute();
        $old_result = $stmt_old->get_result();
        if ($old_row = $old_result->fetch_assoc()) {
            if (password_verify($old_password, $old_row['mat_khau'])) {
                $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_update = "UPDATE khach_hang SET mat_khau = ? WHERE id_khach_hang = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $hashed_new, $id_kh);
                if ($stmt_update->execute()) {
                    $password_success = 'Cập nhật mật khẩu thành công!';
                } else {
                    $password_error = 'Lỗi cập nhật. Vui lòng thử lại.';
                }
            } else {
                $password_error = 'Mật khẩu cũ không đúng.';
            }
        }
    }
}

/* Đơn hàng */
$sql_orders = "SELECT dh.*, SUM(ctdh.so_luong * ctdh.don_gia) as tong_tien 
               FROM don_hang dh 
               JOIN chi_tiet_don_hang ctdh ON dh.id_don_hang = ctdh.id_don_hang 
               WHERE dh.id_khach_hang = ? 
               GROUP BY dh.id_don_hang 
               ORDER BY dh.ngay_dat DESC LIMIT 5";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $id_kh);
$stmt_orders->execute();
$orders = $stmt_orders->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản của tôi - Blank Label</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/Css/style.css">

    <style>
        :root {
            --primary: #00bcd4;
            --secondary: #0097a7;
            --light: #f8f9fa;
            --dark: #495057;
            --border: #dee2e6;
        }
        body {font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:#f8f9fa;color:var(--dark);}

        /* ==================== HEADER ĐẸP NHƯ ẢNH MẪU (TÍM → XANH DƯƠNG + BONG BÓNG) ==================== */
        .profile-header{
            position:relative;
            background:linear-gradient(135deg,#e91e63 0%,#9c27b0 35%,#673ab7 70%,#3f51b5 100%);
            color:white;
            padding:5.5rem 0 4.5rem;
            border-radius:24px;
            overflow:hidden;
            box-shadow:0 15px 40px rgba(103,58,183,.3);
        }
        .profile-header::before,.profile-header::after,.bubble{
            content:'';position:absolute;border-radius:50%;filter:blur(70px);opacity:.3;
        }
        .profile-header::before{width:450px;height:450px;background:#e91e63;top:-120px;left:-100px;}
        .profile-header::after{width:550px;height:550px;background:#3f51b5;bottom:-180px;right:-140px;}
        .bubble-1{width:220px;height:220px;background:rgba(255,255,255,.2);top:60px;right:80px;}
        .bubble-2{width:320px;height:320px;background:rgba(255,255,255,.15);bottom:100px;left:40px;}
        .bubble-3{width:180px;height:180px;background:rgba(255,255,255,.18);top:200px;left:180px;}

        .avatar-container{position:relative;display:inline-block;z-index:10;}
        .avatar{
            width:150px;height:150px;border-radius:50%;border:8px solid white;
            box-shadow:0 20px 50px rgba(0,0,0,.4);object-fit:cover;background:white;
            transition:all .4s ease;
        }
        .avatar:hover{transform:scale(1.08);}
        .change-avatar-btn{
            position:absolute;bottom:12px;right:12px;background:rgba(255,255,255,.95);
            color:#9c27b0;width:50px;height:50px;border-radius:50%;border:none;
            font-size:1.5rem;box-shadow:0 8px 25px rgba(0,0,0,.35);cursor:pointer;
            transition:all .4s ease;
        }
        .change-avatar-btn:hover{background:white;transform:scale(1.25);color:#e91e63;}

        .user-name{font-size:2.4rem;font-weight:800;margin:1.5rem 0 .6rem;text-shadow:0 4px 20px rgba(0,0,0,.6);z-index:10;position:relative;}
        .user-location{font-size:1.15rem;opacity:.95;z-index:10;position:relative;}

        /* ==================== GIỮ NGUYÊN 100% STYLE CŨ CỦA BẠN ==================== */
        .profile-card{background:white;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.1);border:1px solid var(--border);overflow:hidden;margin-bottom:1.5rem;}
        .section-header{background:var(--light);padding:1rem 1.25rem;border-bottom:1px solid var(--border);font-weight:600;color:var(--dark);font-size:1.1rem;}
        .info-grid{padding:1.5rem;}
        .info-row{display:flex;align-items:center;padding:.75rem 0;border-bottom:1px solid #f1f3f4;}
        .info-row:last-child{border-bottom:none;}
        .info-label{font-weight:500;color:var(--dark);min-width:120px;flex:0 0 120px;}
        .info-value{flex:1;color:#6c757d;}
        .edit-btn{background:var(--primary);color:white;border:none;border-radius:6px;padding:.375rem .75rem;font-size:.875rem;transition:background .2s ease;}
        .edit-btn:hover{background:var(--secondary);}
        .nav-link{color:var(--dark);padding:.875rem 1rem;transition:background .2s ease;}
        .nav-link:hover,.nav-link.active{background:var(--primary);color:white;}
        .tab-content{display:none;}
        .tab-content.active{display:block;}
        .empty-state{text-align:center;padding:2rem;color:#6c757d;}
        .empty-state i{font-size:2.5rem;margin-bottom:.5rem;opacity:.5;}
        @media (max-width:768px){
            .info-row{flex-direction:column;align-items:flex-start;}
            .info-label{min-width:auto;margin-bottom:.25rem;}
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container my-4">
    <div class="row">
        <div class="col-md-3">
            <div class="profile-card">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link active" href="#profile" onclick="switchTab('profile')"><i class="bi bi-person me-2"></i>Tài khoản</a></li>
                    <li class="nav-item"><a class="nav-link" href="#orders" onclick="switchTab('orders')"><i class="bi bi-bag me-2"></i>Đơn hàng</a></li>
                    <li class="nav-item"><a class="nav-link" href="#settings" onclick="switchTab('settings')"><i class="bi bi-gear me-2"></i>Cài đặt</a></li>
                </ul>
            </div>
        </div>

        <div class="col-md-9">
            <!-- HEADER SIÊU ĐẸP -->
            <div class="profile-card mb-4">
                <div class="profile-header text-center position-relative">
                    <div class="bubble bubble-1"></div>
                    <div class="bubble bubble-2"></div>
                    <div class="bubble bubble-3"></div>

                    <div class="avatar-container">
                        <?php if (!empty($user['anh_dai_dien'])): ?>
                            <img src="<?= htmlspecialchars($user['anh_dai_dien']) ?>" alt="Avatar" class="avatar">
                        <?php else: ?>
                            <div class="avatar d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-user text-primary fs-1"></i>
                            </div>
                        <?php endif; ?>
                        <button class="change-avatar-btn" data-bs-toggle="modal" data-bs-target="#avatarModal">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h2 class="user-name"><?= htmlspecialchars($user['ho_ten']) ?></h2>
                    <p class="user-location mb-0">Khách hàng thân thiết</p>
                </div>
            </div>

            <!-- ==================== TAB CŨ CỦA BẠN (giữ nguyên 100%) ==================== -->
            <div id="profile" class="tab-content active">
                <div class="profile-card">
                    <div class="section-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-person-badge me-2"></i>Thông tin cá nhân</span>
                        <button class="edit-btn" onclick="editPersonal()">Chỉnh sửa</button>
                    </div>
                    <div class="info-grid">
                        <div class="info-row"><span class="info-label">Họ và tên</span><span class="info-value"><?= htmlspecialchars($user['ho_ten']) ?></span></div>
                        <div class="info-row"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($user['email']) ?></span></div>
                        <div class="info-row"><span class="info-label">Ngày sinh</span><span class="info-value"><?= $dob ?></span></div>
                        <div class="info-row"><span class="info-label">Số điện thoại</span><span class="info-value"><?= htmlspecialchars($user['dien_thoai'] ?? 'Chưa cập nhật') ?></span></div>
                    </div>
                </div>

                <div class="profile-card">
                    <div class="section-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-geo-alt me-2"></i>Địa chỉ giao hàng</span>
                        <button class="edit-btn" onclick="editAddress()">Chỉnh sửa</button>
                    </div>
                    <div class="info-grid">
                        <div class="info-row"><span class="info-label">Quốc gia</span><span class="info-value"><?= $address['country'] ?></span></div>
                        <div class="info-row"><span class="info-label">Thành phố</span><span class="info-value"><?= $address['city'] ?></span></div>
                        <div class="info-row"><span class="info-label">Địa chỉ</span><span class="info-value"><?= $address['street'] ?></span></div>
                        <div class="info-row"><span class="info-label">Mã bưu điện</span><span class="info-value"><?= $address['postal'] ?></span></div>
                    </div>
                </div>
            </div>

            <!-- Orders Tab (giữ nguyên) -->
            <div id="orders" class="tab-content">
                <div class="profile-card">
                    <div class="section-header"><i class="bi bi-receipt me-2"></i>Đơn hàng gần đây</div>
                    <div class="info-grid">
                        <?php if (mysqli_num_rows($orders) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <thead><tr><th>Mã đơn</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
                                    <tbody>
                                        <?php while ($order = $orders->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?= $order['id_don_hang'] ?></td>
                                            <td><?= date('d/m/Y', strtotime($order['ngay_dat'])) ?></td>
                                            <td><?= number_format($order['tong_tien']) ?>₫</td>
                                            <td><span class="badge <?= $order['trang_thai']=='hoan_thanh'?'bg-success':($order['trang_thai']=='dang_giao'?'bg-warning':'bg-secondary') ?>">
                                                <?= ucfirst(str_replace('_',' ',$order['trang_thai'])) ?></span></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state"><i class="bi bi-bag-x"></i><h6>Chưa có đơn hàng</h6>
                                <a href="index.php" class="btn btn-primary mt-2">Mua sắm ngay</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Settings Tab (giữ nguyên) -->
            <div id="settings" class="tab-content">
                <div class="profile-card">
                    <div class="section-header"><i class="bi bi-shield-lock me-2"></i>Thay đổi mật khẩu</div>
                    <div class="info-grid">
                        <?php if ($password_success): ?><div class="alert alert-success"><?= $password_success ?></div><?php endif; ?>
                        <?php if ($password_error): ?><div class="alert alert-danger"><?= $password_error ?></div><?php endif; ?>
                        <form method="POST" class="p-3">
                            <input type="hidden" name="action" value="change_password">
                            <div class="row">
                                <div class="col-md-4 mb-3"><input type="password" name="old_password" class="form-control" placeholder="Mật khẩu cũ" required></div>
                                <div class="col-md-4 mb-3"><input type="password" name="new_password" class="form-control" placeholder="Mật khẩu mới" required></div>
                                <div class="col-md-4 mb-3"><input type="password" name="confirm_password" class="form-control" placeholder="Xác nhận" required></div>
                            </div>
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL THAY AVATAR – ĐÃ SỬA LỖI 100% -->
<div class="modal fade" id="avatarModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#9c27b0,#3f51b5);">
                <h5 class="modal-title fw-bold">Thay đổi ảnh đại diện</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">

                <!-- Tabs -->
                <ul class="nav nav-pills mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#upload">Tải lên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#library">Thư viện ảnh đẹp</a>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- TAB TẢI LÊN -->
                    <div class="tab-pane fade show active" id="upload">
                        <form method="POST" enctype="multipart/form-data" class="text-center">
                            <!-- ĐÂY LÀ DÒNG QUAN TRỌNG NHẤT -->
                            <input type="file" name="avatar_upload" accept="image/*" class="form-control form-control-lg" required>
                            <button type="submit" class="btn btn-primary btn-lg mt-3 px-5">
                                Tải lên & Đặt làm avatar
                            </button>
                        </form>
                    </div>

                    <!-- TAB THƯ VIỆN -->
                    <div class="tab-pane fade" id="library">
                        <div class="row g-4">
                            <?php
                            $unsplash_avatars = [
                                "https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=600&q=80",
                                "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=600&q=80",
                                "https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&q=80",
                                "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=600&q=80",
                                "https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=600&q=80",
                                "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=600&q=80",
                                "https://images.unsplash.com/photo-1539571696357-5a69c17a65c6?w=600&q=80",
                                "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&q=80"
                            ];
                            foreach ($unsplash_avatars as $img): ?>
                                <div class="col-6 col-md-4 col-lg-3">
                                    <form method="POST">
                                        <input type="hidden" name="set_unsplash_avatar" value="1">
                                        <input type="hidden" name="avatar_url" value="<?= $img ?>">
                                        <button type="submit" class="btn p-0 border-0 rounded-4 overflow-hidden shadow w-100">
                                            <img src="<?= $img ?>" class="img-fluid rounded-4" style="height:140px;object-fit:cover;">
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-4 text-muted small">Ảnh miễn phí từ Unsplash</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function switchTab(id){
        document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
        document.querySelectorAll('.nav-link').forEach(l=>l.classList.remove('active'));
        document.getElementById(id).classList.add('active');
        event.target.classList.add('active');
    }
    function editPersonal(){alert('Chỉnh sửa thông tin cá nhân');}
    function editAddress(){alert('Chỉnh sửa địa chỉ');}
</script>
</body>
</html>