<?php
require_once 'modules/activities/activities.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$activities = new Activities($conn);

// Lấy thông tin hoạt động
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$activity = $activities->getById($id);

if (!$activity) {
    $_SESSION['error'] = 'Hoạt động không tồn tại';
    header('Location: index.php?module=activities');
    exit;
}

// Lấy danh sách sinh viên đăng ký
$registrations = $activities->getRegistrations($id);
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Chi tiết hoạt động</h2>
            <div class="flex space-x-4">
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <a href="index.php?module=activities&action=edit&id=<?php echo $id; ?>"
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-edit mr-2"></i>
                        Sửa hoạt động
                    </a>
                <?php endif; ?>
                <a href="index.php?module=activities" class="text-blue-500 hover:underline">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Quay lại danh sách
                </a>
            </div>
        </div>

        <!-- Thông tin hoạt động -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <h3 class="text-lg font-medium mb-4">Thông tin cơ bản</h3>
                <div class="space-y-3">
                    <div>
                        <span class="font-medium">Tên hoạt động:</span>
                        <span class="ml-2"><?php echo htmlspecialchars($activity['TenHoatDong']); ?></span>
                    </div>
                    <div>
                        <span class="font-medium">Thời gian:</span>
                        <div class="ml-2">
                            Bắt đầu: <?php echo date('H:i d/m/Y', strtotime($activity['NgayBatDau'])); ?>
                            <br>
                            Kết thúc: <?php echo date('H:i d/m/Y', strtotime($activity['ThoiGianKetThuc'])); ?>
                        </div>
                    </div>
                    <div>
                        <span class="font-medium">Địa điểm:</span>
                        <span class="ml-2"><?php echo htmlspecialchars($activity['DiaDiem']); ?></span>
                    </div>
                    <div>
                        <span class="font-medium">Số lượng:</span>
                        <span class="ml-2"><?php echo $activity['SoLuongDangKy']; ?>/<?php echo $activity['SoLuongToiDa']; ?></span>
                    </div>
                    <div>
                        <span class="font-medium">Mô tả:</span>
                        <div class="ml-2 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($activity['MoTa'])); ?></div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium mb-4">Danh sách sinh viên đăng ký</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">Họ tên</th>
                                <th scope="col" class="px-6 py-3">MSSV</th>
                                <th scope="col" class="px-6 py-3">Thời gian đăng ký</th>
                                <th scope="col" class="px-6 py-3">Điểm danh</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg): ?>
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($reg['HoTen']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($reg['MaSinhVien']); ?></td>
                                    <td class="px-6 py-4">
                                        <?php echo date('H:i d/m/Y', strtotime($reg['ThoiGianDangKy'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($reg['DiemDanh'] === null): ?>
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                Chưa điểm danh
                                            </span>
                                        <?php elseif ($reg['DiemDanh'] == 1): ?>
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                Có mặt
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                Vắng mặt
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 