<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!is_admin()) {
    header('Location: index.php');
    exit;
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$isEdit = $id !== false && $id > 0;

if ($isEdit) {
    // Lấy thông tin nhiệm vụ
    $stmt = $conn->prepare("SELECT * FROM nhiemvu WHERE Id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    
    if (!$task) {
        die("Nhiệm vụ không tồn tại!");
    }
    
    // Lấy danh sách người được phân công
    $stmt = $conn->prepare("SELECT NguoiDungId FROM phancong_nhiemvu WHERE NhiemVuId = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $assignees = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $assigneeIds = array_column($assignees, 'NguoiDungId');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tieuDe = filter_input(INPUT_POST, 'tieuDe', FILTER_SANITIZE_STRING);
    $moTa = filter_input(INPUT_POST, 'moTa', FILTER_SANITIZE_STRING);
    $hanHoanThanh = filter_input(INPUT_POST, 'hanHoanThanh', FILTER_SANITIZE_STRING);
    $trangThai = filter_input(INPUT_POST, 'trangThai', FILTER_VALIDATE_INT);
    $nguoiDuocGiao = $_POST['nguoiDuocGiao'] ?? [];
    
    if ($isEdit) {
        // Cập nhật nhiệm vụ
        $stmt = $conn->prepare("UPDATE nhiemvu SET TieuDe = ?, MoTa = ?, HanHoanThanh = ?, TrangThai = ? WHERE Id = ?");
        $stmt->bind_param("sssis", $tieuDe, $moTa, $hanHoanThanh, $trangThai, $id);
        
        if ($stmt->execute()) {
            // Cập nhật phân công
            $stmt = $conn->prepare("DELETE FROM phancong_nhiemvu WHERE NhiemVuId = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            if (!empty($nguoiDuocGiao)) {
                $stmt = $conn->prepare("INSERT INTO phancong_nhiemvu (NhiemVuId, NguoiDungId) VALUES (?, ?)");
                foreach ($nguoiDuocGiao as $userId) {
                    $stmt->bind_param("ii", $id, $userId);
                    $stmt->execute();
                }
            }
            
            header('Location: ?module=tasks');
            exit;
        }
    } else {
        // Thêm nhiệm vụ mới
        $stmt = $conn->prepare("INSERT INTO nhiemvu (TieuDe, MoTa, HanHoanThanh, TrangThai, NguoiTaoId) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $tieuDe, $moTa, $hanHoanThanh, $trangThai, $_SESSION['user']['id']);
        
        if ($stmt->execute()) {
            $taskId = $stmt->insert_id;
            
            if (!empty($nguoiDuocGiao)) {
                $stmt = $conn->prepare("INSERT INTO phancong_nhiemvu (NhiemVuId, NguoiDungId) VALUES (?, ?)");
                foreach ($nguoiDuocGiao as $userId) {
                    $stmt->bind_param("ii", $taskId, $userId);
                    $stmt->execute();
                }
            }
            
            header('Location: ?module=tasks');
            exit;
        }
    }
}

// Lấy danh sách người dùng cho phân công
$users = $conn->query("SELECT Id, HoTen FROM nguoidung ORDER BY HoTen")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">
            <?= $isEdit ? 'Sửa nhiệm vụ' : 'Thêm nhiệm vụ mới' ?>
        </h1>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tiêu đề</label>
                <input type="text" name="tieuDe" required
                       value="<?= $isEdit ? htmlspecialchars($task['TieuDe']) : '' ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Mô tả</label>
                <textarea name="moTa" rows="4"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?= $isEdit ? htmlspecialchars($task['MoTa']) : '' ?></textarea>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Hạn hoàn thành</label>
                <input type="date" name="hanHoanThanh" required
                       value="<?= $isEdit ? date('Y-m-d', strtotime($task['HanHoanThanh'])) : '' ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Trạng thái</label>
                <select name="trangThai" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0" <?= $isEdit && $task['TrangThai'] == 0 ? 'selected' : '' ?>>
                        Chưa bắt đầu
                    </option>
                    <option value="1" <?= $isEdit && $task['TrangThai'] == 1 ? 'selected' : '' ?>>
                        Đang thực hiện
                    </option>
                    <option value="2" <?= $isEdit && $task['TrangThai'] == 2 ? 'selected' : '' ?>>
                        Đã hoàn thành
                    </option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Người được giao</label>
                <select name="nguoiDuocGiao[]" multiple
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['Id'] ?>"
                                <?= $isEdit && in_array($user['Id'], $assigneeIds) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($user['HoTen']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="mt-1 text-sm text-gray-500">
                    Giữ Ctrl để chọn nhiều người
                </p>
            </div>
            
            <div class="flex justify-end space-x-4">
                <a href="?module=tasks"
                   class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <?= $isEdit ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
            </div>
        </form>
    </div>
</div> 