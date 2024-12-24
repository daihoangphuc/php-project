<?php
function autoAttendance() {
    global $conn;
    
    // Lấy các hoạt động đang diễn ra
    $activities = $conn->query("
        SELECT Id, NgayBatDau, NgayKetThuc 
        FROM hoatdong 
        WHERE TrangThai = 1 
        AND NOW() BETWEEN NgayBatDau AND NgayKetThuc
    ")->fetch_all(MYSQLI_ASSOC);
    
    foreach ($activities as $activity) {
        // Lấy danh sách người đăng ký nhưng chưa điểm danh
        $stmt = $conn->prepare("
            SELECT dk.NguoiDungId
            FROM danhsachdangky dk
            LEFT JOIN danhsachthamgia tg ON dk.NguoiDungId = tg.NguoiDungId 
                AND dk.HoatDongId = tg.HoatDongId
            WHERE dk.HoatDongId = ? 
            AND dk.TrangThai = 1
            AND tg.Id IS NULL
        ");
        $stmt->bind_param("i", $activity['Id']);
        $stmt->execute();
        $unattended = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Tự động điểm danh vắng mặt sau 15 phút
        if (strtotime($activity['NgayBatDau']) + 900 < time()) {
            foreach ($unattended as $user) {
                $stmt = $conn->prepare("
                    INSERT INTO danhsachthamgia (NguoiDungId, HoatDongId, TrangThai) 
                    VALUES (?, ?, 0)
                ");
                $stmt->bind_param("ii", $user['NguoiDungId'], $activity['Id']);
                $stmt->execute();
                
                logActivity('System', 'System', 'Auto Attendance', 'Absent', 
                    "User: {$user['NguoiDungId']}, Activity: {$activity['Id']}");
            }
        }
    }
}

// Thêm vào cron job hoặc gọi khi truy cập trang
if (!isset($_SESSION['last_attendance_check']) || 
    (time() - $_SESSION['last_attendance_check']) > 300) {
    autoAttendance();
    $_SESSION['last_attendance_check'] = time();
} 