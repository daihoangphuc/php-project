<?php
// Thiết lập các header bảo mật
function setSecurityHeaders() {
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("X-Content-Type-Options: nosniff");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Content-Security-Policy: default-src 'self'");
}

// Lọc và kiểm tra dữ liệu đầu vào
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Kiểm tra token CSRF
function validateCSRFToken() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token!");
    }
}

// Tạo token CSRF mới
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Kiểm tra và giới hạn số lần đăng nhập thất bại
function checkLoginAttempts($username) {
    if (!isset($_SESSION['login_attempts'][$username])) {
        $_SESSION['login_attempts'][$username] = [
            'count' => 0,
            'first_attempt' => time()
        ];
    }

    $attempts = &$_SESSION['login_attempts'][$username];
    
    // Reset sau 30 phút
    if (time() - $attempts['first_attempt'] > 1800) {
        $attempts['count'] = 0;
        $attempts['first_attempt'] = time();
    }

    // Giới hạn 5 lần thử
    if ($attempts['count'] >= 5) {
        return false;
    }

    $attempts['count']++;
    return true;
} 