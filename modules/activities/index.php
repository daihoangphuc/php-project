<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Tìm kiếm và lọc
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? (int)$_GET['status'] : -1;
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(TenHoatDong LIKE ? OR MoTa LIKE ? OR DiaDiem LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'sss';
}

if ($status >= 0) {
    $where[] = "TrangThai = ?";
    $params[] = $status;
    $types .= 'i';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Lấy danh sách hoạt động
$sql = "SELECT h.*, 
        (SELECT COUNT(*) FROM danhsachdangky d WHERE d.HoatDongId = h.Id AND d.TrangThai = 1) as SoNguoiDangKy,
        (SELECT COUNT(*) FROM danhsachthamgia t WHERE t.HoatDongId = h.Id) as SoNguoiThamGia
        FROM hoatdong h
        $whereClause
        ORDER BY h.NgayBatDau DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$limit, $offset]));
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy tổng số hoạt động
$total = $conn->query("SELECT COUNT(*) as count FROM hoatdong")->fetch_assoc()['count'];
$totalPages = ceil($total / $limit);

// Kiểm tra đăng ký của người dùng hiện tại
$user_id = $_SESSION['user_id'];
$registrations = [];
if (!isAdmin()) {
    $stmt = $conn->prepare("SELECT HoatDongId, TrangThai FROM danhsachdangky WHERE NguoiDungId = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $registrations[$row['HoatDongId']] = $row['TrangThai'];
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Quản lý hoạt động</h1>
        <?php if (isAdmin()): ?>
            <a href="?module=activities&action=form" class="btn-primary">
                Thêm hoạt động mới
            </a>
        <?php endif; ?>
    </div>

    <!-- Tìm kiếm và lọc -->
    <form class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="hidden" name="module" value="activities">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
               class="form-input" placeholder="Tìm kiếm hoạt động...">
        <select name="status" class="form-select">
            <option value="-1">Tất cả trạng thái</option>
            <option value="0" <?= $status === 0 ? 'selected' : '' ?>>Đã hủy</option>
            <option value="1" <?= $status === 1 ? 'selected' : '' ?>>Đang diễn ra</option>
            <option value="2" <?= $status === 2 ? 'selected' : '' ?>>Đã kết thúc</option>
        </select>
        <button type="submit" class="btn-secondary">Tìm kiếm</button>
    </form>

    <!-- Danh sách hoạt động -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tên hoạt động
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Thời gian
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Địa điểm
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Đăng ký/Tham gia
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Trạng thái
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Thao tác
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($activity['TenHoatDong']) ?>
                            </div>
                            <?php if ($activity['MoTa']): ?>
                                <div class="text-sm text-gray-500">
                                    <?= htmlspecialchars(substr($activity['MoTa'], 0, 100)) ?>...
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($activity['NgayBatDau'])) ?><br>
                            đến <?= date('d/m/Y H:i', strtotime($activity['NgayKetThuc'])) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= htmlspecialchars($activity['DiaDiem']) ?>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-500">
                            <?= $activity['SoNguoiDangKy'] ?>/<?= $activity['SoNguoiThamGia'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                switch($activity['TrangThai']) {
                                    case 0: echo 'bg-red-100 text-red-800'; break;
                                    case 1: echo 'bg-green-100 text-green-800'; break;
                                    case 2: echo 'bg-gray-100 text-gray-800'; break;
                                }
                                ?>">
                                <?php
                                switch($activity['TrangThai']) {
                                    case 0: echo 'Đã hủy'; break;
                                    case 1: echo 'Đang diễn ra'; break;
                                    case 2: echo 'Đã kết thúc'; break;
                                }
                                ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <?php if (!isAdmin()): ?>
                                <?php if (!isset($registrations[$activity['Id']])): ?>
                                    <button onclick="register(<?= $activity['Id'] ?>)" 
                                            class="text-indigo-600 hover:text-indigo-900">
                                        Đăng ký
                                    </button>
                                <?php elseif ($registrations[$activity['Id']] == 1): ?>
                                    <button onclick="cancelRegistration(<?= $activity['Id'] ?>)"
                                            class="text-red-600 hover:text-red-900">
                                        Hủy đăng ký
                                    </button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="?module=activities&action=form&id=<?= $activity['Id'] ?>" 
                                   class="text-indigo-600 hover:text-indigo-900">Sửa</a>
                                <button onclick="deleteActivity(<?= $activity['Id'] ?>)"
                                        class="ml-3 text-red-600 hover:text-red-900">Xóa</button>
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

<script>
async function register(activityId) {
    try {
        const response = await fetch('api/activities/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ activity_id: activityId })
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Đăng ký thành công');
            location.reload();
        } else {
            showToast('error', data.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        showToast('error', 'Có lỗi xảy ra');
    }
}

async function cancelRegistration(activityId) {
    if (!confirm('Bạn có chắc chắn muốn hủy đăng ký?')) return;
    
    try {
        const response = await fetch('api/activities/cancel-registration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ activity_id: activityId })
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Hủy đăng ký thành công');
            location.reload();
        } else {
            showToast('error', data.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        showToast('error', 'Có lỗi xảy ra');
    }
}

async function deleteActivity(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa hoạt động này?')) return;

    try {
        const response = await fetch(`api/activities/delete.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Xóa hoạt động thành công');
            location.reload();
        } else {
            showToast('error', data.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        showToast('error', 'Có lỗi xảy ra');
    }
}
</script> 