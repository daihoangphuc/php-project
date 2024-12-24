<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Thống kê thành viên
$memberStats = [
    'total' => $conn->query("SELECT COUNT(*) FROM nguoidung")->fetch_row()[0],
    'by_gender' => $conn->query("
        SELECT GioiTinh, COUNT(*) as count 
        FROM nguoidung 
        GROUP BY GioiTinh
    ")->fetch_all(MYSQLI_ASSOC),
    'by_class' => $conn->query("
        SELECT l.TenLop, COUNT(*) as count 
        FROM nguoidung n
        JOIN lophoc l ON n.LopId = l.Id
        GROUP BY l.Id
    ")->fetch_all(MYSQLI_ASSOC),
    'by_position' => $conn->query("
        SELECT c.TenChucVu, COUNT(*) as count 
        FROM nguoidung n
        JOIN chucvu c ON n.ChucVuId = c.Id
        GROUP BY c.Id
    ")->fetch_all(MYSQLI_ASSOC)
];

// Thống kê hoạt động
$activityStats = [
    'total' => $conn->query("SELECT COUNT(*) FROM hoatdong")->fetch_row()[0],
    'by_status' => $conn->query("
        SELECT TrangThai, COUNT(*) as count 
        FROM hoatdong 
        GROUP BY TrangThai
    ")->fetch_all(MYSQLI_ASSOC),
    'participation' => $conn->query("
        SELECT h.TenHoatDong,
            COUNT(DISTINCT d.NguoiDungId) as registered,
            SUM(CASE WHEN d.TrangThai = 1 THEN 1 ELSE 0 END) as attended
        FROM hoatdong h
        LEFT JOIN danhsachthamgia d ON h.Id = d.HoatDongId
        GROUP BY h.Id
    ")->fetch_all(MYSQLI_ASSOC)
];

// Thống kê nhiệm vụ
$taskStats = [
    'total' => $conn->query("SELECT COUNT(*) FROM nhiemvu")->fetch_row()[0],
    'by_status' => $conn->query("
        SELECT TrangThai, COUNT(*) as count 
        FROM nhiemvu 
        GROUP BY TrangThai
    ")->fetch_all(MYSQLI_ASSOC),
    'by_assignee' => $conn->query("
        SELECT n.HoTen, COUNT(*) as count 
        FROM nhiemvu nv
        JOIN nguoidung n ON nv.NguoiThucHienId = n.Id
        GROUP BY n.Id
    ")->fetch_all(MYSQLI_ASSOC)
];
?>

<div class="container mx-auto px-4">
    <!-- Thống kê thành viên -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6">Thống kê thành viên</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Theo giới tính</h3>
                <canvas id="genderChart"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Theo lớp</h3>
                <canvas id="classChart"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Theo chức vụ</h3>
                <canvas id="positionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Thống kê hoạt động -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6">Thống kê hoạt động</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Trạng thái hoạt động</h3>
                <canvas id="activityStatusChart"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Tỷ lệ tham gia</h3>
                <canvas id="participationChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Thống kê nhiệm vụ -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6">Thống kê nhiệm vụ</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Trạng thái nhiệm vụ</h3>
                <canvas id="taskStatusChart"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Phân công nhiệm vụ</h3>
                <canvas id="taskAssignmentChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Khởi tạo các biểu đồ
const memberStats = <?= json_encode($memberStats) ?>;
const activityStats = <?= json_encode($activityStats) ?>;
const taskStats = <?= json_encode($taskStats) ?>;

// Biểu đồ giới tính
new Chart(document.getElementById('genderChart'), {
    type: 'pie',
    data: {
        labels: memberStats.by_gender.map(item => item.GioiTinh),
        datasets: [{
            data: memberStats.by_gender.map(item => item.count),
            backgroundColor: ['#FF6384', '#36A2EB']
        }]
    }
});

// Các biểu đồ khác tương tự...
</script> 