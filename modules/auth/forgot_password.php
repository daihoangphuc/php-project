<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = validateInput($_POST['email']);
    
    $stmt = $conn->prepare("SELECT Id FROM nguoidung WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Lưu token
        $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user['Id'], $token, $expiry);
        $stmt->execute();
        
        // Gửi email
        $resetLink = "http://localhost/?module=auth&action=reset_password&token=" . $token;
        $to = $email;
        $subject = "Khôi phục mật khẩu";
        $message = "Click vào link sau để đặt lại mật khẩu: " . $resetLink;
        $headers = "From: noreply@example.com";
        
        mail($to, $subject, $message, $headers);
        showToast('success', 'Link khôi phục mật khẩu đã được gửi đến email của bạn');
    } else {
        showToast('error', 'Email không tồn tại trong hệ thống');
    }
}
?>

<div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-6">Khôi phục mật khẩu</h2>
    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" required 
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>
        <button type="submit" 
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Gửi link khôi phục
        </button>
    </form>
</div> 