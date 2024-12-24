<?php
class Positions {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($search = '') {
        $where = '';
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where = "WHERE TenChucVu LIKE '%$search%'";
        }
        
        $sql = "SELECT * FROM chucvu $where ORDER BY NgayTao DESC";
        $result = $this->conn->query($sql);
        
        $positions = [];
        while ($row = $result->fetch_assoc()) {
            $positions[] = $row;
        }
        
        return $positions;
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM chucvu WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($name) {
        // Validate
        if (empty($name)) {
            return ['success' => false, 'message' => 'Vui lòng nhập tên chức vụ'];
        }
        
        // Kiểm tra tên đ�� tồn tại
        $sql = "SELECT Id FROM chucvu WHERE TenChucVu = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Tên chức vụ đã tồn tại'];
        }
        
        // Thêm mới
        $sql = "INSERT INTO chucvu (TenChucVu) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $name);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Thêm chức vụ',
                'Thành công',
                "Tên: $name"
            );
            return ['success' => true, 'message' => 'Thêm chức vụ thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function update($id, $name) {
        // Validate
        if (empty($name)) {
            return ['success' => false, 'message' => 'Vui lòng nhập tên chức vụ'];
        }
        
        // Kiểm tra tên đã tồn tại
        $sql = "SELECT Id FROM chucvu WHERE TenChucVu = ? AND Id != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Tên chức vụ đã tồn tại'];
        }
        
        // Cập nhật
        $sql = "UPDATE chucvu SET TenChucVu = ? WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật chức vụ',
                'Thành công',
                "Id: $id, Tên: $name"
            );
            return ['success' => true, 'message' => 'Cập nhật chức vụ thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function delete($id) {
        // Kiểm tra chức vụ có đang được sử dụng
        $sql = "SELECT Id FROM nguoidung WHERE ChucVuId = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Không thể xóa chức vụ đang được sử dụng'];
        }
        
        // Xóa
        $sql = "DELETE FROM chucvu WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa chức vụ',
                'Thành công',
                "Id: $id"
            );
            return ['success' => true, 'message' => 'Xóa chức vụ thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
} 