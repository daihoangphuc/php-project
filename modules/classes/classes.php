<?php
class Classes {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($search = '', $faculty_id = null) {
        $where = "WHERE 1=1";
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where .= " AND (l.TenLop LIKE '%$search%' OR k.TenKhoaTruong LIKE '%$search%')";
        }
        if ($faculty_id) {
            $where .= " AND l.KhoaTruongId = " . (int)$faculty_id;
        }
        
        $sql = "SELECT l.*, k.TenKhoaTruong, COUNT(n.Id) as SoLuongSinhVien
                FROM lophoc l
                LEFT JOIN khoatruong k ON l.KhoaTruongId = k.Id
                LEFT JOIN nguoidung n ON l.Id = n.LopHocId
                $where
                GROUP BY l.Id
                ORDER BY k.TenKhoaTruong, l.TenLop";
        $result = $this->conn->query($sql);
        
        $classes = [];
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
        
        return $classes;
    }
    
    public function getById($id) {
        $sql = "SELECT l.*, k.TenKhoaTruong 
                FROM lophoc l
                LEFT JOIN khoatruong k ON l.KhoaTruongId = k.Id
                WHERE l.Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($data) {
        // Validate
        if (empty($data['name']) || empty($data['faculty_id'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        // Kiểm tra tên đã tồn tại trong khoa
        $sql = "SELECT Id FROM lophoc WHERE TenLop = ? AND KhoaTruongId = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $data['name'], $data['faculty_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Tên lớp đã tồn tại trong khoa này'];
        }
        
        // Thêm mới
        $sql = "INSERT INTO lophoc (TenLop, KhoaTruongId) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $data['name'], $data['faculty_id']);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Thêm lớp học',
                'Thành công',
                "Tên: {$data['name']}, Khoa: {$data['faculty_id']}"
            );
            return ['success' => true, 'message' => 'Thêm lớp học thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function update($id, $data) {
        // Validate
        if (empty($data['name']) || empty($data['faculty_id'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        // Kiểm tra tên đã tồn tại trong khoa
        $sql = "SELECT Id FROM lophoc WHERE TenLop = ? AND KhoaTruongId = ? AND Id != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $data['name'], $data['faculty_id'], $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Tên lớp đã tồn tại trong khoa này'];
        }
        
        // Cập nhật
        $sql = "UPDATE lophoc SET TenLop = ?, KhoaTruongId = ? WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sii", $data['name'], $data['faculty_id'], $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật lớp học',
                'Thành công',
                "Id: $id, Tên: {$data['name']}, Khoa: {$data['faculty_id']}"
            );
            return ['success' => true, 'message' => 'Cập nhật lớp học thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function delete($id) {
        // Kiểm tra lớp có sinh viên không
        $sql = "SELECT Id FROM nguoidung WHERE LopHocId = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Không thể xóa lớp đang có sinh viên'];
        }
        
        // Xóa
        $sql = "DELETE FROM lophoc WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa lớp học',
                'Thành công',
                "Id: $id"
            );
            return ['success' => true, 'message' => 'Xóa lớp học thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
} 