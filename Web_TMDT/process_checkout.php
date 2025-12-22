<?php
session_start();
include 'config.php';

// === BƯỚC 1: KIỂM TRA ĐĂNG NHẬP + GIỎ HÀNG ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST' 
    || !isset($_SESSION['khach_hang']) 
    || !isset($_SESSION['cart']) 
    || empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$kh_id       = (int)$_SESSION['khach_hang']['id_khach_hang'];
$ho_ten      = trim($_POST['ho_ten']);
$dien_thoai  = trim($_POST['dien_thoai']);
$email       = trim($_POST['email'] ?? '');
$dia_chi     = trim($_POST['dia_chi']);
$ghi_chu     = trim($_POST['ghi_chu'] ?? '');
$phuong_thuc = $_POST['phuong_thuc']; // cod hoặc bank

// === BƯỚC 2: TÍNH LẠI TỔNG TIỀN CHÍNH XÁC TỪ DB (CHỐNG SỬA GIÁ) ===
$tong_tien = 0;
foreach ($_SESSION['cart'] as $item) {
    $id_sp    = (int)$item['id'];
    $so_luong = (int)$item['quantity'];

    $sql = "SELECT gia FROM san_pham WHERE id_san_pham = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) continue;
    $stmt->bind_param("i", $id_sp);
    $stmt->execute();
    $result = $stmt->get_result();
    $gia = $result->num_rows > 0 ? $result->fetch_assoc()['gia'] : 0;
    $stmt->close();

    $tong_tien += $gia * $so_luong;
}

// === BƯỚC 3: TẠO ĐƠN HÀNG – CÂU SQL CHUẨN 100% ===
$sql = "INSERT INTO don_hang 
        (id_khach_hang, ho_ten, dien_thoai, email, dia_chi, ghi_chu, phuong_thuc_thanh_toan, tong_tien, trang_thai, ngay_dat) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'cho_xac_nhan', NOW())";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Lỗi chuẩn bị câu lệnh SQL (don_hang): " . $conn->error);
}

// Đúng thứ tự + đúng kiểu dữ liệu
$stmt->bind_param(
    "isssssid", 
    $kh_id,          // i
    $ho_ten,         // s
    $dien_thoai,     // s
    $email,          // s
    $dia_chi,        // s
    $ghi_chu,        // s
    $phuong_thuc,    // s
    $tong_tien       // d
);

if (!$stmt->execute()) {
    die("Lỗi khi lưu đơn hàng: " . $stmt->error);
}

$don_hang_id = $conn->insert_id;
$stmt->close();

// === BƯỚC 4: THÊM CHI TIẾT ĐƠN HÀNG ===
foreach ($_SESSION['cart'] as $item) {
    $id_sp    = (int)$item['id'];
    $so_luong = (int)$item['quantity'];

    // Lấy lại giá chính xác
    $gia_result = $conn->query("SELECT gia FROM san_pham WHERE id_san_pham = $id_sp LIMIT 1");
    $gia = $gia_result && $gia_result->num_rows > 0 ? $gia_result->fetch_assoc()['gia'] : 0;

    $sql_detail = "INSERT INTO chi_tiet_don_hang (id_don_hang, id_san_pham, so_luong, don_gia) 
                   VALUES (?, ?, ?, ?)";
    $stmt_detail = $conn->prepare($sql_detail);
    if ($stmt_detail) {
        $stmt_detail->bind_param("iiid", $don_hang_id, $id_sp, $so_luong, $gia);
        $stmt_detail->execute();
        $stmt_detail->close();
    }
}

// === BƯỚC 5: XÓA GIỎ HÀNG ===
unset($_SESSION['cart']);

// === BƯỚC 6: CHUYỂN HƯỚNG ĐẾN TRANG CẢM ƠN ===
header("Location: thank_you.php?order=" . $don_hang_id);
exit;
?>