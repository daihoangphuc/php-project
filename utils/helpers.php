<?php
function isAllowed($module, $action) {
    // Các route công khai
    $public_routes = [
        'auth' => ['login', 'register', 'forgot_password'],
        'home' => ['index']
    ];

    // Kiểm tra route công khai
    if (isset($public_routes[$module]) && in_array($action, $public_routes[$module])) {
        return true;
    }

    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user'])) {
        return false;
    }

    // Kiểm tra quyền admin
    if ($_SESSION['user']['role'] === 'admin') {
        return true;
    }

    // Định nghĩa quyền cho user thường
    $user_routes = [
        'users' => ['profile', 'update'],
        'activities' => ['index', 'register', 'view'],
        'tasks' => ['index', 'view']
    ];

    return isset($user_routes[$module]) && in_array($action, $user_routes[$module]);
}

function logActivity($ip, $user, $action, $status, $note = '') {
    global $conn;
    $sql = "INSERT INTO loghoatdong (IP, NguoiThucHien, ThoiGian, HanhDong, TrangThai, GhiChu) 
            VALUES (?, ?, NOW(), ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $ip, $user, $action, $status, $note);
    return $stmt->execute();
}

function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
} 