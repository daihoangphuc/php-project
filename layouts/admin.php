<?php
if (!isAdmin()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Panel' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 w-64 h-full bg-gray-800">
        <div class="flex items-center justify-center h-16 bg-gray-900">
            <span class="text-white text-xl font-semibold">Admin Panel</span>
        </div>
        <nav class="mt-4">
            <a href="?module=dashboard" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <span>Dashboard</span>
            </a>
            <a href="?module=users" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <span>Quản lý người dùng</span>
            </a>
            <a href="?module=activities" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <span>Quản lý hoạt động</span>
            </a>
            <a href="?module=tasks" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <span>Quản lý nhiệm vụ</span>
            </a>
            <a href="?module=finances" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <span>Quản lý tài chính</span>
            </a>
            <a href="?module=documents" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <span>Quản lý tài liệu</span>
            </a>
            <a href="?module=news" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <span>Quản lý tin tức</span>
            </a>
            <a href="?module=statistics" class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-700">
                <span>Thống kê báo cáo</span>
            </a>
        </nav>
    </aside>

    <!-- Main content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <?php include 'includes/admin_header.php'; ?>
        
        <!-- Content -->
        <main class="mt-16">
            <?php 
            if(isset($content)) {
                echo $content;
            } else {
                echo '<p class="text-center text-lg font-semibold">Trang bạn truy cập không tồn tại.</p>';
            }
            ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 