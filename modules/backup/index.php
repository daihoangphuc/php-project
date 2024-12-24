<?php
require_once 'modules/backup/backup.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$backup = new Backup($conn);

// Xử lý tạo backup mới
if (isset($_POST['create'])) {
    $result = $backup->create();
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header('Location: index.php?module=backup');
    exit;
}

// Xử lý xóa file backup
if (isset($_POST['delete'])) {
    $filename = $_POST['filename'];
    $result = $backup->delete($filename);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header('Location: index.php?module=backup');
    exit;
}

// Xử lý tải file backup
if (isset($_GET['download'])) {
    $filename = $_GET['download'];
    $file = __DIR__ . '/../../backups/' . basename($filename);
    
    if (file_exists($file)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    }
}

// Lấy danh sách file backup
$backups = $backup->getAll();
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Sao lưu dữ liệu</h2>
            <form method="POST" class="inline">
                <button type="submit" name="create"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>
                    Tạo bản sao lưu mới
                </button>
            </form>
        </div>

        <!-- Thông báo -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Danh sách file backup -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tên file
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Kích thước
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ngày tạo
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Thao tác
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($backups as $file): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($file['filename']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo formatFileSize($file['size']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo date('d/m/Y H:i:s', strtotime($file['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="index.php?module=backup&download=<?php echo urlencode($file['filename']); ?>"
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form method="POST" class="inline" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa file này?');">
                                    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($file['filename']); ?>">
                                    <button type="submit" name="delete" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?> 