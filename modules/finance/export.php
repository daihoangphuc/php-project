<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

if (!is_admin()) {
    header('Location: index.php');
    exit;
}

// Lấy tham số lọc
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Xây dựng câu query
$sql = "SELECT tc.*, nd.HoTen as NguoiTao
        FROM taichinh tc
        LEFT JOIN nguoidung nd ON tc.NguoiTaoId = nd.Id
        WHERE tc.NgayGiaoDich BETWEEN ? AND ?
        ORDER BY tc.NgayGiaoDich DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Tính tổng thu/chi
$sqlTotal = "SELECT 
    SUM(CASE WHEN LoaiGiaoDich = 1 THEN SoTien ELSE 0 END) as TongThu,
    SUM(CASE WHEN LoaiGiaoDich = 0 THEN SoTien ELSE 0 END) as TongChi
    FROM taichinh 
    WHERE NgayGiaoDich BETWEEN ? AND ?";

$stmtTotal = $conn->prepare($sqlTotal);
$stmtTotal->bind_param("ss", $startDate, $endDate);
$stmtTotal->execute();
$totals = $stmtTotal->get_result()->fetch_assoc();

// Thiết lập header cho file Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="bao-cao-tai-chinh.xls"');
header('Cache-Control: max-age=0');

// Xuất header bảng
echo '<table border="1">
    <tr>
        <th colspan="6">BÁO CÁO TÀI CHÍNH</th>
    </tr>
    <tr>
        <th colspan="6">Từ ngày ' . date('d/m/Y', strtotime($startDate)) . ' đến ngày ' . date('d/m/Y', strtotime($endDate)) . '</th>
    </tr>
    <tr>
        <th colspan="6">Tổng thu: ' . number_format($totals['TongThu'], 0, ',', '.') . ' VNĐ - Tổng chi: ' . number_format($totals['TongChi'], 0, ',', '.') . ' VNĐ</th>
    </tr>
    <tr>
        <th>Ngày</th>
        <th>Loại</th>
        <th>Số tiền</th>
        <th>Mô tả</th>
        <th>Người tạo</th>
        <th>Thời gian tạo</th>
    </tr>';

// Xuất dữ liệu
foreach ($transactions as $trans) {
    echo '<tr>
        <td>' . date('d/m/Y', strtotime($trans['NgayGiaoDich'])) . '</td>
        <td>' . ($trans['LoaiGiaoDich'] == 1 ? 'Thu' : 'Chi') . '</td>
        <td>' . number_format($trans['SoTien'], 0, ',', '.') . '</td>
        <td>' . htmlspecialchars($trans['MoTa']) . '</td>
        <td>' . htmlspecialchars($trans['NguoiTao']) . '</td>
        <td>' . date('d/m/Y H:i', strtotime($trans['ThoiGianTao'])) . '</td>
    </tr>';
}

echo '</table>';

// Log hoạt động
logActivity(
    $_SERVER['REMOTE_ADDR'],
    $_SESSION['user']['username'],
    'Xuất báo cáo tài chính',
    'Thành công',
    "Period: $startDate - $endDate"
); 