<?php
session_start();

require_once 'modules/auth/auth.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$auth = new Auth($conn);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = validateInput($_POST['username']);
    $password = $_POST['password'];
    
    $result = $auth->login($username, $password);
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Đăng nhập
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Hoặc
                <a href="index.php?module=auth&action=register" class="font-medium text-blue-600 hover:text-blue-500">
                    đăng ký tài khoản mới
                </a>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z"/>
                    </svg>
                    <span class="font-medium"><?php echo $error; ?></span>
                </div>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Tên đăng nhập</label>
                    <input id="username" name="username" type="text" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Tên đăng nhập">
                </div>
                <div>
                    <label for="password" class="sr-only">Mật khẩu</label>
                    <input id="password" name="password" type="password" required
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
                           placeholder="Mật khẩu">
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                        Ghi nhớ đăng nhập
                    </label>
                </div>

                <div class="text-sm">
                    <a href="index.php?module=auth&action=forgot_password" class="font-medium text-blue-600 hover:text-blue-500">
                        Quên mật khẩu?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt"></i>
                    </span>
                    Đăng nhập
                </button>
            </div>
        </form>

        <div class="text-center">
            <a href="index.php" class="text-sm text-gray-600 hover:text-blue-500">
                <i class="fas fa-arrow-left mr-1"></i>
                Quay lại trang chủ
            </a>
        </div>
    </div>
</div> 