<?php
session_start();
require_once 'config/database.php';
require_once 'helpers/functions.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    die(json_encode(['error' => 'Unauthorized']));
}

// Kiểm tra file upload
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
    die(json_encode(['error' => 'No file uploaded']));
}

$file = $_FILES['file'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

// Kiểm tra loại file
if (!in_array($file['type'], $allowed_types)) {
    die(json_encode(['error' => 'Invalid file type']));
}

// Kiểm tra kích thước
if ($file['size'] > $max_size) {
    die(json_encode(['error' => 'File too large']));
}

// Tạo thư mục uploads nếu chưa có
$upload_dir = 'uploads/images/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Tạo tên file ngẫu nhiên
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Upload file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Ghi log
    logActivity(
        $_SERVER['REMOTE_ADDR'],
        $_SESSION['user']['username'],
        'Upload image',
        'Success',
        "File: $filename"
    );
    
    // Trả về URL của file cho TinyMCE
    die(json_encode([
        'location' => $filepath
    ]));
} else {
    die(json_encode(['error' => 'Upload failed']));
} 