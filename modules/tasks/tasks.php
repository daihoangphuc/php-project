<?php
class Tasks {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($search = '', $status = null, $user_id = null) {
        $where = "WHERE 1=1";
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where .= " AND (t.TieuDe LIKE '%$search%' OR t.MoTa LIKE '%$search%')";
        }
        if ($status !== null) {
            $where .= " AND t.TrangThai = " . (int)$status;
        }
        if ($user_id) {
            $where .= " AND t.NguoiDungId = " . (int)$user_id;
        }
        
        $sql = "SELECT t.*, n.HoTen as NguoiThucHien 
                FROM nhiemvu t
                LEFT JOIN nguoidung n ON t.NguoiDungId = n.Id
                $where
                ORDER BY t.NgayTao DESC";
        $result = $this->conn->query($sql);
        
        $tasks = [];
        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }
        
        return $tasks;
    }
    
    public function getById($id) {
        $sql = "SELECT t.*, n.HoTen as NguoiThucHien 
                FROM nhiemvu t
                LEFT JOIN nguoidung n ON t.NguoiDungId = n.Id
                WHERE t.Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($data) {
        // Validate
        if (empty($data['title']) || empty($data['description'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        // Thêm mới
        $sql = "INSERT INTO nhiemvu (TieuDe, MoTa, NguoiDungId, TrangThai, HanChot) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $status = 0; // Mới
        $stmt->bind_param("ssiis", 
            $data['title'],
            $data['description'],
            $data['user_id'],
            $status,
            $data['deadline']
        );
        
        if ($stmt->execute()) {
            $task_id = $stmt->insert_id;
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Thêm nhiệm vụ',
                'Thành công',
                "Id: $task_id, Tiêu đề: {$data['title']}"
            );
            return ['success' => true, 'message' => 'Thêm nhiệm vụ thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function update($id, $data) {
        // Validate
        if (empty($data['title']) || empty($data['description'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        // Cập nhật
        $sql = "UPDATE nhiemvu 
                SET TieuDe = ?, MoTa = ?, NguoiDungId = ?, TrangThai = ?, HanChot = ?
                WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssiisi",
            $data['title'],
            $data['description'], 
            $data['user_id'],
            $data['status'],
            $data['deadline'],
            $id
        );
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật nhiệm vụ',
                'Thành công',
                "Id: $id, Tiêu đề: {$data['title']}"
            );
            return ['success' => true, 'message' => 'Cập nhật nhiệm vụ thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function delete($id) {
        $sql = "DELETE FROM nhiemvu WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa nhiệm vụ',
                'Thành công',
                "Id: $id"
            );
            return ['success' => true, 'message' => 'Xóa nhiệm vụ thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function updateStatus($id, $status) {
        $sql = "UPDATE nhiemvu SET TrangThai = ? WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $status, $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật trạng thái nhiệm vụ',
                'Thành công',
                "Id: $id, Trạng thái: $status"
            );
            return ['success' => true, 'message' => 'Cập nhật trạng thái thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
} 