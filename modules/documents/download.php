<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID tài liệu không hợp lệ!");
}

$id = (int)$_GET['id'];

// Kiểm tra quyền truy cập
$sql = "SELECT t.*, pq.Quyen 
        FROM tailieu t
        JOIN phanquyentailieu pq ON t.Id = pq.TaiLieuId
        JOIN vaitronguoidung vn ON pq.VaiTroId = vn.VaiTroId
        WHERE t.Id = ? AND vn.NguoiDungId = ? AND pq.Quyen >= 2";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $_SESSION['user']['id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Bạn không có quyền tải xuống tài liệu này!");
}

$document = $result->fetch_assoc();
$filePath = $document['DuongDan'];

if (!file_exists($filePath)) {
    die("File không tồn tại!");
}

// Log hoạt động download
$stmt = $conn->prepare("INSERT INTO nhatkyhoatdong (IP, NguoiDung, HanhDong, ChiTiet) VALUES (?, ?, ?, ?)");
$ip = $_SERVER['REMOTE_ADDR'];
$user = $_SESSION['user']['name'];
$action = "Download tài liệu";
$detail = "Tải xuống tài liệu: " . $document['TenTaiLieu'];
$stmt->bind_param("ssss", $ip, $user, $action, $detail);
$stmt->execute();

// Trả về file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit; 