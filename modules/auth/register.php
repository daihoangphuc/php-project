<?php
require_once 'modules/auth/auth.php';

// Nếu đã đăng nhập thì chuyển về trang chủ
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$auth = new Auth($conn);
$error = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => validateInput($_POST['username']),
        'password' => $_POST['password'],
        'confirm_password' => $_POST['confirm_password'],
        'fullname' => validateInput($_POST['fullname']),
        'email' => validateInput($_POST['email']),
        'student_id' => validateInput($_POST['student_id']),
        'class_id' => (int)$_POST['class_id']
    ];
    
    // Kiểm tra mật khẩu xác nhận
    if ($data['password'] !== $data['confirm_password']) {
        $error = 'Mật khẩu xác nhận không khớp';
    } else {
        $result = $auth->register($data);
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: index.php?module=auth&action=login');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Đăng ký tài khoản
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Hoặc
                <a href="index.php?module=auth&action=login" class="font-medium text-blue-600 hover:text-blue-500">
                    đăng nhập nếu đã có tài khoản
                </a>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-800 rounded-lg p-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">
                        Tên đăng nhập <span class="text-red-500">*</span>
                    </label>
                    <input id="username" name="username" type="text" required
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Mật khẩu <span class="text-red-500">*</span>
                    </label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                        Xác nhận mật khẩu <span class="text-red-500">*</span>
                    </label>
                    <input id="confirm_password" name="confirm_password" type="password" required
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="fullname" class="block text-sm font-medium text-gray-700">
                        Họ và tên <span class="text-red-500">*</span>
                    </label>
                    <input id="fullname" name="fullname" type="text" required
                           value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>"
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input id="email" name="email" type="email" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="student_id" class="block text-sm font-medium text-gray-700">
                        Mã sinh viên <span class="text-red-500">*</span>
                    </label>
                    <input id="student_id" name="student_id" type="text" required
                           value="<?php echo isset($_POST['student_id']) ? htmlspecialchars($_POST['student_id']) : ''; ?>"
                           class="mt-1 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>

                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700">
                        Lớp <span class="text-red-500">*</span>
                    </label>
                    <select id="class_id" name="class_id" required
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md">
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
                                    <?php echo isset($_POST['class_id']) && $_POST['class_id'] == $class['Id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['TenLop']); ?>
                            </option>
                        <?php
                        endforeach;
                        if ($current_faculty !== '') echo '</optgroup>';
                        ?>
                    </select>
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-user-plus"></i>
                    </span>
                    Đăng ký
                </button>
            </div>
        </form>

        <div class="text-center">
            <a href="index.php" class="text-sm text-gray-600 hover:text-blue-500">
                <i class="fas fa-arrow-left mr-1"></i>
                Quay lại trang chủ
            </a>
        </div>
    </div>
</div> 