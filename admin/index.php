<?php include 'includes/header.php'; ?>

<?php
// --- 1. LẤY SỐ LIỆU TỪ DATABASE ---

// Đếm tổng sản phẩm
$sql_sp = "SELECT COUNT(*) as total FROM san_pham";
$res_sp = $conn->query($sql_sp);
$count_sp = $res_sp->fetch_assoc()['total'];

// Đếm tổng danh mục
$sql_dm = "SELECT COUNT(*) as total FROM danh_muc";
$res_dm = $conn->query($sql_dm);
$count_dm = $res_dm->fetch_assoc()['total'];

// Đếm tổng người dùng
$sql_user = "SELECT COUNT(*) as total FROM nguoi_dung"; // Giả sử bạn có bảng nguoi_dung
$res_user = ($conn->query($sql_user)) ? $conn->query($sql_user)->fetch_assoc()['total'] : 0;

// Tính tổng doanh thu (Giả sử có bảng don_hang)
// $sql_tien = "SELECT SUM(tong_tien) as total FROM don_hang";
// ... (code tương tự) ... tạm thời để số cứng demo
$total_revenue = 173850000;
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Sản phẩm</h6>
                    <h2 class="mb-0"><?php echo $count_sp; ?></h2>
                </div>
                <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Danh mục</h6>
                    <h2 class="mb-0"><?php echo $count_dm; ?></h2>
                </div>
                <div class="stat-icon"><i class="bi bi-tags"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-dark p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Khách hàng</h6>
                    <h2 class="mb-0"><?php echo $res_user; ?></h2>
                </div>
                <div class="stat-icon"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-danger text-white p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="text-uppercase mb-1">Doanh thu</h6>
                    <h4 class="mb-0"><?php echo number_format($total_revenue); ?>đ</h4>
                </div>
                <div class="stat-icon"><i class="bi bi-currency-dollar"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Biểu đồ doanh thu 6 tháng</h6>
            </div>
            <div class="card-body">
                <canvas id="myChart" height="150"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h6 class="m-0 font-weight-bold text-primary">Sản phẩm mới thêm</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Tên SP</th>
                            <th>Giá</th>
                            <th>Hình</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Lấy 5 sản phẩm mới nhất
                        $sql_new = "SELECT * FROM san_pham ORDER BY id_san_pham DESC LIMIT 5";
                        $res_new = $conn->query($sql_new);
                        while ($row = $res_new->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . mb_substr($row['ten_san_pham'], 0, 25, 'UTF-8') . '...</td>';
                            echo '<td>' . number_format($row['gia']) . 'đ</td>';
                            
                            echo "<td>
        <img src='/WebQA/Web_TMDT/assets/images/" . htmlspecialchars($row['link_anh']) . "' 
             width='30' 
             class='rounded border img-fluid'
             alt='" . htmlspecialchars($row['ten_san_pham']) . "'
             onerror=\"this.src='/WebQA/Web_TMDT/assets/images/no-image.jpg'\">
      </td>";
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar', // Loại biểu đồ: bar (cột), line (đường), pie (tròn)
        data: {
            labels: ['Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: [20000000, 30000000, 25000000, 40000000, 35000000, 23850000], // Dữ liệu giả định
                backgroundColor: '#00bcd4',
                borderColor: '#008ba3',
                borderWidth: 1
            }]
        },
        options: { scales: { y: { beginAtZero: true } } }
    });
</script>

<?php include 'includes/footer.php'; ?>