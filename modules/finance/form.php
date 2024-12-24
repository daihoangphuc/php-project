<?php
require_once 'includes/auth.php';
redirectIfNotAdmin();

$isEdit = isset($_GET['id']);
$transaction = null;

if ($isEdit) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM taichinh WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $transaction = $stmt->get_result()->fetch_assoc();
    
    if (!$transaction) {
        header('Location: ?module=finance');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = (int)$_POST['type'];
    $amount = (float)str_replace(',', '', $_POST['amount']);
    $description = validateInput($_POST['description']);
    $date = validateInput($_POST['date']);
    
    if ($amount <= 0) {
        $error = "Số tiền phải lớn hơn 0";
    } else {
        if ($isEdit) {
            $sql = "UPDATE taichinh SET Loai = ?, SoTien = ?, MoTa = ?, NgayThucHien = ? WHERE Id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("idssi", $type, $amount, $description, $date, $id);
        } else {
            $sql = "INSERT INTO taichinh (Loai, SoTien, MoTa, NgayThucHien, NguoiTaoId) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $userId = $_SESSION['user']['id'];
            $stmt->bind_param("idssi", $type, $amount, $description, $date, $userId);
        }
        
        if ($stmt->execute()) {
            $action = $isEdit ? 'Cập nhật' : 'Thêm mới';
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['HoTen'],
                $action . ' giao dịch tài chính',
                'Success',
                "Amount: " . number_format($amount)
            );
            
            header('Location: ?module=finance');
            exit;
        } else {
            $error = "Có lỗi xảy ra, vui lòng thử lại!";
        }
    }
}
?>

<!-- Form HTML -->
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">
                <?= $isEdit ? 'Cập nhật giao dịch' : 'Thêm giao dịch mới' ?>
            </h2>
            <a href="?module=finance" class="text-blue-500 hover:underline">
                Quay lại danh sách
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Loại giao dịch <span class="text-red-500">*</span>
                    </label>
                    <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="1" <?= ($isEdit && $transaction['Loai'] == 1) ? 'selected' : '' ?>>Thu</option>
                        <option value="0" <?= ($isEdit && $transaction['Loai'] == 0) ? 'selected' : '' ?>>Chi</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Số tiền <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="amount" required
                           value="<?= $isEdit ? number_format($transaction['SoTien']) : '' ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                           oninput="this.value = this.value.replace(/[^0-9,]/g, '')">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Mô tả <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" required rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?= $isEdit ? htmlspecialchars($transaction['MoTa']) : '' ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Ngày thực hiện <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date" required
                           value="<?= $isEdit ? $transaction['NgayThucHien'] : date('Y-m-d') ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="?module=finance" 
                   class="px-4 py-2 border rounded-md hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <?= $isEdit ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
            </div>
        </form>
    </div>
</div> 