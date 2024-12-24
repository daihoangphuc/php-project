<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Lấy danh sách nhiệm vụ
$sql = "SELECT nv.*, nd.HoTen as NguoiPhuTrach
        FROM nhiemvu nv
        LEFT JOIN phancongnhiemvu pc ON nv.Id = pc.NhiemVuId
        LEFT JOIN nguoidung nd ON pc.NguoiDungId = nd.Id";

// Xử lý tìm kiếm và lọc
if (isset($_GET['search'])) {
    $search = validateInput($_GET['search']);
    $sql .= " WHERE nv.TenNhiemVu LIKE '%$search%' OR nv.MoTa LIKE '%$search%'";
}

if (isset($_GET['status'])) {
    $status = (int)$_GET['status'];
    $sql .= isset($_GET['search']) ? " AND" : " WHERE";
    $sql .= " nv.TrangThai = $status";
}

$sql .= " GROUP BY nv.Id ORDER BY nv.NgayTao DESC LIMIT $limit OFFSET $offset";
$tasks = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Đếm tổng số nhiệm vụ
$totalTasks = $conn->query("SELECT COUNT(*) as total FROM nhiemvu")->fetch_assoc()['total'];
$totalPages = ceil($totalTasks / $limit);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Quản lý nhiệm vụ</h1>
        <?php if (isAdmin()): ?>
            <button data-modal-target="addTaskModal" data-modal-toggle="addTaskModal"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Thêm nhiệm vụ
            </button>
        <?php endif; ?>
    </div>

    <!-- Form tìm kiếm -->
    <div class="mb-6">
        <form method="GET" action="" class="flex gap-4">
            <input type="hidden" name="module" value="tasks">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Tìm kiếm nhiệm vụ..."
                       class="w-full px-4 py-2 border rounded-lg">
            </div>
            <select name="status" class="px-4 py-2 border rounded-lg">
                <option value="">Tất cả trạng thái</option>
                <option value="0">Chưa bắt đầu</option>
                <option value="1">Đang thực hiện</option>
                <option value="2">Đã hoàn thành</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Tìm kiếm
            </button>
        </form>
    </div>

    <!-- Danh sách nhiệm vụ -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tên nhiệm vụ</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Người phụ trách</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Thời gian</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trạng thái</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($tasks as $task): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                <?= htmlspecialchars($task['TenNhiemVu']) ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?= htmlspecialchars($task['MoTa']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= htmlspecialchars($task['NguoiPhuTrach'] ?? 'Chưa phân công') ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= date('d/m/Y', strtotime($task['NgayBatDau'])) ?> - 
                            <?= date('d/m/Y', strtotime($task['NgayKetThuc'])) ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php
                                switch($task['TrangThai']) {
                                    case 0:
                                        echo 'bg-gray-100 text-gray-800';
                                        break;
                                    case 1:
                                        echo 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 2:
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                }
                                ?>">
                                <?php
                                switch($task['TrangThai']) {
                                    case 0:
                                        echo 'Chưa bắt đầu';
                                        break;
                                    case 1:
                                        echo 'Đang thực hiện';
                                        break;
                                    case 2:
                                        echo 'Đã hoàn thành';
                                        break;
                                }
                                ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <a href="?module=tasks&action=view&id=<?= $task['Id'] ?>" 
                               class="text-blue-600 hover:text-blue-900 mr-3">Chi tiết</a>
                            <?php if (isAdmin()): ?>
                                <a href="?module=tasks&action=edit&id=<?= $task['Id'] ?>" 
                                   class="text-indigo-600 hover:text-indigo-900 mr-3">Sửa</a>
                                <button onclick="deleteTask(<?= $task['Id'] ?>)" 
                                        class="text-red-600 hover:text-red-900">Xóa</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?module=tasks&page=<?= $i ?><?= isset($_GET['search']) ? '&search=' . $_GET['search'] : '' ?><?= isset($_GET['status']) ? '&status=' . $_GET['status'] : '' ?>" 
                       class="<?= $i === $page ? 'bg-blue-50 text-blue-600' : 'bg-white text-gray-500' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium hover:bg-gray-50">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal thêm nhiệm vụ -->
<div id="addTaskModal" tabindex="-1" aria-hidden="true" 
     class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <!-- Modal content -->
</div>

<script>
function deleteTask(id) {
    if (confirm('Bạn có chắc chắn muốn xóa nhiệm vụ này?')) {
        fetch(`?module=tasks&action=delete&id=${id}`, {
            method: 'POST'
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Có lỗi xảy ra khi xóa nhiệm vụ');
              }
          });
    }
}
</script> 