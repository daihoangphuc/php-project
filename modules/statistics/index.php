<?php
// Không cần require vì đã được include từ init.php
redirectIfNotAdmin();

// Thống kê thành viên
$member_stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM nguoidung")->fetch_row()[0],
    'by_gender' => $conn->query("
        SELECT GioiTinh, COUNT(*) as SoLuong 
        FROM nguoidung 
        GROUP BY GioiTinh
    ")->fetch_all(MYSQLI_ASSOC),
    'by_class' => $conn->query("
        SELECT l.TenLop, COUNT(nd.Id) as SoLuong
        FROM lophoc l
        LEFT JOIN nguoidung nd ON l.Id = nd.LopHocId
        GROUP BY l.Id, l.TenLop
    ")->fetch_all(MYSQLI_ASSOC),
    'by_role' => $conn->query("
        SELECT cv.TenChucVu, COUNT(nd.Id) as SoLuong
        FROM chucvu cv
        LEFT JOIN nguoidung nd ON cv.Id = nd.ChucVuId
        GROUP BY cv.Id, cv.TenChucVu
    ")->fetch_all(MYSQLI_ASSOC)
];

// Thống kê hoạt động
$activity_stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM hoatdong")->fetch_row()[0],
    'by_status' => $conn->query("
        SELECT TrangThai, COUNT(*) as SoLuong 
        FROM hoatdong 
        GROUP BY TrangThai
    ")->fetch_all(MYSQLI_ASSOC),
    'participation' => $conn->query("
        SELECT h.Id, h.TenHoatDong,
            COUNT(DISTINCT dk.NguoiDungId) as SoDangKy,
            COUNT(DISTINCT tg.NguoiDungId) as SoThamGia
        FROM hoatdong h
        LEFT JOIN danhsachdangky dk ON h.Id = dk.HoatDongId AND dk.TrangThai = 1
        LEFT JOIN danhsachthamgia tg ON h.Id = tg.HoatDongId
        GROUP BY h.Id, h.TenHoatDong
    ")->fetch_all(MYSQLI_ASSOC)
];

// Thống kê nhiệm vụ
$task_stats = [
    'total' => $conn->query("SELECT COUNT(*) FROM nhiemvu")->fetch_row()[0],
    'by_status' => $conn->query("
        SELECT TrangThai, COUNT(*) as SoLuong 
        FROM nhiemvu 
        GROUP BY TrangThai
    ")->fetch_all(MYSQLI_ASSOC)
];

$pageTitle = 'Thống kê báo cáo';
?>

<!-- HTML code --> 