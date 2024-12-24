<?php
function checkLoginAttempts($ip) {
    global $conn;
    
    // Xóa các attempts cũ (quá 30 phút)
    $stmt = $conn->prepare("DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 30 MINUTE)");
    $stmt->execute();
    
    // Đếm số lần thử đăng nhập sai
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM login_attempts WHERE ip_address = ?");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    return $result['count'];
}

function addLoginAttempt($ip) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address) VALUES (?)");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
}

function isIpBlocked($ip) {
    return checkLoginAttempts($ip) >= 5;
} 