<?php
require_once 'includes/auth.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = validateInput($_POST['name']);
    $category = validateInput($_POST['category']);
    $file = $_FILES['file'];
    
    // Kiểm tra file
    $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExt, $allowedTypes)) {
        showToast('error', 'Định dạng file không được hỗ trợ');
        header('Location: ?module=documents');
        exit;
    }
    
    // Tạo tên file mới
    $newFileName = uniqid() . '.' . $fileExt;
    $uploadPath = 'uploads/documents/' . $newFileName;
    
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $stmt = $conn->prepare("INSERT INTO tailieu (TenTaiLieu, PhanLoai, DuongDan, NguoiTaoId) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $category, $newFileName, $_SESSION['user']['id']);
        
        if ($stmt->execute()) {
            showToast('success', 'Tải lên tài liệu thành công');
            logActivity($_SERVER['REMOTE_ADDR'], $_SESSION['user']['HoTen'], 'Upload tài liệu', 'Success', $name);
        } else {
            showToast('error', 'Có lỗi xảy ra khi lưu thông tin tài liệu');
            unlink($uploadPath);
        }
    } else {
        showToast('error', 'Có lỗi xảy ra khi tải file lên');
    }
    
    header('Location: ?module=documents');
    exit;
} 