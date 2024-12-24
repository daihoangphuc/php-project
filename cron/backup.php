<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../modules/backup/backup.php';

try {
    // Kết nối database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8mb4");

    // Khởi tạo class backup
    $backup = new Backup($conn);
    
    // Thực hiện backup
    $result = $backup->create();
    
    // Ghi log
    if ($result['success']) {
        logActivity(
            'SYSTEM',
            'CRON',
            'Auto Backup',
            'Success',
            "File: " . $result['filename']
        );
        
        // Xóa các file backup cũ (giữ lại 7 ngày gần nhất)
        $old_files = glob(__DIR__ . '/../backups/backup_*.sql');
        $files_to_keep = 7;
        
        if (count($old_files) > $files_to_keep) {
            // Sắp xếp theo thời gian tạo
            usort($old_files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            // Xóa các file cũ
            $files_to_delete = array_slice($old_files, $files_to_keep);
            foreach ($files_to_delete as $file) {
                unlink($file);
            }
        }
    } else {
        logActivity(
            'SYSTEM',
            'CRON',
            'Auto Backup',
            'Error',
            "Failed to create backup"
        );
    }
    
    $conn->close();
} catch (Exception $e) {
    logActivity(
        'SYSTEM',
        'CRON',
        'Auto Backup',
        'Error',
        $e->getMessage()
    );
} 