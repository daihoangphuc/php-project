<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Xử lý routing
$module = isset($_GET['module']) ? $_GET['module'] : 'home';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Kiểm tra file tồn tại
$file = "modules/$module/$action.php";
if (!file_exists($file)) {
    $file = 'modules/errors/404.php';
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