<?php
function backupDatabase() {
    $date = date("Y-m-d-H-i-s");
    $filename = "backup-" . $date . ".sql";
    $command = "mysqldump -u root -p'' hstv-management > backups/" . $filename;
    
    if (!is_dir('backups')) {
        mkdir('backups', 0777, true);
    }
    
    system($command);
    logActivity($_SERVER['REMOTE_ADDR'], 'System', 'Backup', 'Success', 'Database backup created: ' . $filename);
} 