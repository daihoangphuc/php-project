<?php
// Kiểm tra quyền admin
redirectIfNotAdmin();

// Code xử lý
$users = $conn->query("
    SELECT u.*, c.TenChucVu, l.TenLop 
    FROM nguoidung u
    LEFT JOIN chucvu c ON u.ChucVuId = c.Id
    LEFT JOIN lophoc l ON u.LopHocId = l.Id
    ORDER BY u.Id DESC
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Quản lý người dùng';
?>

<!-- HTML code -->
<div class="container mx-auto px-4">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Quản lý người dùng</h1>
            <a href="?module=users&action=add" class="bg-blue-500 text-white px-4 py-2 rounded-lg">
                Thêm mới
            </a>
        </div>
        
        <!-- Bảng danh sách -->
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <!-- Table content -->
            </table>
        </div>
    </div>
</div> 