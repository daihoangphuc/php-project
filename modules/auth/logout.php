<?php
// Ghi log trước khi đăng xuất
if (isset($_SESSION['user'])) {
    logActivity(
        $_SERVER['REMOTE_ADDR'],
        $_SESSION['user']['username'],
        'Đăng xuất',
        'Thành công'
    );
}

// Xóa toàn bộ session
session_destroy();

// Chuyển hướng về trang đăng nhập
header('Location: index.php?module=auth&action=login');
exit; 