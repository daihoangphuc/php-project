<?php
require_once 'modules/users/users.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$users = new Users($conn);

// Lấy thông tin thành viên
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = $users->getById($id);

if (!$user) {
    $_SESSION['error'] = 'Thành viên không tồn tại';
    header('Location: index.php?module=users');
    exit;
}

// Lấy vai trò của user
$sql = "SELECT v.TenVaiTro 
        FROM vaitronguoidung vn
        JOIN vaitro v ON vn.VaiTroId = v.Id
        WHERE vn.NguoiDungId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row['TenVaiTro'];
}

// Lấy danh sách hoạt động đã tham gia
$sql = "SELECT h.*, d.TrangThai as DangKyTrangThai
        FROM hoatdong h
        JOIN danhsachdangky d ON h.Id = d.HoatDongId
        WHERE d.NguoiDungId = ?
        ORDER BY h.NgayBatDau DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách nhiệm vụ được phân công
$sql = "SELECT n.*, COUNT(d.Id) as TongThanhVien
        FROM nhiemvu n
        JOIN phancongnhiemvu p ON n.Id = p.NhiemVuId
        LEFT JOIN phancongnhiemvu d ON n.Id = d.NhiemVuId
        WHERE p.NguoiDungId = ?
        GROUP BY n.Id
        ORDER BY n.NgayBatDau DESC
        LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$tasks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="p-4">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Thông tin thành viên</h2>
                <div class="flex space-x-3">
                    <a href="index.php?module=users" class="text-blue-500 hover:underline">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Quay lại danh sách
                    </a>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <a href="index.php?module=users&action=edit&id=<?php echo $user['Id']; ?>" 
                           class="text-blue-500 hover:underline">
                            <i class="fas fa-edit mr-1"></i>
                            Chỉnh sửa
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h3 class="font-semibold text-lg mb-4">Thông tin cá nhân</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Họ và tên</label>
                            <div class="mt-1"><?php echo htmlspecialchars($user['HoTen']); ?></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Mã sinh viên</label>
                            <div class="mt-1"><?php echo htmlspecialchars($user['MaSinhVien']); ?></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <div class="mt-1"><?php echo htmlspecialchars($user['Email']); ?></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Giới tính</label>
                            <div class="mt-1"><?php echo $user['GioiTinh'] ? 'Nam' : 'Nữ'; ?></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Ngày sinh</label>
                            <div class="mt-1"><?php echo date('d/m/Y', strtotime($user['NgaySinh'])); ?></div>
                        </div>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-lg mb-4">Thông tin học tập</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Lớp</label>
                            <div class="mt-1"><?php echo htmlspecialchars($user['TenLop']); ?></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Khoa</label>
                            <div class="mt-1"><?php echo htmlspecialchars($user['TenKhoaTruong']); ?></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Chức vụ</label>
                            <div class="mt-1"><?php echo htmlspecialchars($user['TenChucVu']); ?></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Vai trò</label>
                            <div class="mt-1">
                                <?php if (!empty($roles)): ?>
                                    <?php echo implode(', ', array_map('htmlspecialchars', $roles)); ?>
                                <?php else: ?>
                                    <span class="text-gray-400">Chưa có vai trò</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Trạng thái</label>
                            <div class="mt-1">
                                <?php if ($user['TrangThai']): ?>
                                    <span class="text-green-600">Đang hoạt động</span>
                                <?php else: ?>
                                    <span class="text-red-600">Đã khóa</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (!empty($activities)): ?>
                <div class="mb-8">
                    <h3 class="font-semibold text-lg mb-4">Hoạt động đã tham gia gần đây</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Tên hoạt động</th>
                                    <th scope="col" class="px-6 py-3">Thời gian</th>
                                    <th scope="col" class="px-6 py-3">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activities as $activity): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <a href="index.php?module=activities&action=view&id=<?php echo $activity['Id']; ?>"
                                               class="text-blue-500 hover:underline">
                                                <?php echo htmlspecialchars($activity['TenHoatDong']); ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php echo date('d/m/Y', strtotime($activity['NgayBatDau'])); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            switch ($activity['DangKyTrangThai']) {
                                                case 0:
                                                    echo '<span class="text-yellow-600">Chờ duyệt</span>';
                                                    break;
                                                case 1:
                                                    echo '<span class="text-green-600">Đã duyệt</span>';
                                                    break;
                                                case 2:
                                                    echo '<span class="text-red-600">Từ chối</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($tasks)): ?>
                <div>
                    <h3 class="font-semibold text-lg mb-4">Nhiệm vụ được phân công gần đây</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Tên nhiệm vụ</th>
                                    <th scope="col" class="px-6 py-3">Thời gian</th>
                                    <th scope="col" class="px-6 py-3">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <a href="index.php?module=tasks&action=view&id=<?php echo $task['Id']; ?>"
                                               class="text-blue-500 hover:underline">
                                                <?php echo htmlspecialchars($task['TenNhiemVu']); ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php echo date('d/m/Y', strtotime($task['NgayBatDau'])); ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php
                                            switch ($task['TrangThai']) {
                                                case 0:
                                                    echo '<span class="text-yellow-600">Chưa bắt đầu</span>';
                                                    break;
                                                case 1:
                                                    echo '<span class="text-blue-600">Đang thực hiện</span>';
                                                    break;
                                                case 2:
                                                    echo '<span class="text-green-600">Đã hoàn thành</span>';
                                                    break;
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 