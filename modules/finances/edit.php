<?php
require_once 'modules/finances/finances.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$finances = new Finances($conn);
$error = '';

// Lấy thông tin giao dịch
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$transaction = $finances->getById($id);

if (!$transaction) {
    $_SESSION['error'] = 'Giao dịch không tồn tại';
    header('Location: index.php?module=finances');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'type' => (int)$_POST['type'],
        'amount' => (int)str_replace([',', '.'], '', $_POST['amount']),
        'description' => validateInput($_POST['description']),
        'date' => $_POST['date'],
        'note' => validateInput($_POST['note'])
    ];
    
    $result = $finances->update($id, $data);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: index.php?module=finances');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Sửa giao dịch</h2>
            <a href="index.php?module=finances" class="text-blue-500 hover:underline">
                <i class="fas fa-arrow-left mr-2"></i>
                Quay lại danh sách
            </a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-800 rounded-lg p-4 mb-6">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Loại giao dịch <span class="text-red-500">*</span>
                    </label>
                    <select name="type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="1" <?php echo $transaction['LoaiGiaoDich'] == 1 ? 'selected' : ''; ?>>Thu</option>
                        <option value="0" <?php echo $transaction['LoaiGiaoDich'] == 0 ? 'selected' : ''; ?>>Chi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Số tiền <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="amount" required
                           value="<?php echo number_format($transaction['SoTien'], 0, ',', '.'); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Nhập số tiền"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Ngày giao dịch <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" required
                           value="<?php echo date('Y-m-d', strtotime($transaction['NgayGiaoDich'])); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Mô tả <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="description" required
                           value="<?php echo htmlspecialchars($transaction['MoTa']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="Nhập mô tả giao dịch">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Ghi chú
                    </label>
                    <textarea name="note" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Nhập ghi chú (nếu có)"><?php echo htmlspecialchars($transaction['GhiChu']); ?></textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div> 