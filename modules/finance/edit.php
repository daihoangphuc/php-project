<?php
require_once 'modules/finance/finance.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$finance = new Finance($conn);
$error = '';
$success = '';

// Lấy thông tin giao dịch
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$transaction = $finance->getById($id);

if (!$transaction) {
    $_SESSION['error'] = 'Giao dịch không tồn tại';
    header('Location: index.php?module=finance');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'type' => (int)$_POST['type'],
        'amount' => (int)str_replace([',', '.'], '', $_POST['amount']),
        'description' => validateInput($_POST['description']),
        'date' => $_POST['date'],
        'user_id' => $transaction['NguoiDungId'] // Giữ nguyên người tạo giao dịch
    ];
    
    $result = $finance->update($id, $data);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: index.php?module=finance');
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
            <a href="index.php?module=finance" class="text-blue-500 hover:underline">
                Quay lại danh sách
            </a>
        </div>

        <?php if ($error): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">
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
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                           placeholder="Nhập số tiền">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Ngày giao dịch <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="date" required
                           value="<?php echo date('Y-m-d\TH:i', strtotime($transaction['NgayGiaoDich'])); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Người thực hiện
                    </label>
                    <div class="mt-2 text-gray-600">
                        <?php echo htmlspecialchars($transaction['HoTen']); ?>
                        <div class="text-sm text-gray-500">
                            <?php echo htmlspecialchars($transaction['MaSinhVien']); ?>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Mô tả
                    </label>
                    <textarea name="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Nhập mô tả giao dịch"><?php echo htmlspecialchars($transaction['MoTa']); ?></textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Tự động định dạng số tiền khi nhập
document.querySelector('input[name="amount"]').addEventListener('input', function(e) {
    // Xóa tất cả ký tự không phải số
    let value = this.value.replace(/[^0-9]/g, '');
    
    // Định dạng số với dấu chấm phân cách hàng nghìn
    this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
});
</script> 