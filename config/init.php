<?php
session_start();

// Định nghĩa hằng số đường dẫn
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('MODULES_PATH', ROOT_PATH . '/modules');
define('ASSETS_PATH', ROOT_PATH . '/assets');

// Include các file cần thiết
require_once CONFIG_PATH . '/database.php';
require_once INCLUDES_PATH . '/functions.php';
require_once MODULES_PATH . '/auth/auth.php';

// Kết nối database
$conn = connectDatabase();
if (!$conn) {
    die("Không thể kết nối database");
} 