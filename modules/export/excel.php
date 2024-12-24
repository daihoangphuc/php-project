<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'vendor/autoload.php'; // Require PHPSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportExcel {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function exportMembers() {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set headers
        $sheet->setCellValue('A1', 'Mã SV');
        $sheet->setCellValue('B1', 'Họ Tên');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Lớp');
        $sheet->setCellValue('E1', 'Chức vụ');
        $sheet->setCellValue('F1', 'Giới tính');
        
        // Get data
        $sql = "SELECT n.MaSinhVien, n.HoTen, n.Email, l.TenLop, c.TenChucVu, n.GioiTinh 
                FROM nguoidung n
                LEFT JOIN lophoc l ON n.LopHocId = l.Id
                LEFT JOIN chucvu c ON n.ChucVuId = c.Id
                ORDER BY n.Id";
        $result = $this->conn->query($sql);
        
        $row = 2;
        while ($data = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $row, $data['MaSinhVien']);
            $sheet->setCellValue('B' . $row, $data['HoTen']);
            $sheet->setCellValue('C' . $row, $data['Email']);
            $sheet->setCellValue('D' . $row, $data['TenLop']);
            $sheet->setCellValue('E' . $row, $data['TenChucVu']);
            $sheet->setCellValue('F' . $row, $data['GioiTinh']);
            $row++;
        }
        
        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'danh_sach_thanh_vien_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
    }
    
    public function exportActivities() {
        // Tương tự như exportMembers nhưng cho hoạt động
    }
    
    public function exportTasks() {
        // Tương tự như exportMembers nhưng cho nhiệm vụ
    }
}

// Xử lý yêu cầu xuất báo cáo
if (isset($_GET['type'])) {
    $export = new ExportExcel($conn);
    
    switch ($_GET['type']) {
        case 'members':
            $export->exportMembers();
            break;
        case 'activities':
            $export->exportActivities();
            break;
        case 'tasks':
            $export->exportTasks();
            break;
    }
    exit;
}
?>

<!-- Giao diện xuất báo cáo -->
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Xuất báo cáo</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="?module=export&type=members" 
           class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <h2 class="text-xl font-semibold mb-2">Danh sách thành viên</h2>
            <p class="text-gray-600">Xuất danh sách tất cả thành viên CLB</p>
        </a>
        
        <a href="?module=export&type=activities"
           class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <h2 class="text-xl font-semibold mb-2">Báo cáo hoạt động</h2>
            <p class="text-gray-600">Xuất báo cáo các hoạt động của CLB</p>
        </a>
        
        <a href="?module=export&type=tasks"
           class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow">
            <h2 class="text-xl font-semibold mb-2">Báo cáo nhiệm vụ</h2>
            <p class="text-gray-600">Xuất báo cáo phân công và thực hiện nhiệm vụ</p>
        </a>
    </div>
</div> 