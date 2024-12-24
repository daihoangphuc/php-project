<?php
require_once 'modules/auth/auth.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$auth = new Auth($conn);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        $result = $auth->changePassword($_SESSION['user']['id'], $current_password, $new_password);
        if ($result['success']) {
            $success = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="p-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Đổi mật khẩu</h2>
                <a href="index.php?module=auth&action=profile" class="text-blue-500 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Quay lại thông tin cá nhân
                </a>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-800 rounded-lg p-4 mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 text-green-800 rounded-lg p-4 mb-6">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">
                        Mật khẩu hiện tại <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="current_password" name="current_password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700">
                        Mật khẩu mới <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="new_password" name="new_password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                        Xác nhận mật khẩu mới <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i>
                        Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 