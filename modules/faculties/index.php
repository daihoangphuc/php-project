<?php
require_once 'modules/faculties/faculties.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$faculties = new Faculties($conn);

// Xử lý xóa khoa/trường
if (isset($_POST['delete'])) {
    $id = (int)$_POST['id'];
    $result = $faculties->delete($id);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header('Location: index.php?module=faculties');
    exit;
}

// Xử lý thêm khoa/trường
if (isset($_POST['add'])) {
    $name = validateInput($_POST['name']);
    $result = $faculties->create($name);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header('Location: index.php?module=faculties');
    exit;
}

// Xử lý cập nhật khoa/trường
if (isset($_POST['update'])) {
    $id = (int)$_POST['id'];
    $name = validateInput($_POST['name']);
    $result = $faculties->update($id, $name);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    header('Location: index.php?module=faculties');
    exit;
}

// Lấy từ khóa tìm kiếm
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Lấy danh sách khoa/trường
$list = $faculties->getAll($search);
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Quản lý khoa/trường</h2>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                <i class="fas fa-plus mr-2"></i>
                Thêm khoa/trường
            </button>
        </div>

        <!-- Thông báo -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-50 text-green-800 rounded-lg p-4 mb-6">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-50 text-red-800 rounded-lg p-4 mb-6">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Form tìm kiếm -->
        <form class="mb-6">
            <input type="hidden" name="module" value="faculties">
            <div class="flex gap-4">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                       placeholder="Tìm kiếm theo tên khoa/trường..."
                       class="flex-1 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-search mr-2"></i>
                    Tìm kiếm
                </button>
            </div>
        </form>

        <!-- Danh sách khoa/trường -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">ID</th>
                        <th scope="col" class="px-6 py-3">Tên khoa/trường</th>
                        <th scope="col" class="px-6 py-3">Số lớp</th>
                        <th scope="col" class="px-6 py-3">Ngày tạo</th>
                        <th scope="col" class="px-6 py-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $item): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <?php echo $item['Id']; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo htmlspecialchars($item['TenKhoaTruong']); ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo $item['SoLuongLop']; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo date('d/m/Y H:i', strtotime($item['NgayTao'])); ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-3">
                                    <button onclick="editFaculty(<?php echo $item['Id']; ?>, '<?php echo htmlspecialchars($item['TenKhoaTruong']); ?>')"
                                            class="text-blue-500 hover:underline">
                                        Sửa
                                    </button>
                                    <?php if ($item['SoLuongLop'] == 0): ?>
                                        <form method="POST" class="inline-block" 
                                              onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                            <input type="hidden" name="id" value="<?php echo $item['Id']; ?>">
                                            <button type="submit" name="delete" 
                                                    class="text-red-500 hover:underline">
                                                Xóa
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal thêm khoa/trường -->
<div id="addModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">Thêm khoa/trường mới</h3>
            <button onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tên khoa/trường <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" required
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex justify-end">
                <button type="submit" name="add"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>
                    Thêm mới
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal sửa khoa/trường -->
<div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium">Sửa khoa/trường</h3>
            <button onclick="document.getElementById('editModal').classList.add('hidden')"
                    class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tên khoa/trường <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="flex justify-end">
                <button type="submit" name="update"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-save mr-2"></i>
                    C���p nhật
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editFaculty(id, name) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('editModal').classList.remove('hidden');
}
</script> 