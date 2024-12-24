<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

class Statistics {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Thống kê thành viên
    public function getMemberStats() {
        $stats = [];
        
        // Tổng số thành viên
        $sql = "SELECT COUNT(*) as total FROM nguoidung";
        $result = $this->conn->query($sql);
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Thống kê theo lớp
        $sql = "SELECT l.TenLop, COUNT(n.Id) as count 
                FROM lophoc l 
                LEFT JOIN nguoidung n ON l.Id = n.LopHocId 
                GROUP BY l.Id";
        $result = $this->conn->query($sql);
        $stats['by_class'] = $result->fetch_all(MYSQLI_ASSOC);
        
        // Thống kê theo chức vụ
        $sql = "SELECT c.TenChucVu, COUNT(n.Id) as count 
                FROM chucvu c 
                LEFT JOIN nguoidung n ON c.Id = n.ChucVuId 
                GROUP BY c.Id";
        $result = $this->conn->query($sql);
        $stats['by_position'] = $result->fetch_all(MYSQLI_ASSOC);
        
        // Thống kê theo giới tính
        $sql = "SELECT GioiTinh, COUNT(*) as count 
                FROM nguoidung 
                GROUP BY GioiTinh";
        $result = $this->conn->query($sql);
        $stats['by_gender'] = $result->fetch_all(MYSQLI_ASSOC);
        
        return $stats;
    }
    
    // Thống kê hoạt động
    public function getActivityStats() {
        $stats = [];
        
        // Tổng số hoạt động
        $sql = "SELECT COUNT(*) as total FROM hoatdong";
        $result = $this->conn->query($sql);
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Thống kê theo trạng thái
        $sql = "SELECT TrangThai, COUNT(*) as count 
                FROM hoatdong 
                GROUP BY TrangThai";
        $result = $this->conn->query($sql);
        $stats['by_status'] = $result->fetch_all(MYSQLI_ASSOC);
        
        // Tỷ lệ tham gia
        $sql = "SELECT h.Id, h.TenHoatDong,
                COUNT(DISTINCT dk.NguoiDungId) as registered,
                COUNT(DISTINCT tg.NguoiDungId) as attended
                FROM hoatdong h
                LEFT JOIN danhsachdangky dk ON h.Id = dk.HoatDongId
                LEFT JOIN danhsachthamgia tg ON h.Id = tg.HoatDongId
                GROUP BY h.Id";
        $result = $this->conn->query($sql);
        $stats['participation'] = $result->fetch_all(MYSQLI_ASSOC);
        
        return $stats;
    }
    
    // Thống kê nhiệm vụ
    public function getTaskStats() {
        $stats = [];
        
        // Tổng số nhiệm vụ
        $sql = "SELECT COUNT(*) as total FROM nhiemvu";
        $result = $this->conn->query($sql);
        $stats['total'] = $result->fetch_assoc()['total'];
        
        // Thống kê theo trạng thái
        $sql = "SELECT TrangThai, COUNT(*) as count 
                FROM nhiemvu 
                GROUP BY TrangThai";
        $result = $this->conn->query($sql);
        $stats['by_status'] = $result->fetch_all(MYSQLI_ASSOC);
        
        // Thống kê theo người được giao
        $sql = "SELECT n.HoTen, COUNT(nv.Id) as count 
                FROM nguoidung n
                LEFT JOIN phancong_nhiemvu pn ON n.Id = pn.NguoiDungId
                LEFT JOIN nhiemvu nv ON pn.NhiemVuId = nv.Id
                GROUP BY n.Id";
        $result = $this->conn->query($sql);
        $stats['by_assignee'] = $result->fetch_all(MYSQLI_ASSOC);
        
        return $stats;
    }
}

// Khởi tạo đối tượng thống kê
$stats = new Statistics($conn);
$memberStats = $stats->getMemberStats();
$activityStats = $stats->getActivityStats();
$taskStats = $stats->getTaskStats();
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Thống kê tổng quan</h1>
    
    <!-- Thống kê thành viên -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Thống kê thành viên</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Tổng số thành viên</h3>
                <p class="text-3xl font-bold text-blue-600"><?= $memberStats['total'] ?></p>
            </div>
            
            <!-- Biểu đồ phân bố theo lớp -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Phân bố theo lớp</h3>
                <canvas id="classChart"></canvas>
            </div>
            
            <!-- Biểu đồ phân bố theo chức vụ -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Phân bố theo chức vụ</h3>
                <canvas id="positionChart"></canvas>
            </div>
            
            <!-- Biểu đồ giới tính -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Phân bố giới tính</h3>
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Thống kê hoạt động -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Thống kê hoạt động</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Tổng số hoạt động</h3>
                <p class="text-3xl font-bold text-green-600"><?= $activityStats['total'] ?></p>
            </div>
            
            <!-- Biểu đồ trạng thái hoạt động -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Trạng thái hoạt động</h3>
                <canvas id="activityStatusChart"></canvas>
            </div>
            
            <!-- Biểu đồ tỷ lệ tham gia -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Tỷ lệ tham gia</h3>
                <canvas id="participationChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Thống kê nhiệm vụ -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-4">Thống kê nhiệm vụ</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Tổng số nhiệm vụ</h3>
                <p class="text-3xl font-bold text-purple-600"><?= $taskStats['total'] ?></p>
            </div>
            
            <!-- Biểu đồ trạng thái nhiệm vụ -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Trạng thái nhiệm vụ</h3>
                <canvas id="taskStatusChart"></canvas>
            </div>
            
            <!-- Biểu đồ phân công nhiệm vụ -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-2">Phân công nhiệm vụ</h3>
                <canvas id="taskAssignmentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Script khởi tạo biểu đồ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dữ liệu cho biểu đồ
    const memberStats = <?= json_encode($memberStats) ?>;
    const activityStats = <?= json_encode($activityStats) ?>;
    const taskStats = <?= json_encode($taskStats) ?>;
    
    // Khởi tạo các biểu đồ
    new Chart(document.getElementById('classChart'), {
        type: 'pie',
        data: {
            labels: memberStats.by_class.map(item => item.TenLop),
            datasets: [{
                data: memberStats.by_class.map(item => item.count),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        }
    });
    
    // Khởi tạo các biểu đồ khác tương tự...
});
</script> 