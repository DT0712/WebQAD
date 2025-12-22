<?php include 'includes/header.php'; ?>

<?php
if (!isset($_GET['id'])) {
    header('Location: admin_orders.php');
    exit();
}
$order_id = intval($_GET['id']);

// XỬ LÝ CẬP NHẬT TRẠNG THÁI
if (isset($_POST['update_status'])) {
    $status = $_POST['trang_thai'];
    // SỬA: WHERE id_don_hang
    $conn->query("UPDATE don_hang SET trang_thai = '$status' WHERE id_don_hang = $order_id");
    echo "<div class='alert alert-success'>Đã cập nhật trạng thái đơn hàng!</div>";
}

// Lấy thông tin đơn hàng
// SỬA: WHERE id_don_hang
$sql_order = "SELECT * FROM don_hang WHERE id_don_hang = $order_id";
$order = $conn->query($sql_order)->fetch_assoc();
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white">
                <h6 class="m-0 font-weight-bold text-primary">Thông tin khách hàng</h6>
            </div>
            <div class="card-body">
                <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['ho_ten']); ?></p>
                <p><strong>SĐT:</strong> <?php echo htmlspecialchars($order['dien_thoai']); ?></p>
                <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['dia_chi']); ?></p>
                <p><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['ngay_dat'])); ?></p>
                <p><strong>Ghi chú:</strong> <?php echo htmlspecialchars($order['ghi_chu'] ?? 'Không có'); ?></p>
                
                <hr>
                <form method="POST">
                    <label class="form-label fw-bold">Trạng thái đơn hàng:</label>
                    <select name="trang_thai" class="form-select mb-3">
                        <option value="Chờ xác nhận" <?php if($order['trang_thai']=='Chờ xác nhận') echo 'selected'; ?>>Chờ xác nhận</option>
                        <option value="Đang xử lý" <?php if($order['trang_thai']=='Đang xử lý') echo 'selected'; ?>>Đang xử lý</option>
                        <option value="Đang giao hàng" <?php if($order['trang_thai']=='Đang giao hàng') echo 'selected'; ?>>Đang giao hàng</option>
                        <option value="Đã giao" <?php if($order['trang_thai']=='Đã giao') echo 'selected'; ?>>Đã giao</option>
                        <option value="Hủy" <?php if($order['trang_thai']=='Hủy') echo 'selected'; ?>>Hủy</option>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-primary w-100">Cập nhật trạng thái</button>
                </form>
            </div>
        </div>
        <a href="admin_orders.php" class="btn btn-secondary w-100"><i class="bi bi-arrow-left"></i> Quay lại danh sách</a>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h6 class="m-0 font-weight-bold text-primary">Sản phẩm trong đơn hàng #<?php echo $order_id; ?></h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Lấy chi tiết đơn hàng
                        // SỬA: WHERE c.id_don_hang
                        $sql_detail = "SELECT c.*, s.ten_san_pham, s.link_anh
                                    FROM chi_tiet_don_hang c
                                    JOIN san_pham s ON c.id_san_pham = s.id_san_pham
                                    WHERE c.id_don_hang = $order_id";
                        
                        if ($conn->query($sql_detail)) {
                            $res_detail = $conn->query($sql_detail);
                            while ($item = $res_detail->fetch_assoc()) {
                                // SỬA: $item['don_gia'] thay vì ['gia']
                                $subtotal = $item['don_gia'] * $item['so_luong'];
                                echo "<tr>";
                                echo "<td>
                                        <div class='d-flex align-items-center'>
                                            <img src='../assets/images/" . $item['link_anh'] . "' width='40' class='me-2 rounded'>
                                            " . htmlspecialchars($item['ten_san_pham']) . "
                                        </div>
                                    </td>";
                                // SỬA: don_gia
                                echo "<td>" . number_format($item['don_gia']) . " đ</td>";
                                echo "<td class='text-center'>" . $item['so_luong'] . "</td>";
                                echo "<td class='fw-bold'>" . number_format($subtotal) . " đ</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>Chưa có dữ liệu chi tiết</td></tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Tổng cộng:</td>
                            <td class="fw-bold text-danger fs-5"><?php echo number_format($order['tong_tien']); ?> đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>