<?php
class Statistics {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function getOverview() {
        // Thống kê tổng quan
        $stats = [
            'total_users' => 0,
            'total_activities' => 0,
            'total_tasks' => 0,
            'total_news' => 0,
            'total_income' => 0,
            'total_expense' => 0,
            'balance' => 0,
            'active_users' => 0
        ];
        
        // Đếm tổng số người dùng
        $sql = "SELECT COUNT(*) as total FROM nguoidung WHERE TranThai = 1";
        $result = $this->conn->query($sql);
        $stats['total_users'] = $result->fetch_assoc()['total'];
        
        // Đếm số người dùng hoạt động (có tham gia hoạt động trong 30 ngày)
        $sql = "SELECT COUNT(DISTINCT NguoiDungId) as total 
                FROM danhsachthamgia 
                WHERE DiemDanhLuc >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->conn->query($sql);
        $stats['active_users'] = $result->fetch_assoc()['total'];
        
        // ��ếm tổng số hoạt động
        $sql = "SELECT COUNT(*) as total FROM hoatdong";
        $result = $this->conn->query($sql);
        $stats['total_activities'] = $result->fetch_assoc()['total'];
        
        // Đếm tổng số nhiệm vụ
        $sql = "SELECT COUNT(*) as total FROM nhiemvu";
        $result = $this->conn->query($sql);
        $stats['total_tasks'] = $result->fetch_assoc()['total'];
        
        // Đếm tổng số tin tức
        $sql = "SELECT COUNT(*) as total FROM tintuc";
        $result = $this->conn->query($sql);
        $stats['total_news'] = $result->fetch_assoc()['total'];
        
        // Tính tổng thu chi
        $sql = "SELECT 
                SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE 0 END) as total_income,
                SUM(CASE WHEN LoaiGiaoDich = 0 THEN SoTien ELSE 0 END) as total_expense
                FROM taichinh";
        $result = $this->conn->query($sql);
        $finance = $result->fetch_assoc();
        $stats['total_income'] = $finance['total_income'] ?? 0;
        $stats['total_expense'] = $finance['total_expense'] ?? 0;
        $stats['balance'] = $stats['total_income'] - $stats['total_expense'];
        
        return $stats;
    }
    
    public function getActivityStats($start_date = null, $end_date = null) {
        $where = "WHERE 1=1";
        if ($start_date) {
            $where .= " AND NgayBatDau >= '$start_date'";
        }
        if ($end_date) {
            $where .= " AND NgayKetThuc <= '$end_date'";
        }
        
        // Thống kê hoạt động theo tháng
        $sql = "SELECT 
                    DATE_FORMAT(NgayBatDau, '%Y-%m') as month,
                    COUNT(*) as total_activities,
                    SUM(SoLuong) as total_slots,
                    COUNT(DISTINCT d.NguoiDungId) as total_participants
                FROM hoatdong h
                LEFT JOIN danhsachthamgia d ON h.Id = d.HoatDongId
                $where
                GROUP BY DATE_FORMAT(NgayBatDau, '%Y-%m')
                ORDER BY month DESC";
                
        $result = $this->conn->query($sql);
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        return $stats;
    }
    
    public function getTaskStats($start_date = null, $end_date = null) {
        $where = "WHERE 1=1";
        if ($start_date) {
            $where .= " AND NgayBatDau >= '$start_date'";
        }
        if ($end_date) {
            $where .= " AND NgayKetThuc <= '$end_date'";
        }
        
        // Thống kê nhiệm vụ theo trạng thái và người thực hiện
        $sql = "SELECT 
                    n.TrangThai,
                    COUNT(*) as total_tasks,
                    COUNT(DISTINCT p.NguoiDungId) as total_assignees
                FROM nhiemvu n
                LEFT JOIN phancongnhiemvu p ON n.Id = p.NhiemVuId
                $where
                GROUP BY n.TrangThai";
                
        $result = $this->conn->query($sql);
        $stats = [];
        while ($row = $result->fetch_assoc()) {
            $stats[] = $row;
        }
        return $stats;
    }
    
    public function getFinanceStats($start_date = null, $end_date = null) {
        $where = "WHERE 1=1";
        if ($start_date) {
            $where .= " AND DATE(NgayGiaoDich) >= '$start_date'";
        }
        if ($end_date) {
            $where .= " AND DATE(NgayGiaoDich) <= '$end_date'";
        }
        
        // Thống kê tài chính theo tháng
        $sql = "SELECT 
                    DATE_FORMAT(NgayGiaoDich, '%Y-%m') as month,
                    SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE 0 END) as income,
                    SUM(CASE WHEN LoaiGiaoDich = 0 THEN SoTien ELSE 0 END) as expense,
                    COUNT(*) as total_transactions
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
    
    public function getUserStats() {
        // Thống kê người dùng theo vai trò
        $sql = "SELECT 
                    v.TenVaiTro,
                    COUNT(DISTINCT vn.NguoiDungId) as total_users
                FROM vaitro v
                LEFT JOIN vaitronguoidung vn ON v.Id = vn.VaiTroId
                GROUP BY v.Id, v.TenVaiTro";
                
        $result = $this->conn->query($sql);
        $role_stats = [];
        while ($row = $result->fetch_assoc()) {
            $role_stats[] = $row;
        }
        
        // Thống kê người dùng theo lớp
        $sql = "SELECT 
                    l.TenLop,
                    k.TenKhoaTruong,
                    COUNT(*) as total_users
                FROM nguoidung n
                JOIN lophoc l ON n.LopHocId = l.Id
                JOIN khoatruong k ON l.KhoaTruongId = k.Id
                WHERE n.TranThai = 1
                GROUP BY l.Id, l.TenLop, k.TenKhoaTruong
                ORDER BY k.TenKhoaTruong, l.TenLop";
                
        $result = $this->conn->query($sql);
        $class_stats = [];
        while ($row = $result->fetch_assoc()) {
            $class_stats[] = $row;
        }
        
        return [
            'role_stats' => $role_stats,
            'class_stats' => $class_stats
        ];
    }
} 