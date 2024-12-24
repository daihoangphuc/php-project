<?php
class Users {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($page = 1, $limit = 10, $search = '', $class_id = null, $role = null) {
        $offset = ($page - 1) * $limit;
        
        $where = "WHERE 1=1";
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where .= " AND (n.HoTen LIKE '%$search%' OR n.MaSinhVien LIKE '%$search%' OR n.Email LIKE '%$search%')";
        }
        if ($class_id) {
            $where .= " AND n.LopHocId = " . (int)$class_id;
        }
        if ($role) {
            $where .= " AND v.TenVaiTro = '" . $this->conn->real_escape_string($role) . "'";
        }
        
        $sql = "SELECT n.*, l.TenLop, k.TenKhoaTruong, c.TenChucVu, GROUP_CONCAT(v.TenVaiTro) as roles
                FROM nguoidung n
                LEFT JOIN lophoc l ON n.LopHocId = l.Id
                LEFT JOIN khoatruong k ON l.KhoaTruongId = k.Id
                LEFT JOIN chucvu c ON n.ChucVuId = c.Id
                LEFT JOIN vaitronguoidung vn ON n.Id = vn.NguoiDungId
                LEFT JOIN vaitro v ON vn.VaiTroId = v.Id
                $where
                GROUP BY n.Id
                ORDER BY n.NgayTao DESC
                LIMIT ?, ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        // Đếm tổng số bản ghi
        $count_sql = "SELECT COUNT(DISTINCT n.Id) as total 
                     FROM nguoidung n
                     LEFT JOIN vaitronguoidung vn ON n.Id = vn.NguoiDungId
                     LEFT JOIN vaitro v ON vn.VaiTroId = v.Id
                     $where";
        $count_result = $this->conn->query($count_sql);
        $total = $count_result->fetch_assoc()['total'];
        
        return [
            'users' => $users,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    public function getById($id) {
        $sql = "SELECT n.*, l.TenLop, k.TenKhoaTruong, c.TenChucVu, GROUP_CONCAT(v.TenVaiTro) as roles
                FROM nguoidung n
                LEFT JOIN lophoc l ON n.LopHocId = l.Id
                LEFT JOIN khoatruong k ON l.KhoaTruongId = k.Id
                LEFT JOIN chucvu c ON n.ChucVuId = c.Id
                LEFT JOIN vaitronguoidung vn ON n.Id = vn.NguoiDungId
                LEFT JOIN vaitro v ON vn.VaiTroId = v.Id
                WHERE n.Id = ?
                GROUP BY n.Id";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function update($id, $data) {
        // Validate dữ liệu
        $required_fields = ['fullname', 'email', 'class_id', 'position_id'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
            }
        }
        
        // Kiểm tra email đã tồn tại
        $sql = "SELECT Id FROM nguoidung WHERE Email = ? AND Id != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $data['email'], $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email đã tồn tại'];
        }
        
        // Cập nhật thông tin
        $sql = "UPDATE nguoidung 
                SET HoTen = ?, Email = ?, GioiTinh = ?, NgaySinh = ?, LopHocId = ?, ChucVuId = ?, TranThai = ?
                WHERE Id = ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssiiis", 
            $data['fullname'],
            $data['email'],
            $data['gender'],
            $data['birthday'],
            $data['class_id'],
            $data['position_id'],
            $data['status'],
            $id
        );
        
        if ($stmt->execute()) {
            // Cập nhật vai trò nếu có thay đổi
            if (isset($data['roles'])) {
                // Xóa vai trò cũ
                $sql = "DELETE FROM vaitronguoidung WHERE NguoiDungId = ?";
                $stmt = $this->conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                // Thêm vai trò mới
                foreach ($data['roles'] as $role_id) {
                    $sql = "INSERT INTO vaitronguoidung (VaiTroId, NguoiDungId) VALUES (?, ?)";
                    $stmt = $this->conn->prepare($sql);
                    $stmt->bind_param("ii", $role_id, $id);
                    $stmt->execute();
                }
            }
            
            // Ghi log
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật thông tin thành viên',
                'Thành công',
                "UserId: $id"
            );
            
            return ['success' => true, 'message' => 'Cập nhật thông tin thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function delete($id) {
        // Kiểm tra người dùng tồn tại
        $sql = "SELECT Id FROM nguoidung WHERE Id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại'];
        }
        
        // Xóa vai trò
        $sql = "DELETE FROM vaitronguoidung WHERE NguoiDungId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Xóa người dùng
        $sql = "DELETE FROM nguoidung WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Ghi log
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa thành viên',
                'Thành công',
                "UserId: $id"
            );
            
            return ['success' => true, 'message' => 'Xóa thành viên thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
} 