<?php
// Không cần require vì đã được include từ init.php
redirectIfNotAdmin();

// Thống kê tổng quan
$stats = [
    'total_members' => $conn->query("SELECT COUNT(*) FROM nguoidung")->fetch_row()[0],
    'total_activities' => $conn->query("SELECT COUNT(*) FROM hoatdong")->fetch_row()[0],
    'total_tasks' => $conn->query("SELECT COUNT(*) FROM nhiemvu")->fetch_row()[0],
    'total_documents' => $conn->query("SELECT COUNT(*) FROM tailieu")->fetch_row()[0]
];

// Hoạt động gần đây từ nhật ký
$recent_logs = $conn->query("
    SELECT n.*, n.NguoiDung
    FROM nhatkyhoatdong n
    ORDER BY n.NgayTao DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Thống kê hoạt động theo trạng thái
$activity_stats = $conn->query("
    SELECT TrangThai, COUNT(*) as SoLuong 
    FROM hoatdong 
    GROUP BY TrangThai
")->fetch_all(MYSQLI_ASSOC);

// Thống kê tài chính tháng này
$finance_stats = $conn->query("
    SELECT 
        SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE 0 END) as TongThu,
        SUM(CASE WHEN LoaiGiaoDich = 0 THEN SoTien ELSE 0 END) as TongChi
    FROM taichinh 
    WHERE MONTH(NgayGiaoDich) = MONTH(CURRENT_DATE())
    AND YEAR(NgayGiaoDich) = YEAR(CURRENT_DATE())
")->fetch_assoc();

$pageTitle = 'Dashboard';
?>

<!-- HTML code --> 