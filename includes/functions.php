<?php
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

function isLoggedIn() {
    return isset($_SESSION['user']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: ?module=auth&action=login');
        exit;
    }
}

function redirectIfNotAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function showToast($type, $message) {
    $_SESSION['toast'] = [
        'type' => $type,
        'message' => $message
    ];
}

function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function logActivity($ip, $user, $action, $status, $details = '') {
    global $conn;
    $sql = "INSERT INTO nhatky (IP, NguoiDung, HanhDong, TrangThai, ChiTiet) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $ip, $user, $action, $status, $details);
    $stmt->execute();
} 