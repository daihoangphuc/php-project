<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if ($search) {
    $where = "WHERE TenChucVu LIKE ?";
}

// Lấy danh sách chức vụ
$sql = "SELECT c.*, COUNT(n.Id) as SoThanhVien
        FROM chucvu c
        LEFT JOIN nguoidung n ON c.Id = n.ChucVuId
        $where
        GROUP BY c.Id
        ORDER BY c.NgayTao DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if ($search) {
    $searchParam = "%$search%";
    $stmt->bind_param("sii", $searchParam, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$positions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy tổng số chức vụ
$total = $conn->query("SELECT COUNT(*) as count FROM chucvu")->fetch_assoc()['count'];
$totalPages = ceil($total / $limit);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Quản lý chức vụ</h1>
        <button type="button" data-modal-target="addPositionModal" data-modal-toggle="addPositionModal" 
                class="btn-primary">
            Thêm chức vụ mới
        </button>
    </div>

    <!-- Tìm kiếm -->
    <form class="mb-6">
        <input type="hidden" name="module" value="positions">
        <div class="flex gap-4">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                   class="form-input flex-1" placeholder="Tìm kiếm chức vụ...">
            <button type="submit" class="btn-secondary">Tìm kiếm</button>
        </div>
    </form>

    <!-- Danh sách chức vụ -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tên chức vụ
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Số thành viên
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ngày tạo
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Thao tác
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($positions as $position): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($position['TenChucVu']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= $position['SoThanhVien'] ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= formatDateTime($position['NgayTao']) ?>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <button type="button" 
                                    data-modal-target="editPositionModal" 
                                    data-modal-toggle="editPositionModal"
                                    data-position-id="<?= $position['Id'] ?>"
                                    data-position-name="<?= htmlspecialchars($position['TenChucVu']) ?>"
                                    class="text-indigo-600 hover:text-indigo-900">Sửa</button>
                            <button type="button"
                                    onclick="deletePosition(<?= $position['Id'] ?>)"
                                    class="ml-3 text-red-600 hover:text-red-900">Xóa</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <?php include 'includes/pagination.php'; ?>
</div>

<!-- Modal thêm chức vụ -->
<div id="addPositionModal" tabindex="-1" aria-hidden="true" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Thêm chức vụ mới</h3>
                <button type="button" class="modal-close" data-modal-hide="addPositionModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            <form id="addPositionForm" onsubmit="return addPosition(event)">
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Tên chức vụ</label>
                        <input type="text" name="TenChucVu" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" data-modal-hide="addPositionModal">Hủy</button>
                    <button type="submit" class="btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal sửa chức vụ -->
<div id="editPositionModal" tabindex="-1" aria-hidden="true" class="modal">
    <!-- Tương tự như modal thêm -->
</div>

<script>
// Xử lý thêm chức vụ
async function addPosition(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('api/positions/create.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Thêm chức vụ thành công');
            location.reload();
        } else {
            showToast('error', data.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        showToast('error', 'Có lỗi xảy ra');
    }
}

// Xử lý xóa chức vụ
async function deletePosition(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa chức vụ này?')) return;

    try {
        const response = await fetch(`api/positions/delete.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Xóa chức vụ thành công');
            location.reload();
        } else {
            showToast('error', data.message || 'Có lỗi xảy ra');
        }
    } catch (error) {
        showToast('error', 'Có lỗi xảy ra');
    }
}
</script> 