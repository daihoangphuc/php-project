<?php
require_once 'config/init.php';

// Xử lý routing
$module = isset($_GET['module']) ? $_GET['module'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Kiểm tra file tồn tại
$file = MODULES_PATH . "/$module/$action.php";
if (!file_exists($file)) {
    die("Không tìm thấy file: " . $file);
}

// Lưu nội dung vào buffer
ob_start();
include $file;
$content = ob_get_clean();

// Chọn layout phù hợp
if (isAdmin() && $module != 'auth') {
    include 'layouts/admin.php';
} else {
    include 'layouts/user.php';
} 