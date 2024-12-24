<?php
require_once 'includes/auth.php';
redirectIfNotAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Lấy thông tin file đính kèm
    $stmt = $conn->prepare("SELECT FileDinhKem FROM tintuc WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($news = $result->fetch_assoc()) {
        // Xóa file đính kèm nếu có
        if (!empty($news['FileDinhKem'])) {
            $filePath = 'uploads/news/' . $news['FileDinhKem'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        // Xóa tin tức
        $stmt = $conn->prepare("DELETE FROM tintuc WHERE Id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
            logActivity($_SERVER['REMOTE_ADDR'], $_SESSION['user']['HoTen'], 'Xóa tin tức', 'Success', "ID: $id");
        } else {
            echo json_encode(['success' => false]);
        }
    } else {
        echo json_encode(['success' => false]);
    }
} else {
    echo json_encode(['success' => false]);
} 