<?php
class Activities {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($search = '', $status = null, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $where = "WHERE 1=1";
        
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where .= " AND (TenHoatDong LIKE '%$search%' OR MoTa LIKE '%$search%')";
        }
        
        if ($status !== null) {
            $where .= " AND TrangThai = " . (int)$status;
        }
        
        // Đếm tổng số bản ghi để phân trang
        $count_sql = "SELECT COUNT(*) as total FROM hoatdong $where";
        $count_result = $this->conn->query($count_sql);
        $total_records = $count_result->fetch_assoc()['total'];
        
        $sql = "SELECT h.*, 
                       COUNT(DISTINCT d.Id) as SoLuongDangKy,
                       COUNT(DISTINCT CASE WHEN d.TrangThai = 1 THEN d.Id END) as SoLuongThamGia
                FROM hoatdong h
                LEFT JOIN danhsachthamgia d ON h.Id = d.HoatDongId
                $where
                GROUP BY h.Id
                ORDER BY h.NgayBatDau DESC
                LIMIT $offset, $limit";
                
        $result = $this->conn->query($sql);
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = $row;
        }
        
        return [
            'data' => $activities,
            'total' => $total_records,
            'total_pages' => ceil($total_records / $limit)
        ];
    }
    
    public function create($data) {
        // Validate
        if (empty($data['title']) || empty($data['description']) || 
            empty($data['start_time']) || empty($data['end_time'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        // Kiểm tra thời gian
        if (strtotime($data['end_time']) <= strtotime($data['start_time'])) {
            return ['success' => false, 'message' => 'Thời gian kết thúc phải sau thời gian bắt đầu'];
        }
        
        $sql = "INSERT INTO hoatdong (TenHoatDong, MoTa, NgayBatDau, ThoiGianKetThuc, DiaDiem, SoLuongToiDa, TrangThai) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssii", 
            $data['title'],
            $data['description'],
            $data['start_time'],
            $data['end_time'],
            $data['location'],
            $data['max_participants'],
            $data['status']
        );
        
        if ($stmt->execute()) {
            $activity_id = $stmt->insert_id;
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Thêm hoạt động',
                'Thành công',
                "Id: $activity_id, Tên: {$data['title']}"
            );
            return ['success' => true, 'message' => 'Thêm hoạt động thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }

    public function update($id, $data) {
        // Validate tương tự create
        if (empty($data['title']) || empty($data['description']) || 
            empty($data['start_time']) || empty($data['end_time'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        $sql = "UPDATE hoatdong 
                SET TenHoatDong = ?, MoTa = ?, NgayBatDau = ?, 
                    ThoiGianKetThuc = ?, DiaDiem = ?, SoLuongToiDa = ?, TrangThai = ?
                WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssiii",
            $data['title'],
            $data['description'],
            $data['start_time'],
            $data['end_time'],
            $data['location'],
            $data['max_participants'],
            $data['status'],
            $id
        );
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật hoạt động',
                'Thành công',
                "Id: $id"
            );
            return ['success' => true, 'message' => 'Cập nhật hoạt động thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }

    public function delete($id) {
        // Xóa danh sách tham gia trước
        $sql = "DELETE FROM danhsachthamgia WHERE HoatDongId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Xóa hoạt động
        $sql = "DELETE FROM hoatdong WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa hoạt động',
                'Thành công',
                "Id: $id"
            );
            return ['success' => true, 'message' => 'Xóa hoạt động thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }

    public function register($activity_id, $user_id) {
        // Kiểm tra hoạt động tồn tại và còn nhận đăng ký
        $sql = "SELECT * FROM hoatdong WHERE Id = ? AND TrangThai = 1 AND NgayBatDau > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $activity_id);
        $stmt->execute();
        $activity = $stmt->get_result()->fetch_assoc();
        
        if (!$activity) {
            return ['success' => false, 'message' => 'Hoạt động không tồn tại hoặc đã hết hạn đăng ký'];
        }

        // Kiểm tra số lượng đăng ký
        $sql = "SELECT COUNT(*) as total FROM danhsachthamgia WHERE HoatDongId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $activity_id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['total'];
        
        if ($count >= $activity['SoLuongToiDa']) {
            return ['success' => false, 'message' => 'Hoạt động đã đủ số lượng đăng ký'];
        }

        // Kiểm tra đã đăng ký chưa
        $sql = "SELECT Id FROM danhsachthamgia WHERE HoatDongId = ? AND NguoiDungId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $activity_id, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Bạn đã đăng ký hoạt động này'];
        }

        // Thêm đăng ký mới
        $sql = "INSERT INTO danhsachthamgia (HoatDongId, NguoiDungId, ThoiGianDangKy, TrangThai) 
                VALUES (?, ?, NOW(), 0)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $activity_id, $user_id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Đăng ký hoạt động',
                'Thành công',
                "ActivityId: $activity_id"
            );
            return ['success' => true, 'message' => 'Đăng ký hoạt động thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }

    public function cancelRegister($activity_id, $user_id) {
        // Kiểm tra hoạt động chưa diễn ra
        $sql = "SELECT * FROM hoatdong WHERE Id = ? AND NgayBatDau > NOW()";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $activity_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows == 0) {
            return ['success' => false, 'message' => 'Không thể hủy đăng ký hoạt động đã diễn ra'];
        }

        // Xóa đăng ký
        $sql = "DELETE FROM danhsachthamgia WHERE HoatDongId = ? AND NguoiDungId = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $activity_id, $user_id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Hủy đăng ký hoạt động',
                'Thành công',
                "ActivityId: $activity_id"
            );
            return ['success' => true, 'message' => 'Hủy đăng ký thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }

    public function autoAttendance() {
        // Tự động điểm danh vắng cho các hoạt động đã kết thúc
        $sql = "UPDATE danhsachthamgia d
                JOIN hoatdong h ON d.HoatDongId = h.Id
                SET d.TrangThai = 0, d.GhiChu = 'Vắng mặt'
                WHERE h.ThoiGianKetThuc < NOW() 
                AND d.TrangThai IS NULL";
        $this->conn->query($sql);
    }

    public function getStatistics($from_date = null, $to_date = null) {
        $where = "WHERE 1=1";
        if ($from_date) {
            $where .= " AND h.NgayBatDau >= '$from_date'";
        }
        if ($to_date) {
            $where .= " AND h.ThoiGianKetThuc <= '$to_date'";
        }
        
        $sql = "SELECT h.Id, h.TenHoatDong,
                       COUNT(DISTINCT d.Id) as TongDangKy,
                       COUNT(DISTINCT CASE WHEN d.TrangThai = 1 THEN d.Id END) as TongThamGia,
                       COUNT(DISTINCT CASE WHEN d.TrangThai = 0 THEN d.Id END) as TongVangMat
                FROM hoatdong h
                LEFT JOIN danhsachthamgia d ON h.Id = d.HoatDongId
                $where
                GROUP BY h.Id
                ORDER BY h.NgayBatDau DESC";
                
        $result = $this->conn->query($sql);
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }
} 