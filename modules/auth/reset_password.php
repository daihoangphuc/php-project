<?php
require_once 'modules/auth/auth.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$auth = new Auth($conn);
$error = '';
$success = '';

// Kiểm tra token
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token)) {
    header('Location: index.php?module=auth&action=forgot_password');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        $result = $auth->resetPassword($token, $password);
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?module=auth&action=login');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Đặt lại mật khẩu
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Nhập mật khẩu mới của bạn
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-800 rounded-lg p-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Mật khẩu mới <span class="text-red-500">*</span>
                    </label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="Nhập mật khẩu mới">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                        Xác nhận mật khẩu <span class="text-red-500">*</span>
                    </label>
                    <input id="confirm_password" name="confirm_password" type="password" required
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           placeholder="Nhập lại mật khẩu mới">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-key"></i>
                    </span>
                    Đặt lại mật khẩu
                </button>
            </div>
        </form>

        <div class="text-center space-y-2">
            <div>
                <a href="index.php?module=auth&action=login" class="text-sm text-gray-600 hover:text-blue-500">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Quay lại đăng nhập
                </a>
            </div>
            <div>
                <a href="index.php" class="text-sm text-gray-600 hover:text-blue-500">
                    <i class="fas fa-home mr-1"></i>
                    Trang chủ
                </a>
            </div>
        </div>
    </div>
</div> 