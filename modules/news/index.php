<?php
require_once 'includes/auth.php';
redirectIfNotLoggedIn();

// Phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Lấy danh sách tin tức
$sql = "SELECT t.*, nd.HoTen as NguoiTao 
        FROM tintuc t
        LEFT JOIN nguoidung nd ON t.NguoiTaoId = nd.Id
        ORDER BY t.NgayTao DESC 
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$news = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Đếm tổng số tin tức
$totalNews = $conn->query("SELECT COUNT(*) as total FROM tintuc")->fetch_assoc()['total'];
$totalPages = ceil($totalNews / $limit);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Quản lý tin tức</h1>
        <?php if (isAdmin()): ?>
            <button data-modal-target="addNewsModal" data-modal-toggle="addNewsModal"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Thêm tin tức
            </button>
        <?php endif; ?>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tiêu đề</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Người tạo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ngày tạo</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($news as $item): ?>
                        <tr>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($item['TieuDe']) ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= htmlspecialchars($item['NguoiTao']) ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?= date('d/m/Y H:i', strtotime($item['NgayTao'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="?module=news&action=view&id=<?= $item['Id'] ?>" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">Xem</a>
                                <?php if (isAdmin()): ?>
                                    <a href="?module=news&action=edit&id=<?= $item['Id'] ?>" 
                                       class="text-indigo-600 hover:text-indigo-900 mr-3">Sửa</a>
                                    <button onclick="deleteNews(<?= $item['Id'] ?>)" 
                                            class="text-red-600 hover:text-red-900">Xóa</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Phân trang -->
    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-6">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?module=news&page=<?= $i ?>" 
                       class="<?= $i === $page ? 'bg-blue-50 text-blue-600' : 'bg-white text-gray-500' ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium hover:bg-gray-50">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal thêm tin tức -->
<div id="addNewsModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <!-- Modal content -->
</div>

<script>
function deleteNews(id) {
    if (confirm('Bạn có chắc chắn muốn xóa tin tức này?')) {
        fetch(`?module=news&action=delete&id=${id}`, {
            method: 'POST'
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  location.reload();
              } else {
                  alert('Có lỗi xảy ra khi xóa tin tức');
              }
          });
    }
}
</script> 