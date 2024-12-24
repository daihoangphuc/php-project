<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Thống kê tổng quan
$stats = [
    'total_members' => $conn->query("SELECT COUNT(*) FROM nguoidung")->fetch_row()[0],
    'total_activities' => $conn->query("SELECT COUNT(*) FROM hoatdong")->fetch_row()[0],
    'total_tasks' => $conn->query("SELECT COUNT(*) FROM nhiemvu")->fetch_row()[0],
    'total_documents' => $conn->query("SELECT COUNT(*) FROM tailieu")->fetch_row()[0]
];

// Hoạt động gần đây
$recent_activities = $conn->query("
    SELECT n.*, u.HoTen 
    FROM nhatky n
    LEFT JOIN nguoidung u ON n.NguoiDung = u.username
    ORDER BY n.ThoiGian DESC 
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Thống kê hoạt động theo trạng thái
$activity_stats = $conn->query("
    SELECT TrangThai, COUNT(*) as total FROM hoatdong 
    GROUP BY TrangThai
")->fetch_all(MYSQLI_ASSOC);

// Thống kê tài chính tháng này
$finance_stats = $conn->query("
    SELECT 
        SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE 0 END) as TongThu,
        SUM(CASE WHEN LoaiGiaoDich = 0 THEN SoTien ELSE 0 END) as TongChi
    FROM taichinh 
    WHERE MONTH(NgayGiaoDich) = MONTH(CURRENT_DATE())
    AND YEAR(NgayGiaoDich) = YEAR(CURRENT_DATE())
")->fetch_assoc();
?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-8">Dashboard</h1>

    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Tổng thành viên</p>
                    <p class="text-2xl font-semibold text-gray-800"><?= number_format($stats['total_members']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Hoạt động</p>
                    <p class="text-2xl font-semibold text-gray-800"><?= number_format($stats['total_activities']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Nhiệm vụ</p>
                    <p class="text-2xl font-semibold text-gray-800"><?= number_format($stats['total_tasks']) ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-gray-500">Tài liệu</p>
                    <p class="text-2xl font-semibold text-gray-800"><?= number_format($stats['total_documents']) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Thống kê tài chính -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Tài chính tháng này</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-gray-500">Tổng thu</p>
                    <p class="text-2xl font-semibold text-green-600">
                        <?= number_format($finance_stats['TongThu']) ?> đ
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Tổng chi</p>
                    <p class="text-2xl font-semibold text-red-600">
                        <?= number_format($finance_stats['TongChi']) ?> đ
                    </p>
                </div>
            </div>
        </div>

        <!-- Thống kê hoạt động -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Trạng thái hoạt động</h2>
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <!-- Hoạt động gần đây -->
    <div class="mt-8 bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h2 class="text-xl font-semibold">Hoạt động gần đây</h2>
        </div>
        <div class="p-6">
            <div class="flow-root">
                <ul class="-mb-8">
                    <?php foreach ($recent_activities as $activity): ?>
                        <li class="relative pb-8">
                            <div class="relative flex items-start space-x-3">
                                <div class="min-w-0 flex-1">
                                    <div>
                                        <div class="text-sm">
                                            <span class="font-medium text-gray-900">
                                                <?= htmlspecialchars($activity['HoTen']) ?>
                                            </span>
                                            <span class="text-gray-500">
                                                <?= htmlspecialchars($activity['HanhDong']) ?>
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-sm text-gray-500">
                                            <?= date('d/m/Y H:i', strtotime($activity['ThoiGian'])) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ hoạt động
const activityData = <?= json_encode($activity_stats) ?>;
new Chart(document.getElementById('activityChart'), {
    type: 'doughnut',
    data: {
        labels: ['Đã hủy', 'Đang diễn ra', 'Đã kết thúc'],
        datasets: [{
            data: activityData.map(item => item.total),
            backgroundColor: ['#EF4444', '#3B82F6', '#10B981']
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script> 