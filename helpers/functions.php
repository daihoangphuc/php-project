<?php
// Hàm validate và làm sạch input
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Hàm ghi log hoạt động
function logActivity($ip, $user, $action, $result, $details = '') {
    global $conn;
    
    $sql = "INSERT INTO nhatkyhoatdong (IP, NguoiDung, HanhDong, KetQua, ChiTiet) 
            VALUES (?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $ip, $user, $action, $result, $details);
    $stmt->execute();
}

// Hàm format ngày giờ
function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Hàm format số tiền
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

// Hàm kiểm tra quyền admin
function isAdmin() {
    return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
}

// Hàm tạo breadcrumb
function getBreadcrumb($module, $action) {
    $breadcrumb = [
        'home' => [
            'index' => 'Trang chủ'
        ],
        'activities' => [
            'index' => 'Hoạt động',
            'add' => 'Thêm hoạt động',
            'edit' => 'Sửa hoạt động',
            'view' => 'Chi tiết hoạt động'
        ],
        'tasks' => [
            'index' => 'Nhiệm vụ',
            'add' => 'Thêm nhiệm vụ',
            'edit' => 'Sửa nhiệm vụ',
            'view' => 'Chi tiết nhiệm vụ'
        ],
        'news' => [
            'index' => 'Tin tức',
            'add' => 'Thêm tin tức',
            'edit' => 'Sửa tin tức',
            'view' => 'Chi tiết tin tức'
        ],
        'users' => [
            'index' => 'Thành viên',
            'add' => 'Thêm thành viên',
            'edit' => 'Sửa thành viên',
            'view' => 'Chi tiết thành viên'
        ],
        'finance' => [
            'index' => 'Tài chính',
            'add' => 'Thêm giao dịch',
            'edit' => 'Sửa giao dịch'
        ],
        'statistics' => [
            'index' => 'Thống kê'
        ],
        'auth' => [
            'login' => 'Đăng nhập',
            'register' => 'Đăng ký',
            'forgot_password' => 'Quên mật khẩu',
            'reset_password' => 'Đặt lại mật khẩu',
            'profile' => 'Thông tin cá nhân',
            'change_password' => 'Đổi m��t khẩu'
        ]
    ];
    
    return isset($breadcrumb[$module][$action]) ? $breadcrumb[$module][$action] : '';
} 