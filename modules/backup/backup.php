<?php
class Backup {
    private $conn;
    private $backup_dir;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->backup_dir = __DIR__ . '/../../backups/';
        
        // Tạo thư mục backup nếu chưa tồn tại
        if (!file_exists($this->backup_dir)) {
            mkdir($this->backup_dir, 0777, true);
        }
    }
    
    public function create() {
        $tables = [];
        $result = $this->conn->query('SHOW TABLES');
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        $sql = '';
        
        // Lưu cấu trúc bảng
        foreach ($tables as $table) {
            $result = $this->conn->query('SHOW CREATE TABLE ' . $table);
            $row = $result->fetch_row();
            $sql .= "\n\n" . $row[1] . ";\n\n";
            
            // Lưu dữ liệu
            $result = $this->conn->query('SELECT * FROM ' . $table);
            while ($row = $result->fetch_assoc()) {
                $sql .= 'INSERT INTO ' . $table . ' VALUES (';
                foreach ($row as $value) {
                    $value = addslashes($value);
                    $value = str_replace("\n", "\\n", $value);
                    if (isset($value)) {
                        $sql .= '"' . $value . '",';
                    } else {
                        $sql .= 'NULL,';
                    }
                }
                $sql = rtrim($sql, ',');
                $sql .= ");\n";
            }
        }
        
        $filename = $this->backup_dir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        file_put_contents($filename, $sql);
        
        return [
            'success' => true,
            'message' => 'Sao lưu dữ liệu thành công',
            'filename' => basename($filename)
        ];
    }
    
    public function getAll() {
        $files = glob($this->backup_dir . '*.sql');
        $backups = [];
        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'created_at' => date('Y-m-d H:i:s', filemtime($file))
            ];
        }
        // Sắp xếp theo thời gian tạo mới nhất
        usort($backups, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        return $backups;
    }
    
    public function delete($filename) {
        $file = $this->backup_dir . basename($filename);
        if (file_exists($file)) {
            unlink($file);
            return ['success' => true, 'message' => 'Xóa file backup thành công'];
        }
        return ['success' => false, 'message' => 'File không tồn tại'];
    }
} 