<?php
function connectDatabase() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'clb_hstv';
    $port = 3307; // Thay đổi thành cổng bạn muốn sử dụng

    try {
        // Tạo kết nối với PDO
        $conn = new mysqli($host, $username, $password, $database, $port);
        
        // Kiểm tra kết nối
        if ($conn->connect_error) {
            throw new Exception("Kết nối thất bại: " . $conn->connect_error);
        }
        
        // Đặt charset là utf8mb4
        if (!$conn->set_charset("utf8mb4")) {
            throw new Exception("Lỗi khi thiết lập charset: " . $conn->error);
        }
        
        return $conn;
    } catch (Exception $e) {
        // Log lỗi và hiển thị thông báo
        error_log($e->getMessage());
        die("Có lỗi xảy ra khi kết nối đến cơ sở dữ liệu. Vui lòng thử lại sau.");
    }
}
