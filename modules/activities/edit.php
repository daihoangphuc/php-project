<?php
redirectIfNotAdmin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: index.php?module=activities');
    exit;
}

// Lấy thông tin hoạt động
$activity = $conn->query("SELECT * FROM hoatdong WHERE Id = " . intval($id))->fetch_assoc();
if (!$activity) {
    header('Location: index.php?module=activities');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate dữ liệu
    $tenHoatDong = $_POST['tenHoatDong'] ?? '';
    $moTa = $_POST['moTa'] ?? '';
    $ngayBatDau = $_POST['ngayBatDau'] ?? '';
    $ngayKetThuc = $_POST['ngayKetThuc'] ?? '';
    $diaDiem = $_POST['diaDiem'] ?? '';
    $soLuong = $_POST['soLuong'] ?? 0;
    $trangThai = $_POST['trangThai'] ?? 0;
    
    if (empty($tenHoatDong) || empty($ngayBatDau) || empty($ngayKetThuc)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        // Cập nhật hoạt động
        $sql = "UPDATE hoatdong SET 
                TenHoatDong = ?, 
                MoTa = ?, 
                NgayBatDau = ?, 
                NgayKetThuc = ?, 
                DiaDiem = ?, 
                SoLuong = ?, 
                TrangThai = ? 
                WHERE Id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiii", $tenHoatDong, $moTa, $ngayBatDau, $ngayKetThuc, $diaDiem, $soLuong, $trangThai, $id);
        
        if ($stmt->execute()) {
            // Ghi log
            $sql = "INSERT INTO nhatkyhoatdong (IP, NguoiDung, HanhDong, KetQua, ChiTiet) 
                    VALUES (?, ?, 'Cập nhật hoạt động', 'Thành công', ?)";
            $stmt = $conn->prepare($sql);
            $ip = $_SERVER['REMOTE_ADDR'];
            $nguoiDung = $_SESSION['user']['TenDangNhap'];
            $chiTiet = "Hoạt động ID: $id - $tenHoatDong";
            $stmt->bind_param("sss", $ip, $nguoiDung, $chiTiet);
            $stmt->execute();
            
            header('Location: index.php?module=activities');
            exit;
        } else {
            $error = 'Lỗi: ' . $conn->error;
        }
    }
}

$pageTitle = 'Sửa hoạt động';
?>

<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Sửa hoạt động</h2>
            <a href="?module=activities" class="text-blue-500 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tên hoạt động <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="tenHoatDong" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($activity['TenHoatDong']) ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Địa điểm <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="diaDiem" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($activity['DiaDiem']) ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Thời gian bắt đầu <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="ngayBatDau" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= date('Y-m-d\TH:i', strtotime($activity['NgayBatDau'])) ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Thời gian kết thúc <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" name="ngayKetThuc" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= date('Y-m-d\TH:i', strtotime($activity['NgayKetThuc'])) ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Số lượng tối đa
                    </label>
                    <input type="number" name="soLuong" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           value="<?= htmlspecialchars($activity['SoLuong']) ?>">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Trạng thái
                    </label>
                    <select name="trangThai"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="0" <?= $activity['TrangThai'] == 0 ? 'selected' : '' ?>>
                            Chưa diễn ra
                        </option>
                        <option value="1" <?= $activity['TrangThai'] == 1 ? 'selected' : '' ?>>
                            Đang diễn ra
                        </option>
                        <option value="2" <?= $activity['TrangThai'] == 2 ? 'selected' : '' ?>>
                            Đã kết thúc
                        </option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mô tả
                </label>
                <textarea name="moTa" rows="4"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?= htmlspecialchars($activity['MoTa']) ?></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <button type="button" onclick="window.location='?module=activities'"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Hủy
                </button>
                <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                    Cập nhật
                </button>
            </div>
        </form>
    </div>
</div> 