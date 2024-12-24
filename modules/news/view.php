<?php
require_once 'modules/news/news.php';

$news = new News($conn);

// Lấy thông tin tin tức
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$article = $news->getById($id);

if (!$article) {
    $_SESSION['error'] = 'Tin tức không tồn tại';
    header('Location: index.php?module=news');
    exit;
}
?>

<div class="p-4">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Chi tiết tin tức</h2>
            <div class="flex space-x-4">
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                    <a href="index.php?module=news&action=edit&id=<?php echo $article['Id']; ?>"
                       class="text-blue-500 hover:underline">
                        Sửa tin tức
                    </a>
                <?php endif; ?>
                <a href="index.php?module=news" class="text-blue-500 hover:underline">
                    Quay lại danh sách
                </a>
            </div>
        </div>

        <!-- Thông tin tin tức -->
        <div class="space-y-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <?php echo htmlspecialchars($article['TieuDe']); ?>
                </h1>
                <div class="mt-2 flex items-center text-sm text-gray-500">
                    <div class="flex items-center">
                        <i class="fas fa-user mr-2"></i>
                        <?php echo htmlspecialchars($article['TenNguoiTao']); ?>
                        <span class="mx-2">-</span>
                        <?php echo htmlspecialchars($article['MaSinhVien']); ?>
                    </div>
                    <span class="mx-2">•</span>
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <?php echo date('d/m/Y H:i', strtotime($article['NgayTao'])); ?>
                    </div>
                </div>
            </div>

            <?php if ($article['FileDinhKem']): ?>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="flex items-center">
                        <i class="fas fa-paperclip text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600">File đính kèm:</span>
                        <a href="uploads/<?php echo $article['FileDinhKem']; ?>" 
                           class="ml-2 text-blue-500 hover:underline"
                           target="_blank">
                            <?php echo $article['FileDinhKem']; ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="prose max-w-none">
                <?php 
                // Hiển thị nội dung với định dạng HTML (đã được xử lý bởi TinyMCE)
                echo $article['NoiDung']; 
                ?>
            </div>

            <!-- Phần chia sẻ mạng xã hội -->
            <div class="border-t pt-6">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Chia sẻ:</span>
                    <?php
                    $share_url = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
                    $share_title = urlencode($article['TieuDe']);
                    ?>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>"
                       target="_blank"
                       class="text-blue-600 hover:text-blue-700">
                        <i class="fab fa-facebook-square text-xl"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_title; ?>"
                       target="_blank"
                       class="text-blue-400 hover:text-blue-500">
                        <i class="fab fa-twitter-square text-xl"></i>
                    </a>
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $share_url; ?>&title=<?php echo $share_title; ?>"
                       target="_blank"
                       class="text-blue-700 hover:text-blue-800">
                        <i class="fab fa-linkedin text-xl"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Thêm CSS cho nội dung từ TinyMCE -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/typography.min.css" rel="stylesheet"> 