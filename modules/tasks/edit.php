<?php
require_once 'modules/tasks/tasks.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$tasks = new Tasks($conn);
$error = '';
$success = '';

// Lấy thông tin nhiệm vụ
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$task = $tasks->getById($id);

if (!$task) {
    $_SESSION['error'] = 'Nhiệm vụ không tồn tại';
    header('Location: index.php?module=tasks');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => validateInput($_POST['name']),
        'description' => validateInput($_POST['description']),
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'status' => (int)$_POST['status'],
        'members' => isset($_POST['members']) ? $_POST['members'] : []
    ];
    
    $result = $tasks->update($id, $data);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: index.php?module=tasks');
        exit;
    } else {
        $error = $result['message'];
    }
}

// Lấy danh sách thành viên có thể phân công
$available_members = $tasks->getAvailableMembers();

// Lấy danh sách thành viên đã được phân công
$assigned_members = $tasks->getAssignedMembers($id);
$assigned_member_ids = array_column($assigned_members, 'Id');
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Sửa thông tin nhiệm vụ</h2>
            <a href="index.php?module=tasks" class="text-blue-500 hover:underline">
                Quay lại danh sách
            </a>
        </div>

        <?php if ($error): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Tên nhiệm vụ <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           value="<?php echo htmlspecialchars($task['TenNhiemVu']); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Thời gian bắt đầu <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="start_date" required
                           value="<?php echo date('Y-m-d\TH:i', strtotime($task['NgayBatDau'])); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Thời gian kết thúc <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="end_date" required
                           value="<?php echo date('Y-m-d\TH:i', strtotime($task['NgayKetThuc'])); ?>"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Trạng thái
                    </label>
                    <select name="status" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="0" <?php echo $task['TrangThai'] == 0 ? 'selected' : ''; ?>>
                            Chưa bắt đầu
                        </option>
                        <option value="1" <?php echo $task['TrangThai'] == 1 ? 'selected' : ''; ?>>
                            Đang thực hiện
                        </option>
                        <option value="2" <?php echo $task['TrangThai'] == 2 ? 'selected' : ''; ?>>
                            Đã hoàn thành
                        </option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Mô tả
                    </label>
                    <textarea name="description" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($task['MoTa']); ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Phân công cho thành viên
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php foreach ($available_members as $member): ?>
                            <label class="flex items-center space-x-3">
                                <input type="checkbox" name="members[]" value="<?php echo $member['Id']; ?>"
                                       <?php echo in_array($member['Id'], $assigned_member_ids) ? 'checked' : ''; ?>
                                       class="rounded text-blue-600 focus:ring-blue-500">
                                <span>
                                    <?php echo htmlspecialchars($member['HoTen']); ?>
                                    <span class="text-sm text-gray-500">
                                        (<?php echo htmlspecialchars($member['MaSinhVien']); ?>)
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div> 