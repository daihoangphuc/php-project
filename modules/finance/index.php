<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Lọc theo loại và thời gian
$conditions = [];
$params = [];
$types = "";

if (isset($_GET['type']) && $_GET['type'] !== '') {
    $type = (int)$_GET['type'];
    $conditions[] = "tc.LoaiGiaoDich = ?";
    $params[] = $type;
    $types .= "i";
}

if (isset($_GET['start_date'])) {
    $startDate = validateInput($_GET['start_date']);
    $conditions[] = "tc.NgayGiaoDich >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if (isset($_GET['end_date'])) {
    $endDate = validateInput($_GET['end_date']);
    $conditions[] = "tc.NgayGiaoDich <= ?";
    $params[] = $endDate;
    $types .= "s";
}

// Lấy danh sách giao dịch tài chính
$sql = "SELECT tc.*, nd.HoTen as NguoiTao 
        FROM taichinh tc
        LEFT JOIN nguoidung nd ON tc.NguoiDungId = nd.Id";

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY tc.NgayGiaoDich DESC LIMIT ? OFFSET ?";
$types .= "ii";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tính tổng thu/chi
$totalIncome = 0;
$totalExpense = 0;
foreach ($transactions as $trans) {
    if ($trans['LoaiGiaoDich'] == 1) { // Thu
        $totalIncome += $trans['SoTien'];
    } else { // Chi
        $totalExpense += $trans['SoTien'];
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Quản lý tài chính</h1>
        <?php if (isAdmin()): ?>
            <button data-modal-target="addFinanceModal" data-modal-toggle="addFinanceModal"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Thêm giao dịch
            </button>
        <?php endif; ?>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-green-100 p-6 rounded-lg border border-green-300">
            <i class="fas fa-coins text-green-600"></i>
            <h3 class="text-green-800 font-semibold mb-2">Tổng thu</h3>
            <p class="text-2xl text-green-600"><?= number_format($totalIncome) ?> VNĐ</p>
        </div>
        <div class="bg-red-100 p-6 rounded-lg border border-red-300">
            <i class="fas fa-coins text-red-600"></i>
            <h3 class="text-red-800 font-semibold mb-2">Tổng chi</h3>
            <p class="text-2xl text-red-600"><?= number_format($totalExpense) ?> VNĐ</p>
        </div>
        <div class="bg-blue-100 p-6 rounded-lg border border-blue-300 ">
            <i class="fas fa-coins text-blue-600"></i>
            <h3 class="text-blue-800 font-semibold mb-2">Số dư</h3>
            <p class="text-2xl text-blue-600"><?= number_format($totalIncome - $totalExpense) ?> VNĐ</p>
        </div>
    </div>

    <!-- Form lọc -->
    <div class="mb-6">
        <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="module" value="finance">
            <select name="type" class="px-4 py-2 border rounded-lg">
                <option value="">Tất cả loại</option>
                <option value="1">Thu</option>
                <option value="0">Chi</option>
            </select>
            <input type="date" name="start_date" class="px-4 py-2 border rounded-lg" placeholder="Từ ngày">
            <input type="date" name="end_date" class="px-4 py-2 border rounded-lg" placeholder="Đến ngày">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                Lọc
            </button>
        </form>
    </div>

    <!-- Biểu đồ -->
    <div class="mb-6">
        <canvas id="financeChart"></canvas>
    </div>

    <!-- Danh sách giao dịch -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mô tả</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Loại</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Số tiền</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Người tạo</th>
                    <?php if (isAdmin()): ?>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($transactions as $trans): ?>
                    <tr>
                        <td class="px-6 py-4"><?= htmlspecialchars($trans['MoTa']) ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                       <?= $trans['LoaiGiaoDich'] == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $trans['LoaiGiaoDich'] == 1 ? 'Thu' : 'Chi' ?>
                            </span>
                        </td>
                        <td class="px-6 py-4"><?= number_format($trans['SoTien']) ?> VNĐ</td>
                        <td class="px-6 py-4"><?= date('d/m/Y', strtotime($trans['NgayGiaoDich'])) ?></td>
                        <td class="px-6 py-4"><?= htmlspecialchars($trans['NguoiTao']) ?></td>
                        <?php if (isAdmin()): ?>
                            <td class="px-6 py-4 text-right">
                                <button onclick="editFinance(<?= $trans['Id'] ?>)" 
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">Sửa</button>
                                <button onclick="deleteFinance(<?= $trans['Id'] ?>)" 
                                        class="text-red-600 hover:text-red-900">Xóa</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal thêm/sửa giao dịch -->
<div id="addFinanceModal" tabindex="-1" aria-hidden="true" 
     class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <!-- Modal content -->
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Khởi tạo biểu đồ
const ctx = document.getElementById('financeChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(function($trans) { 
            return date('d/m/Y', strtotime($trans['NgayGiaoDich'])); 
        }, $transactions)) ?>,
        datasets: [{
            label: 'Thu',
            data: <?= json_encode(array_map(function($trans) { 
                return $trans['LoaiGiaoDich'] == 1 ? $trans['SoTien'] : 0; 
            }, $transactions)) ?>,
            backgroundColor: 'rgba(34, 197, 94, 0.5)',
            borderColor: 'rgb(34, 197, 94)',
            borderWidth: 1
        }, {
            label: 'Chi',
            data: <?= json_encode(array_map(function($trans) { 
                return $trans['LoaiGiaoDich'] == 0 ? $trans['SoTien'] : 0; 
            }, $transactions)) ?>,
            backgroundColor: 'rgba(239, 68, 68, 0.5)',
            borderColor: 'rgb(239, 68, 68)',
            borderWidth: 1
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

function deleteFinance(id) {
    if (confirm('Bạn có chắc chắn muốn xóa giao dịch này?')) {
        fetch(`?module=finance&action=delete&id=${id}`, {
            method: 'POST'
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Có lỗi xảy ra khi xóa giao dịch');
              }
          });
    }
}
</script> 