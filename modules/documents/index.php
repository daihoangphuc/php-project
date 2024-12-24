<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

// Lấy danh sách tài liệu
$sql = "SELECT tl.*, nd.HoTen as NguoiTao 
        FROM tailieu tl
        LEFT JOIN nguoidung nd ON tl.NguoiTaoId = nd.Id
        WHERE 1=1";

// Xử lý tìm kiếm
if (isset($_GET['search'])) {
    $search = validateInput($_GET['search']);
    $sql .= " AND (TenTaiLieu LIKE '%$search%' OR MoTa LIKE '%$search%')";
}

// Xử lý phân loại
if (isset($_GET['category'])) {
    $category = validateInput($_GET['category']);
    $sql .= " AND PhanLoai = '$category'";
}

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$sql .= " ORDER BY NgayTao DESC LIMIT $limit OFFSET $offset";
$documents = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Đếm tổng số tài liệu
$totalDocs = $conn->query("SELECT COUNT(*) as total FROM tailieu")->fetch_assoc()['total'];
$totalPages = ceil($totalDocs / $limit);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Quản lý tài liệu</h1>
        <?php if (isAdmin()): ?>
            <button data-modal-target="uploadModal" data-modal-toggle="uploadModal" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Thêm tài liệu
            </button>
        <?php endif; ?>
    </div>

    <!-- Thanh tìm kiếm và lọc -->
    <div class="mb-6 flex gap-4">
        <div class="flex-1">
            <form class="flex gap-2">
                <input type="hidden" name="module" value="documents">
                <input type="text" name="search" value="<?= $_GET['search'] ?? '' ?>" 
                       class="flex-1 rounded-lg border-gray-300" 
                       placeholder="Tìm kiếm tài liệu...">
                <select name="category" class="rounded-lg border-gray-300">
                    <option value="">Tất cả phân loại</option>
                    <option value="Tài liệu học tập">Tài liệu học tập</option>
                    <option value="Biểu mẫu">Biểu mẫu</option>
                    <option value="Khác">Khác</option>
                </select>
                <button type="submit" class="bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200">
                    Tìm kiếm
                </button>
            </form>
        </div>
    </div>

    <!-- Danh sách tài liệu -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tên tài liệu
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Phân loại
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Người tạo
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
                <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <i class="far fa-file-alt text-gray-400 mr-3"></i>
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($doc['TenTaiLieu']) ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= htmlspecialchars($doc['PhanLoai']) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= htmlspecialchars($doc['NguoiTao']) ?>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <?= date('d/m/Y', strtotime($doc['NgayTao'])) ?>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <a href="uploads/documents/<?= $doc['DuongDan'] ?>" 
                               class="text-blue-600 hover:text-blue-900 mr-3">
                                Tải xuống
                            </a>
                            <?php if (isAdmin()): ?>
                                <button onclick="deleteDocument(<?= $doc['Id'] ?>)"
                                        class="text-red-600 hover:text-red-900">
                                    Xóa
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?module=documents&page=<?= $i ?>" 
                       class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 <?= $i === $page ? 'bg-blue-50' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal thêm tài liệu -->
<div id="uploadModal" tabindex="-1" aria-hidden="true" 
     class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow">
            <div class="flex items-start justify-between p-4 border-b rounded-t">
                <h3 class="text-xl font-semibold text-gray-900">
                    Thêm tài liệu mới
                </h3>
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="uploadModal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                </button>
            </div>
            <form action="?module=documents&action=upload" method="POST" enctype="multipart/form-data">
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Tên tài liệu
                        </label>
                        <input type="text" name="name" required
                               class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Phân loại
                        </label>
                        <select name="category" required
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="Tài liệu học tập">Tài liệu học tập</option>
                            <option value="Biểu mẫu">Biểu mẫu</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-900">
                            Tệp tài liệu
                        </label>
                        <input type="file" name="file" required
                               class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none">
                    </div>
                </div>
                <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                    <button type="submit" 
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        Tải lên
                    </button>
                    <button type="button" data-modal-hide="uploadModal"
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                        Hủy
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteDocument(id) {
    if (confirm('Bạn có chắc chắn muốn xóa tài liệu này?')) {
        fetch(`?module=documents&action=delete&id=${id}`, {
            method: 'POST'
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Có lỗi xảy ra khi xóa tài liệu');
              }
          });
    }
}
</script> 