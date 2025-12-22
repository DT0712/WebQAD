<?php
session_start();
include 'config.php';
include 'includes/header.php';

if (!isset($_SESSION['khach_hang'])) {
    header("Location: login.php");
    exit;
}

// 1. Doanh thu 7 ngày gần nhất
$doanhthu_query = mysqli_query($conn, "
    SELECT DATE(ngay_dat) AS ngay, SUM(tong_tien) AS doanhthu 
    FROM don_hang 
    WHERE ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(ngay_dat) 
    ORDER BY ngay_dat
");

// 2. Tổng quan tháng này
$thongke = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        COUNT(*) AS tong_don,
        COALESCE(SUM(tong_tien), 0) AS doanhthu_thang
    FROM don_hang 
    WHERE MONTH(ngay_dat) = MONTH(CURDATE()) 
      AND YEAR(ngay_dat)  = YEAR(CURDATE())
"));

// 3. Top 8 sản phẩm bán chạy
$top_query = mysqli_query($conn, "
    SELECT sp.ten_san_pham, sp.link_anh, sp.gia, SUM(ct.so_luong) AS sl_ban
    FROM chi_tiet_don_hang ct
    JOIN san_pham sp ON ct.id_san_pham = sp.id_san_pham
    GROUP BY ct.id_san_pham
    ORDER BY sl_ban DESC LIMIT 8
");

$top_labels = $top_data = $top_images = $top_prices = [];
$colors = ['#ff6b6b','#4ecdc4','#45b7d1','#96ceb4','#feca57','#ff9ff3','#54a0ff','#c44569'];

while ($row = mysqli_fetch_assoc($top_query)) {
    $top_labels[] = $row['ten_san_pham'];
    $top_data[]   = (int)$row['sl_ban'];
    $anh = !empty($row['link_anh']) ? 'assets/images/'.$row['link_anh'] : 'assets/images/no-image.jpg';
    $top_images[] = $anh;
    $top_prices[] = (float)$row['gia'];
}

// 4. Trạng thái đơn hàng
$tt = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT 
        SUM(CASE WHEN trang_thai = 'cho_xac_nhan' THEN 1 ELSE 0 END) AS cho,
        SUM(CASE WHEN trang_thai = 'da_xac_nhan'  THEN 1 ELSE 0 END) AS xacnhan,
        SUM(CASE WHEN trang_thai = 'dang_giao'    THEN 1 ELSE 0 END) AS giao,
        SUM(CASE WHEN trang_thai = 'hoan_thanh'   THEN 1 ELSE 0 END) AS hoanthanh,
        SUM(CASE WHEN trang_thai = 'da_huy'       THEN 1 ELSE 0 END) AS huy
    FROM don_hang
"));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thống Kê Doanh Thu - Shop Thời Trang</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <style>
        .card { border-radius: 20px; transition: all 0.3s; overflow: hidden; }
        .card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px rgba(0,0,0,0.15)!important; }
        .bg-grad-1 { background: linear-gradient(135deg, #667eea, #764ba2); }
        .bg-grad-2 { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .bg-grad-3 { background: linear-gradient(135deg, #4facfe, #00f2fe); }
    </style>
</head>
<body>

<div class="container py-5">
    <h2 class="text-center mb-5 fw-bold text-success">
        THỐNG KÊ DOANH THU & SẢN PHẨM
    </h2>

    <!-- Tổng quan nhanh -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-lg text-white bg-grad-1">
                <div class="card-body text-center py-5">
                    <h5><i class="bi bi-cart-check fs-1"></i><br>Tổng đơn hàng tháng này</h5>
                    <h2 class="fw-bold display-5"><?= number_format($thongke['tong_don']) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-lg text-white bg-grad-2">
                <div class="card-body text-center py-5">
                    <h5><i class="bi bi-currency-dollar fs-1"></i><br>Doanh thu tháng này</h5>
                    <h2 class="fw-bold display-5"><?= number_format($thongke['doanhthu_thang']) ?>đ</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-lg text-white bg-grad-3">
                <div class="card-body text-center py-5">
                    <h5><i class="bi bi-truck fs-1"></i><br>Đơn chờ xử lý</h5>
                    <h2 class="fw-bold display-5">
                        <?= mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS cho FROM don_hang WHERE trang_thai='cho_xac_nhan'"))['cho'] ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Dòng 1: Doanh thu 7 ngày + Trạng thái đơn hàng -->
    <div class="row g-5 mb-5">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-success text-white text-center py-4">
                    <h4 class="mb-0">Doanh Thu 7 Ngày Gần Nhất</h4>
                </div>
                <div class="card-body">
                    <canvas id="doanhThuChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-header bg-warning text-dark text-center py-4">
                    <h4 class="mb-0">Tỷ Lệ Trạng Thái Đơn Hàng</h4>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <canvas id="trangThaiChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Dòng 2: Top 8 sản phẩm -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header text-white text-center py-4" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <h4 class="mb-0">TOP 5 SẢN PHẨM BÁN CHẠY NHẤT</h4>
                    <small>Hot nhất tháng <?= date('m/Y') ?></small>
                </div>
                <div class="card-body p-4">
                    <canvas id="topSanPhamChart" height="480"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 1. Doanh thu 7 ngày
new Chart(document.getElementById('doanhThuChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column(mysqli_fetch_all($doanhthu_query), 0)) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode(array_column(mysqli_fetch_all(mysqli_query($conn, "
                SELECT COALESCE(SUM(tong_tien),0) 
                FROM don_hang 
                WHERE DATE(ngay_dat) >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY DATE(ngay_dat) ORDER BY ngay_dat
            ")), 0)) ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40,167,69,0.15)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#28a745',
            pointRadius: 7
        }]
    },
    options: { responsive: true }
});

// 2. Pie trạng thái đơn
new Chart(document.getElementById('trangThaiChart'), {
    type: 'pie',
    data: {
        labels: ['Chờ xác nhận','Đã xác nhận','Đang giao','Hoàn thành','Đã hủy'],
        datasets: [{
            data: [<?= $tt['cho']?>,<?= $tt['xacnhan']?>,<?= $tt['giao']?>,<?= $tt['hoanthanh']?>,<?= $tt['huy']?>],
            backgroundColor: ['#ffc107','#28a745','#007bff','#20c997','#dc3545'],
            borderWidth: 4,
            borderColor: '#fff'
        }]
    }
});

// ⭐⭐⭐ 3. TOP 8 SẢN PHẨM — ĐÃ SỬA FULL CĂN GIỮA ⭐⭐⭐
const topCtx = document.getElementById('topSanPhamChart').getContext('2d');
new Chart(topCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($top_labels) ?>,
        datasets: [{
            label: 'Số lượng bán',
            data: <?= json_encode($top_data) ?>,
            backgroundColor: <?= json_encode($colors) ?>.map(c => c + 'cc'),
            borderColor: <?= json_encode($colors) ?>,
            borderWidth: 2,
            borderRadius: 12,
            borderSkipped: false,
            barThickness: 42,
            maxBarThickness: 46
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,

        layout: { padding: { left: 140, right: 40, top: 20, bottom: 20 } },

        plugins: {
            legend: { display: false },
            datalabels: {
                anchor: 'end', align: 'end', color: '#1a1a1a',
                font: { weight: 'bold', size: 16 },
                formatter: v => v + ' cái',
                padding: 8,
                backgroundColor: 'rgba(255,255,255,0.95)',
                borderRadius: 8,
                borderWidth: 1,
                borderColor: '#ddd'
            }
        },

        scales: {
            x: { beginAtZero: true, grid: { display: false }, ticks: { display: false } },

            y: {
                grid: { display: false },
                ticks: {
                    padding: 20,
                    font: { size: 16, weight: '600' },
                    color: '#2c3e50',
                    textAlign: 'center',  
                    callback: function(value) {
                        const label = this.getLabelForValue(value);
                        return label.length > 28 ? label.slice(0,28)+"..." : label;
                    }
                }
            }
        },

// Thay thế toàn bộ phần "animation" cũ bằng đoạn này
animation: {
    onComplete() {
        const chart = this;
        const ctx = chart.ctx;
        const images = <?= json_encode($top_images) ?>; // mảng đường dẫn ảnh
        const meta = chart.getDatasetMeta(0);

        // Kích thước avatar
        const size = 52;
        // Tọa độ X cơ bản để vẽ (bên trái nhãn)
        const baseX = chart.scales.y.left - 85;

        // Clear vùng overlay (mở rộng một chút để chắc chắn)
        // Điều chỉnh clear region nếu cần (không xóa phần chart)
        ctx.clearRect(baseX - 200, 0, 300, chart.height);

        // Vẽ mỗi avatar đúng 1 lần (không animation)
        meta.data.forEach((bar, index) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.src = images[index];

            img.onload = () => {
                const y = Math.round(bar.y - size / 2);
                const x = Math.round(baseX);

                // Vẽ vòng tròn clip => vẽ ảnh => vẽ viền
                ctx.save();

                // tạo vùng tròn để clip ảnh (đảm bảo không bị thò ra)
                ctx.beginPath();
                ctx.arc(x + size/2, y + size/2, size/2, 0, Math.PI * 2);
                ctx.closePath();
                ctx.clip();

                // vẽ ảnh khít vùng clip
                ctx.drawImage(img, x, y, size, size);

                ctx.restore();

                // vẽ viền trắng
                ctx.beginPath();
                ctx.arc(x + size/2, y + size/2, size/2 - 1.5, 0, Math.PI * 2);
                ctx.lineWidth = 3;
                ctx.strokeStyle = "#ffffff";
                ctx.stroke();
            };

            // Trong trường hợp img không load được (404), vẽ placeholder màu
            img.onerror = () => {
                const y = Math.round(bar.y - size / 2);
                const x = Math.round(baseX);

                ctx.save();
                ctx.beginPath();
                ctx.arc(x + size/2, y + size/2, size/2, 0, Math.PI * 2);
                ctx.closePath();
                ctx.fillStyle = '#ddd';
                ctx.fill();
                ctx.restore();

                ctx.beginPath();
                ctx.arc(x + size/2, y + size/2, size/2 - 1.5, 0, Math.PI * 2);
                ctx.lineWidth = 3;
                ctx.strokeStyle = "#ffffff";
                ctx.stroke();
            };
        });
    }
}

    },
    plugins: [ChartDataLabels]
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
