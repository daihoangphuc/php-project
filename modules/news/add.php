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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => validateInput($_POST['title']),
        'content' => validateInput($_POST['content']),
        'user_id' => $_SESSION['user']['id']
    ];
    
    // Xử lý file đính kèm nếu có
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === 0) {
        $data['file'] = $_FILES['attachment'];
    }
    
    $result = $news->create($data);
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
            <h2 class="text-xl font-semibold">Thêm tin tức mới</h2>
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
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    Nội dung <span class="text-red-500">*</span>
                </label>
                <textarea name="content" rows="10" required
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">
                    File đính kèm
                </label>
                <div class="mt-1">
                    <input type="file" name="attachment"
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-2 file:px-4
                                  file:rounded-full file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-blue-50 file:text-blue-700
                                  hover:file:bg-blue-100">
                    <p class="mt-1 text-sm text-gray-500">
                        Cho phép upload file: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG. Tối đa 5MB.
                    </p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Thêm mới
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