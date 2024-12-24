<?php
require_once 'modules/users/users.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$users = new Users($conn);
$error = '';

// Lấy thông tin thành viên
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = $users->getById($id);

if (!$user) {
    $_SESSION['error'] = 'Thành viên không tồn tại';
    header('Location: index.php?module=users');
    exit;
}

// Lấy danh sách lớp học
$sql = "SELECT l.*, k.TenKhoaTruong 
        FROM lophoc l
        JOIN khoatruong k ON l.KhoaTruongId = k.Id
        ORDER BY k.TenKhoaTruong, l.TenLop";
$result = $conn->query($sql);
$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}

// Lấy danh sách chức vụ
$sql = "SELECT * FROM chucvu ORDER BY TenChucVu";
$result = $conn->query($sql);
$positions = [];
while ($row = $result->fetch_assoc()) {
    $positions[] = $row;
}

// Lấy danh sách vai trò
$sql = "SELECT * FROM vaitro ORDER BY TenVaiTro";
$result = $conn->query($sql);
$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

// Lấy vai trò hiện tại của user
$sql = "SELECT VaiTroId FROM vaitronguoidung WHERE NguoiDungId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$current_roles = [];
while ($row = $result->fetch_assoc()) {
    $current_roles[] = $row['VaiTroId'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'fullname' => validateInput($_POST['fullname']),
        'email' => validateInput($_POST['email']),
        'class_id' => (int)$_POST['class_id'],
        'position_id' => (int)$_POST['position_id'],
        'gender' => (int)$_POST['gender'],
        'birthday' => $_POST['birthday'],
        'status' => (int)$_POST['status'],
        'roles' => isset($_POST['roles']) ? $_POST['roles'] : []
    ];
    
    $result = $users->update($id, $data);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: index.php?module=users');
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
                <h2 class="text-xl font-semibold">Sửa thông tin thành viên</h2>
                <a href="index.php?module=users" class="text-blue-500 hover:underline">
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
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Tên đăng nhập
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($user['TenDangNhap']); ?>" readonly
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Mã sinh viên
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($user['MaSinhVien']); ?>" readonly
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Họ và tên <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="fullname" required
                               value="<?php echo htmlspecialchars($user['HoTen']); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" required
                               value="<?php echo htmlspecialchars($user['Email']); ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Giới tính
                        </label>
                        <select name="gender"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="1" <?php echo $user['GioiTinh'] == 1 ? 'selected' : ''; ?>>Nam</option>
                            <option value="0" <?php echo $user['GioiTinh'] == 0 ? 'selected' : ''; ?>>Nữ</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Ngày sinh
                        </label>
                        <input type="date" name="birthday"
                               value="<?php echo $user['NgaySinh']; ?>"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Lớp <span class="text-red-500">*</span>
                        </label>
                        <select name="class_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Chọn lớp</option>
                            <?php
                            $current_faculty = '';
                            foreach ($classes as $class):
                                if ($class['TenKhoaTruong'] !== $current_faculty):
                                    if ($current_faculty !== '') echo '</optgroup>';
                                    $current_faculty = $class['TenKhoaTruong'];
                                    echo '<optgroup label="' . htmlspecialchars($current_faculty) . '">';
                                endif;
                            ?>
                                <option value="<?php echo $class['Id']; ?>" 
                                        <?php echo $user['LopHocId'] == $class['Id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['TenLop']); ?>
                                </option>
                            <?php
                            endforeach;
                            if ($current_faculty !== '') echo '</optgroup>';
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Chức vụ <span class="text-red-500">*</span>
                        </label>
                        <select name="position_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Chọn chức vụ</option>
                            <?php foreach ($positions as $position): ?>
                                <option value="<?php echo $position['Id']; ?>"
                                        <?php echo $user['ChucVuId'] == $position['Id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($position['TenChucVu']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Trạng thái <span class="text-red-500">*</span>
                        </label>
                        <select name="status" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="1" <?php echo $user['TranThai'] == 1 ? 'selected' : ''; ?>>Hoạt động</option>
                            <option value="0" <?php echo $user['TranThai'] == 0 ? 'selected' : ''; ?>>Khóa</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Vai trò
                        </label>
                        <div class="space-y-2">
                            <?php foreach ($roles as $role): ?>
                                <label class="inline-flex items-center mr-6">
                                    <input type="checkbox" name="roles[]" value="<?php echo $role['Id']; ?>"
                                           <?php echo in_array($role['Id'], $current_roles) ? 'checked' : ''; ?>
                                           class="rounded text-blue-600 focus:ring-blue-500 h-4 w-4">
                                    <span class="ml-2 text-sm text-gray-700">
                                        <?php echo htmlspecialchars($role['TenVaiTro']); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
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