<?php
require_once 'vendor/autoload.php';

function getGoogleClient() {
    $client = new Google_Client();
    $client->setClientId('YOUR_CLIENT_ID');
    $client->setClientSecret('YOUR_CLIENT_SECRET');
    $client->setRedirectUri('http://localhost/callback.php');
    $client->addScope("email");
    $client->addScope("profile");
    return $client;
}

function handleGoogleLogin($googleUser) {
    global $conn;
    
    $email = $googleUser->getEmail();
    $name = $googleUser->getName();
    
    // Kiểm tra email đã tồn tại
    $stmt = $conn->prepare("SELECT * FROM nguoidung WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Đăng nhập nếu tài khoản tồn tại
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user;
    } else {
        // Tạo tài khoản mới
        $stmt = $conn->prepare("INSERT INTO nguoidung (HoTen, Email, role) VALUES (?, ?, 'user')");
        $stmt->bind_param("ss", $name, $email);
        $stmt->execute();
        
        $_SESSION['user'] = [
            'id' => $conn->insert_id,
            'HoTen' => $name,
            'Email' => $email,
            'role' => 'user'
        ];
    }
} 