<?php
require_once 'includes/auth.php';

$conditions = [];
$params = [];
$types = "";

if (isset($_GET['keyword'])) {
    $keyword = "%" . validateInput($_GET['keyword']) . "%";
    $conditions[] = "(hd.TenHoatDong LIKE ? OR hd.MoTa LIKE ?)";
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= "ss";
}

if (isset($_GET['start_date'])) {
    $startDate = validateInput($_GET['start_date']);
    $conditions[] = "hd.NgayBatDau >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if (isset($_GET['end_date'])) {
    $endDate = validateInput($_GET['end_date']);
    $conditions[] = "hd.NgayKetThuc <= ?";
    $params[] = $endDate;
    $types .= "s";
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = (int)$_GET['status'];
    $conditions[] = "hd.TrangThai = ?";
    $params[] = $status;
    $types .= "i";
}

$sql = "SELECT hd.*, COUNT(DISTINCT dk.NguoiDungId) as registered,
               COUNT(DISTINCT tg.NguoiDungId) as attended
        FROM hoatdong hd
        LEFT JOIN danhsachdangky dk ON hd.Id = dk.HoatDongId
        LEFT JOIN danhsachthamgia tg ON hd.Id = tg.HoatDongId";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " GROUP BY hd.Id ORDER BY hd.NgayBatDau DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!-- Form tìm kiếm và kết quả ở đây --> 