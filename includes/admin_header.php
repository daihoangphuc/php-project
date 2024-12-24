<header class="bg-white shadow-md fixed top-0 right-0 left-64 h-16 z-10">
    <div class="flex justify-between items-center h-full px-8">
        <h1 class="text-xl font-semibold"><?= $pageTitle ?? 'Admin Panel' ?></h1>
        <div class="flex items-center space-x-4">
            <span class="text-gray-600">
                <?= htmlspecialchars($_SESSION['user']['HoTen']) ?>
            </span>
            <a href="?module=auth&action=logout" class="text-red-600 hover:text-red-700">
                Đăng xuất
            </a>
        </div>
    </div>
</header> 