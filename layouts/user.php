<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Trang chủ' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
        <nav class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-xl font-bold">Logo</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user'])): ?>
                        <a href="?module=profile" class="text-gray-700 hover:text-gray-900">
                            <?= htmlspecialchars($_SESSION['user']['HoTen']) ?>
                        </a>
                        <a href="?module=auth&action=logout" class="text-red-600 hover:text-red-700">
                            Đăng xuất
                        </a>
                    <?php else: ?>
                        <a href="?module=auth&action=login" class="text-blue-600 hover:text-blue-700">
                            Đăng nhập
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main content -->
    <main class="container mx-auto px-4 py-8">
        <?php include $content; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Về chúng tôi</h3>
                    <p class="text-gray-400">Thông tin về CLB</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Liên hệ</h3>
                    <p class="text-gray-400">Email: example@gmail.com</p>
                    <p class="text-gray-400">Phone: 0123456789</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Theo dõi</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">Facebook</a>
                        <a href="#" class="text-gray-400 hover:text-white">Instagram</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 