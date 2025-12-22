<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý Liên hệ</h2>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Khách hàng</th>
                        <th>Nội dung tóm tắt</th>
                        <th>Ngày gửi</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM lien_he ORDER BY id DESC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_class = ($row['trang_thai'] == 'Đã phản hồi') ? 'bg-success' : 'bg-warning text-dark';
                            
                            echo "<tr>";
                            echo "<td>#" . $row['id'] . "</td>";
                            echo "<td>
                                    <strong>" . htmlspecialchars($row['ho_ten']) . "</strong><br>
                                    <small class='text-muted'>" . htmlspecialchars($row['email']) . "</small>
                                </td>";
                            echo "<td>" . mb_substr(htmlspecialchars($row['noi_dung']), 0, 50) . "...</td>";
                            echo "<td>" . date('d/m/Y H:i', strtotime($row['ngay_gui'])) . "</td>";
                            echo "<td><span class='badge $status_class'>" . $row['trang_thai'] . "</span></td>";
                            echo "<td>
                                    <a href='admin_contact_detail.php?id=" . $row['id'] . "' class='btn btn-sm btn-info text-white'>
                                        <i class='bi bi-reply'></i> Xem & Trả lời
                                    </a>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>Chưa có liên hệ nào.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>