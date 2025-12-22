<?php include 'includes/header.php'; ?>

<?php
if (!isset($_GET['id'])) {
    // Sửa chuyển hướng về đúng file danh sách
    header('Location: admin_contacts.php');
    exit();
}
$id = intval($_GET['id']);

// XỬ LÝ KHI ADMIN GỬI PHẢN HỒI
if (isset($_POST['btn_reply'])) {
    $phan_hoi = $conn->real_escape_string($_POST['phan_hoi']);
    
    $sql_update = "UPDATE lien_he SET
                phan_hoi = '$phan_hoi',
                trang_thai = 'Đã phản hồi'
                WHERE id = $id";
                
    if ($conn->query($sql_update)) {
        echo "<div class='alert alert-success'>Đã lưu phản hồi thành công!</div>";
    } else {
        echo "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}

$sql = "SELECT * FROM lien_he WHERE id = $id";
$msg = $conn->query($sql)->fetch_assoc();
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <a href="admin_contacts.php" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Quay lại</a>
        
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="m-0 font-weight-bold text-primary">Chi tiết liên hệ #<?php echo $id; ?></h5>
            </div>
            <div class="card-body">
                <div class="mb-4 p-3 bg-light rounded">
                    <p><strong>Người gửi:</strong> <?php echo htmlspecialchars($msg['ho_ten']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($msg['email']); ?></p>
                    <p><strong>Ngày gửi:</strong> <?php echo date('d/m/Y H:i', strtotime($msg['ngay_gui'])); ?></p>
                    <hr>
                    <p><strong>Nội dung:</strong></p>
                    <p class="fst-italic">"<?php echo nl2br(htmlspecialchars($msg['noi_dung'])); ?>"</p>
                </div>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phản hồi của Admin:</label>
                        <textarea name="phan_hoi" class="form-control" rows="5" placeholder="Nhập nội dung trả lời khách hàng tại đây..."><?php echo htmlspecialchars($msg['phan_hoi']); ?></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="btn_reply" class="btn btn-primary">
                            <i class="bi bi-send"></i> Lưu & Gửi Phản Hồi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>