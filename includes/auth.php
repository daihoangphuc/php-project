<?php
// Các hàm xác thực và phân quyền
function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header('Location: ?module=auth&action=login');
        exit;
    }
}

function checkAdmin() {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
} 