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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'type' => (int)$_POST['type'],
        'amount' => (int)str_replace([',', '.'], '', $_POST['amount']), // Chuyển định dạng số tiền về số nguyên
        'description' => validateInput($_POST['description']),
        'date' => $_POST['date'],
        'user_id' => $_SESSION['user']['id']
    ];
    
    $result = $finance->create($data);
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
            <h2 class="text-xl font-semibold">Thêm giao dịch mới</h2>
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
                        <option value="1">Thu</option>
                        <option value="0">Chi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Số tiền <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="amount" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                           placeholder="Nhập số tiền">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Ngày giao dịch <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="date" required
                           value="<?php echo date('Y-m-d\TH:i'); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Mô tả
                    </label>
                    <textarea name="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Nhập mô tả giao dịch"></textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Thêm mới
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