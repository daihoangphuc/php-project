<?php
require_once 'includes/auth.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin giao dịch trước khi xóa
    $stmt = $conn->prepare("SELECT * FROM taichinh WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();
    
    if ($transaction) {
        $stmt = $conn->prepare("DELETE FROM taichinh WHERE Id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['HoTen'],
                'Xóa giao dịch tài chính',
                'Success',
                "ID: $id, Amount: " . number_format($transaction['SoTien'])
            );
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
} 