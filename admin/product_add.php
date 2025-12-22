<?php include 'includes/header.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ten_sp = $_POST['ten_san_pham'];
    $gia = $_POST['gia'];
    $danh_muc = $_POST['id_danh_muc'];
    $mo_ta = $_POST['mo_ta'];
    
    $target_dir = "../Web_TMDT/assets/images/";
    $img_name = basename($_FILES["hinh_anh"]["name"]);
    $target_file = $target_dir . $img_name;
    
    if (move_uploaded_file($_FILES["hinh_anh"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO san_pham (ten_san_pham, gia, id_danh_muc, link_anh) VALUES ('$ten_sp', '$gia', '$danh_muc', '$img_name')";
        
        if ($conn->query($sql) === TRUE) {
            // Sửa đường dẫn quay lại
            echo "<div class='alert alert-success'>Thêm sản phẩm thành công! <a href='admin_products.php'>Quay lại danh sách</a></div>";
        } else {
            echo "<div class='alert alert-danger'>Lỗi SQL: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Lỗi upload ảnh.</div>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white">
                <h5 class="m-0 font-weight-bold text-primary">Thêm Sản Phẩm Mới</h5>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm</label>
                        <input type="text" name="ten_san_pham" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Giá (VNĐ)</label>
                            <input type="number" name="gia" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Danh mục</label>
                            <select name="id_danh_muc" class="form-select">
                                <?php
                                $cat_res = $conn->query("SELECT * FROM danh_muc");
                                while($c = $cat_res->fetch_assoc()){
                                    // Sửa lại cho phù hợp DB: ưu tiên id, nếu ko có thì lấy id_danh_muc
                                    $val = isset($c['id']) ? $c['id'] : $c['id_danh_muc'];
                                    echo "<option value='".$val."'>".$c['ten_danh_muc']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Hình ảnh</label>
                        <input type="file" name="hinh_anh" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea name="mo_ta" class="form-control" rows="4"></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                        <a href="admin_products.php" class="btn btn-light">Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>