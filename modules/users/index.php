<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = "WHERE HoTen LIKE ? OR Email LIKE ? OR MaSinhVien LIKE ?";
}

// Lấy danh sách người dùng
$sql = "SELECT n.*, l.TenLop, c.TenChucVu, GROUP_CONCAT(v.TenVaiTro) as VaiTro
        FROM nguoidung n
        LEFT JOIN lophoc l ON n.LopHocId = l.Id
        LEFT JOIN chucvu c ON n.ChucVuId = c.Id
        LEFT JOIN vaitronguoidung vn ON n.Id = vn.NguoiDungId
        LEFT JOIN vaitro v ON vn.VaiTroId = v.Id
        $where
        GROUP BY n.Id
        ORDER BY n.NgayTao DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($search) {
    $searchParam = "%$search%";
    $stmt->bind_param("ssssii", $searchParam, $searchParam, $searchParam, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy tổng số người dùng
$total = $conn->query("SELECT COUNT(*) as count FROM nguoidung")->fetch_assoc()['count'];
$totalPages = ceil($total / $limit);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Quản lý người dùng</h1>
        <?php if (isAdmin()): ?>
            <a href="?module=users&action=form" class="btn-primary">
                Thêm người dùng mới
            </a>
        <?php endif; ?>
    </div>

    <!-- Tìm kiếm -->
    <form class="mb-6">
        <input type="hidden" name="module" value="users">
        <div class="flex gap-4">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   class="form-input flex-1" placeholder="Tìm kiếm theo tên, email, mã sinh viên...">
            <button type="submit" class="btn-secondary">Tìm kiếm</button>
        </div>
    </form>

    <!-- Danh sách người dùng -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Mã SV
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Họ tên
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Lớp
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Chức vụ
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Vai trò
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Thao tác
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($user['MaSinhVien']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($user['HoTen']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($user['Email']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($user['TenLop']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($user['TenChucVu']) ?>
                        </td>
                        <td class="px-6 py-4">
                            <?= htmlspecialchars($user['VaiTro']) ?>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <a href="?module=users&action=form&id=<?= $user['Id'] ?>" 
                               class="text-indigo-600 hover:text-indigo-900">Sửa</a>
                            <?php if (isAdmin()): ?>
                                <a href="?module=users&action=delete&id=<?= $user['Id'] ?>" 
                                   class="ml-3 text-red-600 hover:text-red-900"
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">Xóa</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <?php include 'includes/pagination.php'; ?>
</div> 