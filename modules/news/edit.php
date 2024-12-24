<?php
require_once 'modules/news/news.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: index.php?module=auth&action=login');
    exit;
}

$news = new News($conn);
$error = '';
$success = '';

// Lấy thông tin tin tức
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = $news->getById($id);

if (!$article) {
    $_SESSION['error'] = 'Tin tức không tồn tại';
    header('Location: index.php?module=news');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => validateInput($_POST['title']),
        'content' => validateInput($_POST['content']),
        'current_file' => $article['FileDinhKem']
    ];
    
    // Xử lý file đính kèm mới nếu có
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $data['file'] = $_FILES['attachment'];
    }
    
    $result = $news->update($id, $data);
    if ($result['success']) {
        $_SESSION['success'] = $result['message'];
        header('Location: index.php?module=news');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Sửa tin tức</h2>
            <a href="index.php?module=news" class="text-blue-500 hover:underline">
                Quay lại danh sách
            </a>
        </div>

        <?php if ($error): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Tiêu đề <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" required
                       value="<?php echo htmlspecialchars($article['TieuDe']); ?>"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Nội dung <span class="text-red-500">*</span>
                </label>
                <textarea name="content" rows="10" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo htmlspecialchars($article['NoiDung']); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    File đính kèm
                </label>
                <div class="mt-1 space-y-2">
                    <?php if ($article['FileDinhKem']): ?>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">File hiện tại:</span>
                            <a href="uploads/<?php echo $article['FileDinhKem']; ?>" 
                               class="text-blue-500 hover:underline"
                               target="_blank">
                                <?php echo $article['FileDinhKem']; ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" name="attachment"
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100">
                    <p class="text-sm text-gray-500">
                        Cho phép upload file: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG. Tối đa 5MB.
                        <?php if ($article['FileDinhKem']): ?>
                            <br>
                            Upload file mới sẽ thay thế file cũ.
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Thông tin khác
                </label>
                <div class="mt-2 text-sm text-gray-600">
                    <p>Người tạo: <?php echo htmlspecialchars($article['TenNguoiTao']); ?></p>
                    <p>MSSV: <?php echo htmlspecialchars($article['MaSinhVien']); ?></p>
                    <p>Ngày tạo: <?php echo date('d/m/Y H:i', strtotime($article['NgayTao'])); ?></p>
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

<!-- Thêm TinyMCE cho trình soạn thảo văn bản -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: 'textarea[name="content"]',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    language: 'vi',
    height: 500,
    // Cấu hình upload ảnh (có thể thêm sau)
    images_upload_url: 'upload.php',
    automatic_uploads: true,
    images_reuse_filename: true,
    relative_urls: false,
    remove_script_host: false,
    convert_urls: true
});
</script> 