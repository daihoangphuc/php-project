<?php
class Faculties {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($search = '') {
        $where = '';
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where = "WHERE TenKhoaTruong LIKE '%$search%'";
        }
        
        $sql = "SELECT k.*, COUNT(l.Id) as SoLuongLop
                FROM khoatruong k
                LEFT JOIN lophoc l ON k.Id = l.KhoaTruongId
                $where
                GROUP BY k.Id
                ORDER BY k.NgayTao DESC";
        $result = $this->conn->query($sql);
        
        $faculties = [];
        while ($row = $result->fetch_assoc()) {
            $faculties[] = $row;
        }
        
        return $faculties;
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM khoatruong WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($name) {
        // Validate
        if (empty($name)) {
            return ['success' => false, 'message' => 'Vui lòng nhập tên khoa/trường'];
        }
        
        // Kiểm tra tên đã tồn tại
        $sql = "SELECT Id FROM khoatruong WHERE TenKhoaTruong = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Tên khoa/trường đã tồn tại'];
        }
        
        // Thêm mới
        $sql = "INSERT INTO khoatruong (TenKhoaTruong) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Thêm khoa/trường',
                'Thành công',
                "Tên: $name"
            );
            return ['success' => true, 'message' => 'Thêm khoa/trường thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function update($id, $name) {
        // Validate
        if (empty($name)) {
            return ['success' => false, 'message' => 'Vui lòng nhập tên khoa/trường'];
        }
        
        // Kiểm tra tên đã tồn tại
        $sql = "SELECT Id FROM khoatruong WHERE TenKhoaTruong = ? AND Id != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Tên khoa/trường đã tồn tại'];
        }
        
        // Cập nhật
        $sql = "UPDATE khoatruong SET TenKhoaTruong = ? WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật khoa/trường',
                'Thành công',
                "Id: $id, Tên: $name"
            );
            return ['success' => true, 'message' => 'Cập nhật khoa/trường thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function delete($id) {
        // Kiểm tra khoa/trường có lớp học không
        $sql = "SELECT Id FROM lophoc WHERE KhoaTruongId = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Không thể xóa khoa/trường đang có lớp học'];
        }
        
        // Xóa
        $sql = "DELETE FROM khoatruong WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa khoa/trường',
                'Thành công',
                "Id: $id"
            );
            return ['success' => true, 'message' => 'Xóa khoa/trường thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
} 