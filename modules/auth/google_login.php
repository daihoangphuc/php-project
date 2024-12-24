<?php
require_once 'vendor/autoload.php';
require_once 'config/google.php';

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(GOOGLE_REDIRECT_URI);
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    if (!isset($token['error'])) {
        $google_client->setAccessToken($token['access_token']);
        
        $google_service = new Google_Service_Oauth2($google_client);
        $data = $google_service->userinfo->get();
        
        // Kiểm tra email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT Id FROM nguoidung WHERE Email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            // Thêm người dùng mới
            $stmt = $conn->prepare("INSERT INTO nguoidung (HoTen, Email, TenDangNhap, MatKhauHash) VALUES (?, ?, ?, ?)");
            $username = explode('@', $data['email'])[0];
            $random_pass = bin2hex(random_bytes(8));
            $hash = password_hash($random_pass, PASSWORD_DEFAULT);
            $stmt->bind_param("ssss", $data['name'], $data['email'], $username, $hash);
            $stmt->execute();
            
            // Gửi email thông báo mật khẩu
            send_password_email($data['email'], $random_pass);
        }
        
        // Đăng nhập người dùng
        $_SESSION['user'] = [
            'email' => $data['email'],
            'name' => $data['name']
        ];
        
        header('Location: index.php');
        exit();
    }
} 