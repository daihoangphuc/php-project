<?php
// Bỏ các require vì đã được include từ index.php
// Bỏ phần HTML/CSS vì đã có layout

// Code PHP xử lý
$conn = connectDatabase();
if (!$conn) {
    die("Lỗi kết nối database");
}

// Kiểm tra lỗi query
$result = $conn->query("SELECT * FROM hoatdong");
if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}

// Lấy hoạt động sắp diễn ra
$upcoming_activities = $conn->query("
    SELECT * FROM hoatdong 
    WHERE NgayBatDau > NOW()
    ORDER BY NgayBatDau ASC 
    LIMIT 3
")->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách ban chủ nhiệm
$management_board = $conn->query("
    SELECT nd.*, cv.TenChucVu 
    FROM nguoidung nd
    JOIN chucvu cv ON nd.ChucVuId = cv.Id
    WHERE cv.TenChucVu NOT IN ('Thành viên')
")->fetch_all(MYSQLI_ASSOC);

// Thống kê tổng quan
$stats = [
    'total_members' => $conn->query("SELECT COUNT(*) FROM nguoidung")->fetch_row()[0],
    'total_activities' => $conn->query("SELECT COUNT(*) FROM hoatdong")->fetch_row()[0],
    'total_completed' => $conn->query("SELECT COUNT(*) FROM hoatdong WHERE TrangThai = 'Đã hoàn thành'")->fetch_row()[0]
];
?>

<!-- Banner -->
<div class="relative bg-blue-600 h-96">
    <div class="absolute inset-0">
        <img src="assets/images/banner.jpg" alt="Banner" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-blue-900 opacity-50"></div>
    </div>
    <div class="relative container mx-auto px-4 h-full flex items-center">
        <div class="text-white">
            <h1 class="text-4xl font-bold mb-4">Câu lạc bộ HSTV</h1>
            <p class="text-xl mb-8">Nơi kết nối và phát triển tài năng</p>
            <?php if (!isLoggedIn()): ?>
                <a href="?module=auth&action=register" class="bg-white text-blue-600 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50">
                    Tham gia ngay
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Thống kê -->
<div class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-blue-600 mb-2"><?= number_format($stats['total_members']) ?></div>
                <div class="text-gray-600">Thành viên</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-green-600 mb-2"><?= number_format($stats['total_activities']) ?></div>
                <div class="text-gray-600">Hoạt động</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <div class="text-3xl font-bold text-purple-600 mb-2"><?= number_format($stats['total_completed']) ?></div>
                <div class="text-gray-600">Hoạt động đã hoàn thành</div>
            </div>
        </div>
    </div>
</div>

<!-- Hoạt động sắp diễn ra -->
<div class="py-12">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-8">Hoạt động sắp diễn ra</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php foreach ($upcoming_activities as $activity): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <img src="<?= htmlspecialchars($activity['HinhAnh']) ?>" alt="" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <h3 class="font-bold text-xl mb-2"><?= htmlspecialchars($activity['TenHoatDong']) ?></h3>
                        <p class="text-gray-600 mb-4"><?= htmlspecialchars($activity['MoTa']) ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($activity['NgayBatDau'])) ?>
                            </span>
                            <?php if (isLoggedIn()): ?>
                                <a href="?module=activities&action=register&id=<?= $activity['Id'] ?>" 
                                   class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    Đăng ký
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Ban chủ nhiệm -->
<div class="py-12 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold mb-8">Ban chủ nhiệm</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <?php foreach ($management_board as $member): ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <img src="<?= $member['Avatar'] ?? 'assets/images/default-avatar.png' ?>" 
                         alt="<?= htmlspecialchars($member['HoTen']) ?>"
                         class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                    <h3 class="font-bold mb-1"><?= htmlspecialchars($member['HoTen']) ?></h3>
                    <p class="text-blue-600"><?= htmlspecialchars($member['TenChucVu']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div> 