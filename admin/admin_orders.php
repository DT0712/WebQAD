<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý Đơn hàng</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Mã ĐH</th>
                        <th>Khách hàng</th>
                        <th>Ngày đặt</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // SỬA: Sắp xếp theo id_don_hang
                    $sql = "SELECT * FROM don_hang ORDER BY id_don_hang DESC";
                    
                    if ($conn->query($sql)) {
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status_color = 'bg-secondary';
                                if ($row['trang_thai'] == 'Đang xử lý') $status_color = 'bg-warning text-dark';
                                if ($row['trang_thai'] == 'Đã giao') $status_color = 'bg-success';
                                if ($row['trang_thai'] == 'Hủy') $status_color = 'bg-danger';

                                echo "<tr>";
                                // SỬA: id -> id_don_hang
                                echo "<td>#" . $row['id_don_hang'] . "</td>";
                                echo "<td>
                                        <strong>" . htmlspecialchars($row['ho_ten']) . "</strong><br>
                                        <small class='text-muted'>" . htmlspecialchars($row['dien_thoai']) . "</small>
                                    </td>";
                                echo "<td>" . date('d/m/Y', strtotime($row['ngay_dat'])) . "</td>";
                                echo "<td class='fw-bold text-primary'>" . number_format($row['tong_tien']) . " đ</td>";
                                echo "<td><span class='badge $status_color'>" . $row['trang_thai'] . "</span></td>";
                                echo "<td>
                                        <a href='admin_order_detail.php?id=" . $row['id_don_hang'] . "' class='btn btn-sm btn-info text-white'><i class='bi bi-eye'></i> Xem</a>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Chưa có đơn hàng nào</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-danger'>Lỗi: Chưa có bảng 'don_hang' trong CSDL</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>