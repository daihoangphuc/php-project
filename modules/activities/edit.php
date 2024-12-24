<?php
require_once 'modules/activities/activities.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$activities = new Activities($conn);
$error = '';

// Lấy thông tin hoạt động
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$activity = $activities->getById($id);

if (!$activity) {
    $_SESSION['error'] = 'Hoạt động không tồn tại';
    header('Location: index.php?module=activities');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => validateInput($_POST['title']),
        'description' => validateInput($_POST['description']),
        'location' => validateInput($_POST['location']),
        'start_time' => $_POST['start_time'],
        'end_time' => $_POST['end_time'],
        'max_participants' => (int)$_POST['max_participants'],
        'status' => (int)$_POST['status']
    ];
    
    $result = $activities->update($id, $data);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: index.php?module=activities');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<div class="p-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Sửa hoạt động</h2>
                <a href="index.php?module=activities" class="text-blue-500 hover:underline">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Quay lại danh sách
                </a>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-800 rounded-lg p-4 mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Tên hoạt động <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" required
                           value="<?php echo htmlspecialchars($activity['TenHoatDong']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Mô tả <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" rows="4" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($activity['MoTa']); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Địa điểm <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="location" required
                           value="<?php echo htmlspecialchars($activity['DiaDiem']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Thời gian bắt đầu <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="start_time" required
                               value="<?php echo date('Y-m-d\TH:i', strtotime($activity['NgayBatDau'])); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Thời gian kết thúc <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="end_time" required
                               value="<?php echo date('Y-m-d\TH:i', strtotime($activity['ThoiGianKetThuc'])); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Số lượng tham gia tối đa <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="max_participants" required min="1"
                               value="<?php echo (int)$activity['SoLuongToiDa']; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Trạng thái <span class="text-red-500">*</span>
                        </label>
                        <select name="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="0" <?php echo $activity['TrangThai'] == 0 ? 'selected' : ''; ?>>
                                Chưa diễn ra
                            </option>
                            <option value="1" <?php echo $activity['TrangThai'] == 1 ? 'selected' : ''; ?>>
                                Đang diễn ra
                            </option>
                            <option value="2" <?php echo $activity['TrangThai'] == 2 ? 'selected' : ''; ?>>
                                Đã kết thúc
                            </option>
                        </select>
                    </div>
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