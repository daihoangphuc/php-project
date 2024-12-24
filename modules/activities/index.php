<?php
// Không cần require vì đã được include từ init.php
redirectIfNotAdmin();

// Lấy danh sách hoạt động
$activities = $conn->query("
    SELECT h.*
    FROM hoatdong h
    ORDER BY h.NgayBatDau DESC
")->fetch_all(MYSQLI_ASSOC);

// Lấy số lượng đăng ký theo hoạt động
$registrations = $conn->query("
    SELECT HoatDongId, COUNT(*) as SoLuong
    FROM danhsachdangky
    WHERE TrangThai = 1
    GROUP BY HoatDongId
")->fetch_all(MYSQLI_ASSOC);

// Lấy số lượng tham gia thực tế
$participants = $conn->query("
    SELECT HoatDongId, COUNT(*) as SoLuong
    FROM danhsachthamgia
    GROUP BY HoatDongId
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Quản lý hoạt động';
?>

<!-- HTML -->
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Danh sách hoạt động</h2>
            <a href="?module=activities&action=add" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                Thêm hoạt động mới
            </a>
        </div>

        <!-- Bảng danh sách -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Tên hoạt động
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Thời gian
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Địa điểm
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Số lượng
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Trạng thái
                        </th>
                        <th class="px-6 py-3 border-b border-gray-200 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider">
                            Thao tác
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($activities as $activity): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <div class="text-sm leading-5 font-medium text-gray-900">
                                    <?= htmlspecialchars($activity['TenHoatDong']) ?>
                                </div>
                                <div class="text-sm leading-5 text-gray-500">
                                    <?= htmlspecialchars($activity['MoTa']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <div class="text-sm leading-5 text-gray-900">
                                    <?= date('d/m/Y H:i', strtotime($activity['NgayBatDau'])) ?>
                                </div>
                                <div class="text-sm leading-5 text-gray-500">
                                    đến <?= date('d/m/Y H:i', strtotime($activity['NgayKetThuc'])) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <div class="text-sm leading-5 text-gray-900">
                                    <?= htmlspecialchars($activity['DiaDiem']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <?php
                                $registered = 0;
                                $participated = 0;
                                foreach ($registrations as $reg) {
                                    if ($reg['HoatDongId'] == $activity['Id']) {
                                        $registered = $reg['SoLuong'];
                                        break;
                                    }
                                }
                                foreach ($participants as $part) {
                                    if ($part['HoatDongId'] == $activity['Id']) {
                                        $participated = $part['SoLuong'];
                                        break;
                                    }
                                }
                                ?>
                                <div class="text-sm leading-5 text-gray-900">
                                    Đăng ký: <?= $registered ?>/<?= $activity['SoLuong'] ?>
                                </div>
                                <div class="text-sm leading-5 text-gray-500">
                                    Tham gia: <?= $participated ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap">
                                <?php
                                $status_class = 'bg-gray-100 text-gray-800';
                                $status_text = 'Không xác định';
                                switch ($activity['TrangThai']) {
                                    case 0:
                                        $status_class = 'bg-green-100 text-green-800';
                                        $status_text = 'Sắp diễn ra';
                                        break;
                                    case 1:
                                        $status_class = 'bg-blue-100 text-blue-800';
                                        $status_text = 'Đang diễn ra';
                                        break;
                                    case 2:
                                        $status_class = 'bg-gray-100 text-gray-800';
                                        $status_text = 'Đã kết thúc';
                                        break;
                                }
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $status_class ?>">
                                    <?= $status_text ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-no-wrap text-right text-sm leading-5 font-medium">
                                <a href="?module=activities&action=view&id=<?= $activity['Id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Chi tiết</a>
                                <a href="?module=activities&action=edit&id=<?= $activity['Id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Sửa</a>
                                <a href="?module=activities&action=delete&id=<?= $activity['Id'] ?>" class="text-red-600 hover:text-red-900" onclick="return confirm('Bạn có chắc chắn muốn xóa hoạt động này?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div> 