<?php
// Bắt đầu session ngay đầu file để lưu thông báo
session_start();
include 'config.php';

// --- XỬ LÝ GỬI LIÊN HỆ ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lấy dữ liệu và làm sạch
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);

    // Chèn vào Database
    $sql = "INSERT INTO lien_he (ho_ten, email, noi_dung) VALUES ('$name', '$email', '$message')";

    if ($conn->query($sql) === TRUE) {
        // LƯU THÔNG BÁO VÀO SESSION
        $_SESSION['flash_message'] = '
        <div class="alert alert-success text-center my-3" role="alert">
            <h4 class="alert-heading">Cảm ơn bạn!</h4>
            <p>BlankLabel cảm ơn phản hồi của bạn, chúng tôi sẽ cố gắng liên hệ tới bạn sớm nhất có thể.</p>
        </div>';
        
        // CHUYỂN HƯỚNG TRANG (Để tránh lỗi Confirm Form Resubmission)
        header("Location: contact.php");
        exit(); // Dừng code ngay sau khi chuyển hướng
    } else {
        // Nếu lỗi thì hiện luôn, không cần redirect
        $error_message = '<div class="alert alert-danger">Lỗi: ' . $conn->error . '</div>';
    }
}

// Bắt đầu load giao diện HTML
include 'includes/header.php'; 
?>

<div class="container my-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb"></ol>
    </nav>

    <div class="row">
        <?php 
        // 1. HIỂN THỊ THÔNG BÁO THÀNH CÔNG (Lấy từ Session)
        if (isset($_SESSION['flash_message'])) {
            echo $_SESSION['flash_message'];
            unset($_SESSION['flash_message']); // Xóa ngay sau khi hiện để F5 không hiện lại
        }

        // 2. HIỂN THỊ LỖI (Nếu có)
        if (isset($error_message)) {
            echo $error_message;
        }
        ?>

        <div class="col-md-6 mb-4">
            <h2 class="mb-4">Liên hệ với chúng tôi</h2>
            <p class="text-muted">Bạn hãy điền nội dung tin nhắn vào form dưới đây và gửi cho chúng tôi.</p>
            
            <form action="contact.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Họ và tên *</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email *</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Nội dung *</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-info text-white">GỬI TIN NHẮN</button>
            </form>
        </div>
        
        <div class="col-md-6">
            <div class="contact-info p-4">
                <h3 class="mb-4">Thông tin liên hệ</h3>
                <p><i class="bi bi-geo-alt"></i> 2 Võ Oanh, Phường 25, Bình Thạnh, Thành phố Hồ Chí Minh </p>
                <p><i class="bi bi-telephone"></i> 1900 xxxx</p>
                <p><i class="bi bi-envelope"></i> blankLabel@gmail.com</p>
            </div>
            <div class="mt-4">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.990412809715!2d106.7144933758411!3d10.804921158588502!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317528a2a0142385%3A0x4231b1a7d1b8c1d!2zMiBWw7UgT2FuaCwgUGjGsOG7nW5nIDI1LCBCw6xuaCBUaOG6oW5oLCBUaMOgbmggcGjhu5EgSOG7kyBDaMOtIE1pbmgsIFZp4buHdE5hbQ!5e0!3m2!1svi!2s!4v1730095165997!5m2!1svi!2s" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 