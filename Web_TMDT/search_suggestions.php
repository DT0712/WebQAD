<?php
include 'config.php'; // Giả sử bạn có file config.php kết nối DB

header('Content-Type: application/json');

$query = $_GET['query'] ?? '';
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id_san_pham AS id, ten_san_pham AS name, gia AS price, link_anh AS image 
        FROM san_pham 
        WHERE ten_san_pham LIKE ? 
        LIMIT 5";
$stmt = $conn->prepare($sql);
$likeQuery = "%$query%";
$stmt->bind_param("s", $likeQuery);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>