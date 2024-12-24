<?php
require_once 'modules/tasks/tasks.php';

$tasks = new Tasks($conn);

// Lấy thông tin nhiệm vụ
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$task = $tasks->getById($id);

if (!$task) {
    $_SESSION['error'] = 'Nhiệm vụ không tồn tại';
    header('Location: index.php?module=tasks');
    exit;
}

// Lấy danh sách thành viên được phân công
$assigned_members = $tasks->getAssignedMembers($id);

// Kiểm tra người dùng hiện tại có được phân công không
$is_assigned = false;
if (isset($_SESSION['user'])) {
    foreach ($assigned_members as $member) {
        if ($member['Id'] == $_SESSION['user']['id']) {
            $is_assigned = true;
            break;
        }
    }
}
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Chi tiết nhiệm vụ</h2>
            <div class="flex space-x-4">
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="index.php?module=tasks&action=edit&id=<?php echo $task['Id']; ?>"
                       class="text-blue-500 hover:underline">
                        Sửa nhiệm vụ
                    </a>
                <?php endif; ?>
                <a href="index.php?module=tasks" class="text-blue-500 hover:underline">
                    Quay lại danh sách
                </a>
            </div>
        </div>

        <!-- Thông tin nhiệm vụ -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <h3 class="text-lg font-semibold mb-4">Thông tin chung</h3>
                <div class="space-y-3">
                    <p>
                        <span class="font-medium">Tên nhiệm vụ:</span>
                        <?php echo htmlspecialchars($task['TenNhiemVu']); ?>
                    </p>
                    <p>
                        <span class="font-medium">Thời gian bắt đầu:</span>
                        <?php echo date('d/m/Y H:i', strtotime($task['NgayBatDau'])); ?>
                    </p>
                    <p>
                        <span class="font-medium">Thời gian kết thúc:</span>
                        <?php echo date('d/m/Y H:i', strtotime($task['NgayKetThuc'])); ?>
                    </p>
                    <p>
                        <span class="font-medium">Trạng thái:</span>
                        <?php
                        $status_class = '';
                        $status_text = '';
                        switch ($task['TrangThai']) {
                            case 0:
                                $status_class = 'text-yellow-600';
                                $status_text = 'Chưa bắt đầu';
                                break;
                            case 1:
                                $status_class = 'text-blue-600';
                                $status_text = 'Đang thực hiện';
                                break;
                            case 2:
                                $status_class = 'text-green-600';
                                $status_text = 'Đã hoàn thành';
                                break;
                        }
                        ?>
                        <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </p>
                </div>

                <?php if ($task['MoTa']): ?>
                    <div class="mt-4">
                        <h4 class="font-medium mb-2">Mô tả:</h4>
                        <p class="text-gray-600">
                            <?php echo nl2br(htmlspecialchars($task['MoTa'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Thành viên được phân công</h3>
                <?php if (!empty($assigned_members)): ?>
                    <div class="space-y-4">
                        <?php foreach ($assigned_members as $member): ?>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-medium">
                                        <?php echo htmlspecialchars($member['HoTen']); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        MSSV: <?php echo htmlspecialchars($member['MaSinhVien']); ?>
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    Phân công: <?php echo date('d/m/Y H:i', strtotime($member['NgayPhanCong'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">Chưa có thành viên nào được phân công</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tiến độ và cập nhật (có thể thêm sau) -->
        <?php if ($is_assigned || (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin')): ?>
            <div class="mt-8">
                <h3 class="text-lg font-semibold mb-4">Cập nhật tiến độ</h3>
                <!-- Form cập nhật tiến độ có thể thêm sau -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <p class="text-blue-600">
                        Tính năng cập nhật tiến độ đang được phát triển...
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div> 