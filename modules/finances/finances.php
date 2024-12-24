<?php
class Finances {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($search = '', $type = null, $from_date = null, $to_date = null, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        $where = "WHERE 1=1";
        
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where .= " AND (MoTa LIKE '%$search%' OR GhiChu LIKE '%$search%')";
        }
        
        if ($type !== null) {
            $where .= " AND LoaiGiaoDich = " . (int)$type;
        }
        
        if ($from_date) {
            $where .= " AND NgayGiaoDich >= '$from_date'";
        }
        
        if ($to_date) {
            $where .= " AND NgayGiaoDich <= '$to_date'";
        }
        
        // Đếm tổng số bản ghi
        $count_sql = "SELECT COUNT(*) as total FROM taichinh $where";
        $count_result = $this->conn->query($count_sql);
        $total_records = $count_result->fetch_assoc()['total'];
        
        // Lấy danh sách giao dịch
        $sql = "SELECT t.*, n.HoTen as NguoiTao 
                FROM taichinh t
                LEFT JOIN nguoidung n ON t.NguoiTaoId = n.Id
                $where
                ORDER BY t.NgayGiaoDich DESC, t.Id DESC
                LIMIT $offset, $limit";
                
        $result = $this->conn->query($sql);
        
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        
        return [
            'data' => $transactions,
            'total' => $total_records,
            'total_pages' => ceil($total_records / $limit)
        ];
    }
    
    public function getById($id) {
        $sql = "SELECT t.*, n.HoTen as NguoiTao 
                FROM taichinh t
                LEFT JOIN nguoidung n ON t.NguoiTaoId = n.Id
                WHERE t.Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($data) {
        // Validate
        if (empty($data['description']) || !isset($data['amount']) || empty($data['date'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        if ($data['amount'] <= 0) {
            return ['success' => false, 'message' => 'Số tiền phải lớn hơn 0'];
        }
        
        $sql = "INSERT INTO taichinh (MoTa, SoTien, LoaiGiaoDich, NgayGiaoDich, GhiChu, NguoiTaoId) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdissi", 
            $data['description'],
            $data['amount'],
            $data['type'],
            $data['date'],
            $data['note'],
            $_SESSION['user']['id']
        );
        
        if ($stmt->execute()) {
            $transaction_id = $stmt->insert_id;
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Thêm giao dịch tài chính',
                'Thành công',
                "Id: $transaction_id, Số tiền: {$data['amount']}"
            );
            return ['success' => true, 'message' => 'Thêm giao dịch thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function update($id, $data) {
        // Validate tương tự create
        if (empty($data['description']) || !isset($data['amount']) || empty($data['date'])) {
            return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
        }
        
        if ($data['amount'] <= 0) {
            return ['success' => false, 'message' => 'Số tiền phải lớn hơn 0'];
        }
        
        $sql = "UPDATE taichinh 
                SET MoTa = ?, SoTien = ?, LoaiGiaoDich = ?, NgayGiaoDich = ?, GhiChu = ?
                WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sdissi",
            $data['description'],
            $data['amount'],
            $data['type'],
            $data['date'],
            $data['note'],
            $id
        );
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật giao dịch tài chính',
                'Thành công',
                "Id: $id"
            );
            return ['success' => true, 'message' => 'Cập nhật giao dịch thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function delete($id) {
        $sql = "DELETE FROM taichinh WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa giao dịch tài chính',
                'Thành công',
                "Id: $id"
            );
            return ['success' => true, 'message' => 'Xóa giao dịch thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function getStatistics($from_date = null, $to_date = null) {
        $where = "WHERE 1=1";
        if ($from_date) {
            $where .= " AND NgayGiaoDich >= '$from_date'";
        }
        if ($to_date) {
            $where .= " AND NgayGiaoDich <= '$to_date'";
        }
        
        $sql = "SELECT 
                    SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE 0 END) as TongThu,
                    SUM(CASE WHEN LoaiGiaoDich = 0 THEN SoTien ELSE 0 END) as TongChi,
                    SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE -SoTien END) as SoDu,
                    DATE_FORMAT(NgayGiaoDich, '%Y-%m') as Thang
                FROM taichinh
                $where
                GROUP BY DATE_FORMAT(NgayGiaoDich, '%Y-%m')
                ORDER BY Thang DESC";
                
        $result = $this->conn->query($sql);
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        
        return $stats;
    }
} 