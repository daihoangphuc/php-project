<?php
require_once 'includes/auth.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin file
    $stmt = $conn->prepare("SELECT DuongDan FROM tailieu WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($file = $result->fetch_assoc()) {
        $filePath = 'uploads/documents/' . $file['DuongDan'];
        
        // Xóa file
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Xóa record trong database
        $stmt = $conn->prepare("DELETE FROM tailieu WHERE Id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
            logActivity($_SERVER['REMOTE_ADDR'], $_SESSION['user']['HoTen'], 'Xóa tài liệu', 'Success', "ID: $id");
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
} 