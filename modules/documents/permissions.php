<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!is_admin()) {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    die("ID tài liệu không hợp lệ!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $permissions = $_POST['permissions'] ?? [];
    
    // Xóa phân quyền cũ
    $stmt = $conn->prepare("DELETE FROM phanquyentailieu WHERE TaiLieuId = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Thêm phân quyền mới
    if (!empty($permissions)) {
        $stmt = $conn->prepare("INSERT INTO phanquyentailieu (TaiLieuId, VaiTroId, Quyen) VALUES (?, ?, ?)");
        foreach ($permissions as $vaiTroId => $quyen) {
            $stmt->bind_param("iii", $id, $vaiTroId, $quyen);
            $stmt->execute();
        }
    }
    
    header('Location: ?module=documents');
    exit;
}

// Lấy thông tin tài liệu
$stmt = $conn->prepare("SELECT * FROM tailieu WHERE Id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$document = $stmt->get_result()->fetch_assoc();

// Lấy danh sách vai trò
$roles = $conn->query("SELECT * FROM vaitro")->fetch_all(MYSQLI_ASSOC);

// Lấy phân quyền hiện tại
$stmt = $conn->prepare("SELECT * FROM phanquyentailieu WHERE TaiLieuId = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$currentPermissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$currentPermissions = array_column($currentPermissions, 'Quyen', 'VaiTroId');
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Phân quyền tài liệu</h1>
        <h2 class="text-xl mb-4"><?= htmlspecialchars($document['TenTaiLieu']) ?></h2>
        
        <form method="POST" class="space-y-6">
            <?php foreach ($roles as $role): ?>
                <div class="flex items-center justify-between p-4 bg-white rounded-lg shadow">
                    <span class="font-medium"><?= htmlspecialchars($role['TenVaiTro']) ?></span>
                    <select name="permissions[<?= $role['Id'] ?>]"
                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="0" <?= ($currentPermissions[$role['Id']] ?? 0) == 0 ? 'selected' : '' ?>>Không có quyền</option>
                        <option value="1" <?= ($currentPermissions[$role['Id']] ?? 0) == 1 ? 'selected' : '' ?>>Xem</option>
                        <option value="2" <?= ($currentPermissions[$role['Id']] ?? 0) == 2 ? 'selected' : '' ?>>Tải xuống</option>
                    </select>
                </div>
            <?php endforeach; ?>
            
            <div class="flex justify-end space-x-4">
                <a href="?module=documents"
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div> 