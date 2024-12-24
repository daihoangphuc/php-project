<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!is_admin()) {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die("ID hoạt động không hợp lệ!");
}

// Lấy thông tin hoạt động
$sql = "SELECT * FROM hoatdong WHERE Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$activity = $stmt->get_result()->fetch_assoc();

if (!$activity) {
    die("Hoạt động không tồn tại!");
}

// Lấy danh sách người đăng ký
$sql = "SELECT dk.*, nd.HoTen, nd.MaSinhVien 
        FROM dangkyhoatdong dk
        JOIN nguoidung nd ON dk.NguoiDungId = nd.Id
        WHERE dk.HoatDongId = ?
        ORDER BY nd.HoTen";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Xử lý điểm danh
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendees = $_POST['attendance'] ?? [];
    
    // Cập nhật trạng thái tham gia
    $stmt = $conn->prepare("UPDATE dangkyhoatdong SET TrangThai = 0 WHERE HoatDongId = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    if (!empty($attendees)) {
        $stmt = $conn->prepare("UPDATE dangkyhoatdong SET TrangThai = 1 WHERE HoatDongId = ? AND NguoiDungId = ?");
        foreach ($attendees as $userId) {
            $stmt->bind_param("ii", $id, $userId);
            $stmt->execute();
        }
    }
    
    // Log hoạt động
    logActivity(
        $_SERVER['REMOTE_ADDR'],
        $_SESSION['user']['username'],
        'Điểm danh hoạt động',
        'Thành công',
        "ActivityId: $id"
    );
    
    header('Location: ?module=activities&action=view&id=' . $id);
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Điểm danh hoạt động</h1>
        
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4"><?= htmlspecialchars($activity['TenHoatDong']) ?></h2>
            
            <div class="space-y-4 mb-6">
                <div>
                    <span class="text-gray-600">Thời gian:</span>
                    <span class="ml-2">
                        <?= date('d/m/Y H:i', strtotime($activity['NgayBatDau'])) ?>
                        đ���n
                        <?= date('d/m/Y H:i', strtotime($activity['NgayKetThuc'])) ?>
                    </span>
                </div>
                
                <div>
                    <span class="text-gray-600">Địa điểm:</span>
                    <span class="ml-2"><?= htmlspecialchars($activity['DiaDiem']) ?></span>
                </div>
            </div>
            
            <form method="POST">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Mã SV
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Họ tên
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Thời gian đăng ký
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($registrations as $reg): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <input type="checkbox" name="attendance[]" value="<?= $reg['NguoiDungId'] ?>"
                                           <?= $reg['TrangThai'] == 1 ? 'checked' : '' ?>
                                           class="attendance-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= htmlspecialchars($reg['MaSinhVien']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($reg['HoTen']) ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($reg['ThoiGianDangKy'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="flex justify-end space-x-4 mt-6">
                    <a href="?module=activities&action=view&id=<?= $id ?>"
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Hủy
                    </a>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Lưu điểm danh
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('select-all').addEventListener('change', function() {
    document.querySelectorAll('.attendance-checkbox').forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script> 