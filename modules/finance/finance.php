<?php
class Finance {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getAll($page = 1, $limit = 10, $search = '', $type = null, $start_date = null, $end_date = null) {
        $offset = ($page - 1) * $limit;
        
        $where = "WHERE 1=1";
        if (!empty($search)) {
            $search = $this->conn->real_escape_string($search);
            $where .= " AND MoTa LIKE '%$search%'";
        }
        if ($type !== null) {
            $type = (int)$type;
            $where .= " AND LoaiGiaoDich = $type";
        }
        if ($start_date) {
            $where .= " AND DATE(NgayGiaoDich) >= '$start_date'";
        }
        if ($end_date) {
            $where .= " AND DATE(NgayGiaoDich) <= '$end_date'";
        }
        
        $sql = "SELECT t.*, n.HoTen, n.MaSinhVien 
                FROM taichinh t
                LEFT JOIN nguoidung n ON t.NguoiDungId = n.Id
                $where 
                ORDER BY t.NgayGiaoDich DESC 
                LIMIT ?, ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $offset, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        
        // Đếm tổng số bản ghi
        $count_sql = "SELECT COUNT(*) as total FROM taichinh t $where";
        $count_result = $this->conn->query($count_sql);
        $total = $count_result->fetch_assoc()['total'];
        
        // Tính tổng thu chi
        $stats_sql = "SELECT 
                        SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE 0 END) as total_income,
                        SUM(CASE WHEN LoaiGiaoDich = 0 THEN SoTien ELSE 0 END) as total_expense
                     FROM taichinh t $where";
        $stats_result = $this->conn->query($stats_sql);
        $stats = $stats_result->fetch_assoc();
        
        return [
            'transactions' => $transactions,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'total_income' => $stats['total_income'] ?? 0,
            'total_expense' => $stats['total_expense'] ?? 0,
            'balance' => ($stats['total_income'] ?? 0) - ($stats['total_expense'] ?? 0)
        ];
    }
    
    public function getById($id) {
        $sql = "SELECT t.*, n.HoTen, n.MaSinhVien 
                FROM taichinh t
                LEFT JOIN nguoidung n ON t.NguoiDungId = n.Id
                WHERE t.Id = ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function create($data) {
        // Validate dữ liệu
        $required_fields = ['amount', 'type', 'date'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
            }
        }
        
        if ($data['amount'] <= 0) {
            return ['success' => false, 'message' => 'Số tiền phải lớn hơn 0'];
        }
        
        $sql = "INSERT INTO taichinh (LoaiGiaoDich, SoTien, MoTa, NgayGiaoDich, NguoiDungId) 
                VALUES (?, ?, ?, ?, ?)";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissi", 
            $data['type'],
            $data['amount'],
            $data['description'],
            $data['date'],
            $data['user_id']
        );
        
        if ($stmt->execute()) {
            $transaction_id = $stmt->insert_id;
            
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Thêm giao dịch tài chính',
                'Thành công',
                "TransactionId: $transaction_id"
            );
            
            return ['success' => true, 'message' => 'Thêm giao dịch thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function update($id, $data) {
        // Validate dữ liệu
        $required_fields = ['amount', 'type', 'date'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
            }
        }
        
        if ($data['amount'] <= 0) {
            return ['success' => false, 'message' => 'Số tiền phải lớn hơn 0'];
        }
        
        $sql = "UPDATE taichinh SET 
                LoaiGiaoDich = ?,
                SoTien = ?,
                MoTa = ?,
                NgayGiaoDich = ?,
                NguoiDungId = ?
                WHERE Id = ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iissii", 
            $data['type'],
            $data['amount'],
            $data['description'],
            $data['date'],
            $data['user_id'],
            $id
        );
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật giao dịch tài chính',
                'Thành công',
                "TransactionId: $id"
            );
            
            return ['success' => true, 'message' => 'Cập nhật giao dịch thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function delete($id) {
        // Kiểm tra giao dịch tồn tại
        $check_sql = "SELECT Id FROM taichinh WHERE Id = ? LIMIT 1";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            return ['success' => false, 'message' => 'Giao dịch không tồn tại'];
        }
        
        // Xóa giao dịch
        $sql = "DELETE FROM taichinh WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Xóa giao dịch tài chính',
                'Thành công',
                "TransactionId: $id"
            );
            
            return ['success' => true, 'message' => 'Xóa giao dịch thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function getStats($start_date = null, $end_date = null) {
        $where = "WHERE 1=1";
        if ($start_date) {
            $where .= " AND DATE(NgayGiaoDich) >= '$start_date'";
        }
        if ($end_date) {
            $where .= " AND DATE(NgayGiaoDich) <= '$end_date'";
        }
        
        $sql = "SELECT 
                    DATE_FORMAT(NgayGiaoDich, '%Y-%m') as month,
                    SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE 0 END) as income,
                    SUM(CASE WHEN LoaiGiaoDich = 0 THEN SoTien ELSE 0 END) as expense
                FROM taichinh
                $where
                GROUP BY DATE_FORMAT(NgayGiaoDich, '%Y-%m')
                ORDER BY month DESC";
                
        $result = $this->conn->query($sql);
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        return $stats;
    }
} 