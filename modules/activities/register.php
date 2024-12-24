<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $activityId = (int)$_GET['id'];
    $userId = $_SESSION['user']['id'];
    
    // Kiểm tra đã đăng ký chưa
    $stmt = $conn->prepare("SELECT Id FROM danhsachdangky WHERE NguoiDungId = ? AND HoatDongId = ?");
    $stmt->bind_param("ii", $userId, $activityId);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        showToast('error', 'Bạn đã đăng ký hoạt động này rồi');
    } else {
        // Kiểm tra số lượng đăng ký
        $activity = $conn->query("SELECT SoLuong FROM hoatdong WHERE Id = $activityId")->fetch_assoc();
        $registered = $conn->query("SELECT COUNT(*) as count FROM danhsachdangky WHERE HoatDongId = $activityId")->fetch_assoc()['count'];
        
        if ($activity['SoLuong'] > 0 && $registered >= $activity['SoLuong']) {
            showToast('error', 'Hoạt động đã đủ số lượng đăng ký');
        } else {
            $stmt = $conn->prepare("INSERT INTO danhsachdangky (NguoiDungId, HoatDongId) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $activityId);
            
            if ($stmt->execute()) {
                showToast('success', 'Đăng ký hoạt động thành công');
                logActivity($_SERVER['REMOTE_ADDR'], $_SESSION['user']['HoTen'], 'Đăng ký hoạt động', 'Success', "ID: $activityId");
            } else {
                showToast('error', 'Có lỗi xảy ra khi đăng ký');
            }
        }
    }
    
    header('Location: ?module=activities&action=view&id=' . $activityId);
    exit;
} 