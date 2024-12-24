<?php
class News {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($page = 1, $limit = 10, $search = '') {
        $offset = ($page - 1) * $limit;
        
        $where = "WHERE 1=1";
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where .= " AND (TieuDe LIKE '%$search%' OR NoiDung LIKE '%$search%')";
        }
        
        $sql = "SELECT t.*, n.HoTen as TenNguoiTao 
                FROM tintuc t
                LEFT JOIN nguoidung n ON t.NguoiTaoId = n.Id
                $where 
                ORDER BY t.NgayTao DESC 
                LIMIT ?, ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $news = [];
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
        
        // Đếm tổng số bản ghi
        $count_sql = "SELECT COUNT(*) as total FROM tintuc t $where";
        $count_result = $this->conn->query($count_sql);
        $total = $count_result->fetch_assoc()['total'];
        
        return [
            'news' => $news,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function getById($id) {
        $sql = "SELECT t.*, n.HoTen as TenNguoiTao, n.MaSinhVien
                FROM tintuc t
                LEFT JOIN nguoidung n ON t.NguoiTaoId = n.Id
                WHERE t.Id = ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($data) {
        // Validate dữ liệu
        $required_fields = ['title', 'content'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
            }
        }
        
        // Upload file đính kèm nếu có
        $file_path = null;
        if (isset($data['file']) && $data['file']['error'] === 0) {
            $result = $this->uploadFile($data['file']);
            if (!$result['success']) {
                return $result;
            }
            $file_path = $result['file_path'];
        }
        
        $sql = "INSERT INTO tintuc (TieuDe, NoiDung, NgayTao, FileDinhKem, NguoiTaoId) 
                VALUES (?, ?, NOW(), ?, ?)";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", 
            $data['title'],
            $data['content'],
            $file_path,
            $data['user_id']
        );
        
        if ($stmt->execute()) {
            $news_id = $stmt->insert_id;
            
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Thêm tin tức mới',
                'Thành công',
                "NewsId: $news_id"
            );
            
            return ['success' => true, 'message' => 'Thêm tin tức thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function update($id, $data) {
        // Validate dữ liệu
        $required_fields = ['title', 'content'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
            }
        }
        
        // Upload file đính kèm mới nếu có
        $file_path = $data['current_file'];
        if (isset($data['file']) && $data['file']['error'] === 0) {
            $result = $this->uploadFile($data['file']);
            if (!$result['success']) {
                return $result;
            }
            $file_path = $result['file_path'];
            
            // Xóa file cũ nếu có
            if ($data['current_file']) {
                @unlink('uploads/' . $data['current_file']);
            }
        }
        
        $sql = "UPDATE tintuc SET 
                TieuDe = ?,
                NoiDung = ?,
                FileDinhKem = ?
                WHERE Id = ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", 
            $data['title'],
            $data['content'],
            $file_path,
            $id
        );
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật tin tức',
                'Thành công',
                "NewsId: $id"
            );
            
            return ['success' => true, 'message' => 'Cập nhật tin tức thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function delete($id) {
        // Kiểm tra tin tức tồn tại
        $check_sql = "SELECT FileDinhKem FROM tintuc WHERE Id = ? LIMIT 1";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $news = $check_stmt->get_result()->fetch_assoc();
        
        if (!$news) {
            return ['success' => false, 'message' => 'Tin tức không tồn tại'];
        }
        
        // Xóa file đính kèm nếu có
        if ($news['FileDinhKem']) {
            @unlink('uploads/' . $news['FileDinhKem']);
        }
        
        // Xóa tin tức
        $sql = "DELETE FROM tintuc WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa tin tức',
                'Thành công',
                "NewsId: $id"
            );
            
            return ['success' => true, 'message' => 'Xóa tin tức thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    private function uploadFile($file) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        
        if (!in_array($file_extension, $allowed_types)) {
            return ['success' => false, 'message' => 'Chỉ cho phép upload file: ' . implode(', ', $allowed_types)];
        }
        
        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            return ['success' => false, 'message' => 'Kích thước file không được vượt quá 5MB'];
        }
        
        $file_name = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return ['success' => true, 'file_path' => $file_name];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra khi upload file'];
    }
} 