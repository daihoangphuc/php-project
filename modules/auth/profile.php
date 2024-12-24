<?php
require_once 'modules/auth/auth.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$auth = new Auth($conn);
$error = '';
$success = '';

// Lấy thông tin người dùng
$sql = "SELECT n.*, l.TenLop, k.TenKhoaTruong, c.TenChucVu
        FROM nguoidung n
        LEFT JOIN lophoc l ON n.LopHocId = l.Id
        LEFT JOIN khoatruong k ON l.KhoaTruongId = k.Id
        LEFT JOIN chucvu c ON n.ChucVuId = c.Id
        WHERE n.Id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user']['id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'fullname' => validateInput($_POST['fullname']),
        'email' => validateInput($_POST['email']),
        'gender' => (int)$_POST['gender'],
        'birthday' => $_POST['birthday']
    ];
    
    $result = $auth->updateProfile($_SESSION['user']['id'], $data);
    if ($result['success']) {
        $success = $result['message'];
        // Cập nhật lại thông tin hiển thị
        $user['HoTen'] = $data['fullname'];
        $user['Email'] = $data['email'];
        $user['GioiTinh'] = $data['gender'];
        $user['NgaySinh'] = $data['birthday'];
    } else {
        $error = $result['message'];
    }
}
?>

<div class="p-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-semibold">Thông tin cá nhân</h2>
                <a href="index.php?module=auth&action=change_password" class="text-blue-500 hover:underline">
                    <i class="fas fa-key mr-1"></i>
                    Đổi mật khẩu
                </a>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 text-red-800 rounded-lg p-4 mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-50 text-green-800 rounded-lg p-4 mb-6">
                    <?php echo $success; ?>
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
                            Lớp
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($user['TenLop']); ?>" readonly
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Khoa
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($user['TenKhoaTruong']); ?>" readonly
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Chức vụ
                        </label>
                        <input type="text" value="<?php echo htmlspecialchars($user['TenChucVu']); ?>" readonly
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Ngày tham gia
                        </label>
                        <input type="text" value="<?php echo date('d/m/Y', strtotime($user['NgayTao'])); ?>" readonly
                               class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
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