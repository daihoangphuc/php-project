<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Tìm kiếm và lọc
$search = isset($_GET['search']) ? $_GET['search'] : '';
$type = isset($_GET['type']) ? (int)$_GET['type'] : -1;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';

$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "MoTa LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if ($type >= 0) {
    $where[] = "LoaiGiaoDich = ?";
    $params[] = $type;
    $types .= 'i';
}

if ($from_date) {
    $where[] = "NgayGiaoDich >= ?";
    $params[] = $from_date;
    $types .= 's';
}

if ($to_date) {
    $where[] = "NgayGiaoDich <= ?";
    $params[] = $to_date . ' 23:59:59';
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Lấy danh sách giao dịch
$sql = "SELECT t.*, n.HoTen as NguoiTao
        FROM taichinh t
        LEFT JOIN nguoidung n ON t.NguoiDungId = n.Id
        $whereClause
        ORDER BY t.NgayGiaoDich DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$limit, $offset]));
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tính tổng thu/chi
$totalIncome = 0;
$totalExpense = 0;
foreach ($transactions as $trans) {
    if ($trans['LoaiGiaoDich'] == 1) {
        $totalIncome += $trans['SoTien'];
    } else {
        $totalExpense += $trans['SoTien'];
    }
}

// Lấy tổng số giao dịch
$total = $conn->query("SELECT COUNT(*) as count FROM taichinh $whereClause")->fetch_assoc()['count'];
$totalPages = ceil($total / $limit);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Quản lý tài chính</h1>
        <?php if (isAdmin()): ?>
            <button type="button" data-modal-target="addTransactionModal" 
                    data-modal-toggle="addTransactionModal" 
                    class="btn-primary">
                Thêm giao dịch mới
            </button>
        <?php endif; ?>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Tổng thu</h3>
            <p class="text-2xl font-bold text-green-600">
                <?= number_format($totalIncome, 0, ',', '.') ?> đ
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Tổng chi</h3>
            <p class="text-2xl font-bold text-red-600">
                <?= number_format($totalExpense, 0, ',', '.') ?> đ
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-700">Số dư</h3>
            <p class="text-2xl font-bold <?= ($totalIncome - $totalExpense) >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                <?= number_format($totalIncome - $totalExpense, 0, ',', '.') ?> đ
            </p>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <canvas id="financeChart"></canvas>
    </div>

    <!-- Form tìm kiếm và lọc -->
    <form class="mb-6 bg-white rounded-lg shadow p-6">
        <input type="hidden" name="module" value="finances">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       class="form-input w-full" placeholder="Tìm theo mô tả...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Loại giao dịch</label>
                <select name="type" class="form-select w-full">
                    <option value="-1">Tất cả</option>
                    <option value="1" <?= $type === 1 ? 'selected' : '' ?>>Thu</option>
                    <option value="0" <?= $type === 0 ? 'selected' : '' ?>>Chi</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Từ ngày</label>
                <input type="date" name="from_date" value="<?= $from_date ?>" class="form-input w-full">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Đến ngày</label>
                <input type="date" name="to_date" value="<?= $to_date ?>" class="form-input w-full">
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button type="submit" class="btn-secondary">Tìm kiếm</button>
        </div>
    </form>

    <!-- Danh sách giao dịch -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ngày giao dịch
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Loại
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Số tiền
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Mô tả
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Người tạo
                    </th>
                    <?php if (isAdmin()): ?>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Thao tác
                        </th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($transactions as $trans): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= date('d/m/Y H:i', strtotime($trans['NgayGiaoDich'])) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                       <?= $trans['LoaiGiaoDich'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $trans['LoaiGiaoDich'] ? 'Thu' : 'Chi' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="<?= $trans['LoaiGiaoDich'] ? 'text-green-600' : 'text-red-600' ?>">
                                <?= number_format($trans['SoTien'], 0, ',', '.') ?> đ
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <?= htmlspecialchars($trans['MoTa']) ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= htmlspecialchars($trans['NguoiTao']) ?>
                        </td>
                        <?php if (isAdmin()): ?>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <button type="button" 
                                        onclick="editTransaction(<?= $trans['Id'] ?>)"
                                        class="text-indigo-600 hover:text-indigo-900">Sửa</button>
                                <button type="button"
                                        onclick="deleteTransaction(<?= $trans['Id'] ?>)"
                                        class="ml-3 text-red-600 hover:text-red-900">Xóa</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <?php include 'includes/pagination.php'; ?>
</div>

<!-- Thêm Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Khởi tạo biểu đồ
const ctx = document.getElementById('financeChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($t) { 
            return date('d/m/Y', strtotime($t['NgayGiaoDich'])); 
        }, array_reverse($transactions))) ?>,
        datasets: [{
            label: 'Thu',
            borderColor: '#059669',
            data: <?= json_encode(array_map(function($t) { 
                return $t['LoaiGiaoDich'] ? $t['SoTien'] : 0; 
            }, array_reverse($transactions))) ?>
        }, {
            label: 'Chi',
            borderColor: '#DC2626',
            data: <?= json_encode(array_map(function($t) { 
                return !$t['LoaiGiaoDich'] ? $t['SoTien'] : 0; 
            }, array_reverse($transactions))) ?>
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script> 