<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

// Lấy thống kê tổng quan
$stats = [
    'total_members' => $conn->query("SELECT COUNT(*) FROM nguoidung")->fetch_row()[0],
    'total_activities' => $conn->query("SELECT COUNT(*) FROM hoatdong")->fetch_row()[0],
    'active_tasks' => $conn->query("SELECT COUNT(*) FROM nhiemvu WHERE TrangThai = 1")->fetch_row()[0],
    'completed_activities' => $conn->query("SELECT COUNT(*) FROM hoatdong WHERE TrangThai = 2")->fetch_row()[0]
];

// Thống kê hoạt động theo tháng
$activity_stats = $conn->query("
    SELECT 
        DATE_FORMAT(NgayBatDau, '%Y-%m') as month,
        COUNT(*) as total,
        COUNT(CASE WHEN TrangThai = 2 THEN 1 END) as completed
    FROM hoatdong
    WHERE NgayBatDau >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(NgayBatDau, '%Y-%m')
    ORDER BY month ASC
")->fetch_all(MYSQLI_ASSOC);

// Thống kê tham gia hoạt động
$participation_stats = $conn->query("
    SELECT 
        h.Id,
        h.TenHoatDong,
        COUNT(DISTINCT dk.NguoiDungId) as registered,
        COUNT(DISTINCT CASE WHEN tg.TrangThai = 1 THEN tg.NguoiDungId END) as attended
    FROM hoatdong h
    LEFT JOIN danhsachdangky dk ON h.Id = dk.HoatDongId
    LEFT JOIN danhsachthamgia tg ON h.Id = tg.HoatDongId
    WHERE h.NgayBatDau >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
    GROUP BY h.Id
    ORDER BY h.NgayBatDau DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Báo cáo & Thống kê</h1>
        <button onclick="exportToExcel()" 
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
            Xuất Excel
        </button>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-blue-100 p-6 rounded-lg">
            <h3 class="text-blue-800 font-semibold mb-2">Tổng thành viên</h3>
            <p class="text-2xl text-blue-600"><?= number_format($stats['total_members']) ?></p>
        </div>
        <div class="bg-green-100 p-6 rounded-lg">
            <h3 class="text-green-800 font-semibold mb-2">Tổng hoạt động</h3>
            <p class="text-2xl text-green-600"><?= number_format($stats['total_activities']) ?></p>
        </div>
        <div class="bg-yellow-100 p-6 rounded-lg">
            <h3 class="text-yellow-800 font-semibold mb-2">Nhiệm vụ đang thực hiện</h3>
            <p class="text-2xl text-yellow-600"><?= number_format($stats['active_tasks']) ?></p>
        </div>
        <div class="bg-purple-100 p-6 rounded-lg">
            <h3 class="text-purple-800 font-semibold mb-2">Hoạt động đã hoàn thành</h3>
            <p class="text-2xl text-purple-600"><?= number_format($stats['completed_activities']) ?></p>
        </div>
    </div>

    <!-- Biểu đồ hoạt động -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Thống kê hoạt động theo tháng</h2>
        <canvas id="activityChart"></canvas>
    </div>

    <!-- Biểu đồ tham gia -->
    <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-xl font-semibold mb-4">Thống kê tham gia hoạt động</h2>
        <canvas id="participationChart"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script>
// Biểu đồ hoạt động
const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($item) {
            return date('m/Y', strtotime($item['month'] . '-01'));
        }, $activity_stats)) ?>,
        datasets: [{
            label: 'Tổng số',
            data: <?= json_encode(array_map(function($item) {
                return $item['total'];
            }, $activity_stats)) ?>,
            borderColor: 'rgb(59, 130, 246)',
            tension: 0.1
        }, {
            label: 'Hoàn thành',
            data: <?= json_encode(array_map(function($item) {
                return $item['completed'];
            }, $activity_stats)) ?>,
            borderColor: 'rgb(16, 185, 129)',
            tension: 0.1
        }]
    }
});

// Biểu đồ tham gia
const participationCtx = document.getElementById('participationChart').getContext('2d');
new Chart(participationCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(function($item) {
            return $item['TenHoatDong'];
        }, $participation_stats)) ?>,
        datasets: [{
            label: 'Đăng ký',
            data: <?= json_encode(array_map(function($item) {
                return $item['registered'];
            }, $participation_stats)) ?>,
            backgroundColor: 'rgba(59, 130, 246, 0.5)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1
        }, {
            label: 'Tham gia',
            data: <?= json_encode(array_map(function($item) {
                return $item['attended'];
            }, $participation_stats)) ?>,
            backgroundColor: 'rgba(16, 185, 129, 0.5)',
            borderColor: 'rgb(16, 185, 129)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Xuất Excel
function exportToExcel() {
    const data = {
        'Thống kê tổng quan': [
            ['Chỉ số', 'Số lượng'],
            ['Tổng thành viên', <?= $stats['total_members'] ?>],
            ['Tổng hoạt động', <?= $stats['total_activities'] ?>],
            ['Nhiệm vụ đang thực hiện', <?= $stats['active_tasks'] ?>],
            ['Hoạt động đã hoàn thành', <?= $stats['completed_activities'] ?>]
        ],
        'Thống kê hoạt động': <?= json_encode(array_map(function($item) {
            return [
                'Tháng' => date('m/Y', strtotime($item['month'] . '-01')),
                'Tổng số' => $item['total'],
                'Hoàn thành' => $item['completed']
            ];
        }, $activity_stats)) ?>,
        'Thống kê tham gia': <?= json_encode(array_map(function($item) {
            return [
                'Ho���t động' => $item['TenHoatDong'],
                'Đăng ký' => $item['registered'],
                'Tham gia' => $item['attended']
            ];
        }, $participation_stats)) ?>
    };

    const wb = XLSX.utils.book_new();
    
    // Thêm các sheet
    Object.entries(data).forEach(([name, content]) => {
        const ws = XLSX.utils.json_to_sheet(content);
        XLSX.utils.book_append_sheet(wb, ws, name);
    });

    // Tải file
    XLSX.writeFile(wb, 'bao-cao-thong-ke.xlsx');
}
</script> 