<?php
function connectDatabase() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'clb_hstv';
    $port = 3306;

    try {
        $conn = new mysqli($host, $username, $password, $database, $port);
        
        if ($conn->connect_error) {
            // In thông báo lỗi chi tiết hơn để debug
            die("Lỗi kết nối MySQL: " . $conn->connect_error . 
                "<br>Host: $host" .
                "<br>Database: $database" .
                "<br>Port: $port");
        }
        
        if (!$conn->set_charset("utf8mb4")) {
            die("Lỗi charset: " . $conn->error);
        }
        
        return $conn;
    } catch (Exception $e) {
        die("Lỗi: " . $e->getMessage());
    }
}
