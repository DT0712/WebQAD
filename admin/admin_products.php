<?php include 'includes/header.php'; ?>

<?php
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $sql = "DELETE FROM san_pham WHERE id_san_pham = $id";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success'>Đã xóa sản phẩm thành công!</div>";
    } else {
        echo "<div class='alert alert-danger'>Lỗi: " . $conn->error . "</div>";
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Quản lý Sản phẩm</h2>
    <a href="product_add.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Thêm sản phẩm</a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá tiền</th>
                        <th>Danh mục</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM san_pham ORDER BY id_san_pham DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>#" . $row['id_san_pham'] . "</td>";
                            echo "<td>
        <img src='/WebQA/Web_TMDT/assets/images/" . htmlspecialchars($row['link_anh']) . "' 
             width='50' 
             class='rounded border img-fluid'
             alt='" . htmlspecialchars($row['ten_san_pham']) . "'
             onerror=\"this.src='/WebQA/Web_TMDT/assets/images/no-image.jpg'\">
      </td>";
                            echo "<td style='max-width:300px;'>" . htmlspecialchars($row['ten_san_pham']) . "</td>";
                            echo "<td class='fw-bold text-success'>" . number_format($row['gia']) . " đ</td>";
                            echo "<td>" . $row['id_danh_muc'] . "</td>";
                            echo "<td>
                                    <a href='product_edit.php?id=" . $row['id_san_pham'] . "' class='btn btn-sm btn-warning text-white me-1'><i class='bi bi-pencil-square'></i></a>
                                    <a href='admin_products.php?delete_id=" . $row['id_san_pham'] . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Bạn có chắc muốn xóa sản phẩm này?');\"><i class='bi bi-trash'></i></a>
                                </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>Chưa có sản phẩm nào</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>