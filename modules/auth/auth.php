<?php
class Auth {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function login($username, $password) {
        // Kiểm tra số lần đăng nhập sai
        $attempts = $this->checkLoginAttempts($username);
        if ($attempts >= 5) {
            return ['success' => false, 'message' => 'Tài khoản đã bị khóa 30 phút do đăng nhập sai nhiều lần'];
        }
        
        // Kiểm tra đăng nhập
        $sql = "SELECT * FROM nguoidung WHERE TenDangNhap = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if ($user && password_verify($password, $user['MatKhau'])) {
            $_SESSION['user'] = [
                'id' => $user['Id'],
                'username' => $user['TenDangNhap'],
                'name' => $user['HoTen'],
                'role' => $user['Quyen']
            ];
            
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $username,
                'Đăng nhập',
                'Thành công'
            );
            
            return ['success' => true];
        }
        
        // Lưu lần đăng nhập sai
        $this->addLoginAttempt($username);
        
        return ['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'];
    }
    
    private function checkLoginAttempts($username) {
        // Xóa các lần đăng nhập cũ (quá 30 phút)
        $sql = "DELETE FROM dangnhap_sai WHERE ThoiGian < DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        $this->conn->query($sql);
        
        // Đếm số lần đăng nhập sai
        $sql = "SELECT COUNT(*) as total FROM dangnhap_sai WHERE TenDangNhap = ? AND ThoiGian > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['total'];
    }
    
    private function addLoginAttempt($username) {
        $sql = "INSERT INTO dangnhap_sai (TenDangNhap, ThoiGian, IP) VALUES (?, NOW(), ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $username, $_SERVER['REMOTE_ADDR']);
        $stmt->execute();
    }
    
    public function register($data) {
        // Validate dữ liệu
        $required_fields = ['username', 'password', 'fullname', 'email', 'student_id', 'class_id'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
            }
        }
        
        // Kiểm tra username đã tồn tại
        $sql = "SELECT Id FROM nguoidung WHERE TenDangNhap = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Tên đăng nhập đã tồn tại'];
        }
        
        // Kiểm tra email đã tồn tại
        $sql = "SELECT Id FROM nguoidung WHERE Email = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email đã tồn tại'];
        }
        
        // Kiểm tra MSSV đã tồn tại
        $sql = "SELECT Id FROM nguoidung WHERE MaSinhVien = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $data['student_id']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Mã sinh viên đã tồn tại'];
        }
        
        // Hash mật khẩu
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Thêm người dùng mới
        $sql = "INSERT INTO nguoidung (TenDangNhap, MatKhauHash, HoTen, Email, MaSinhVien, LopHocId, ChucVuId) 
                VALUES (?, ?, ?, ?, ?, ?, 4)"; // 4 là ID của chức vụ "Thành viên"
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssi", 
            $data['username'],
            $password_hash,
            $data['fullname'],
            $data['email'],
            $data['student_id'],
            $data['class_id']
        );
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Gán vai trò member
            $sql = "INSERT INTO vaitronguoidung (VaiTroId, NguoiDungId) VALUES (2, ?)"; // 2 là ID của vai trò "member"
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Ghi log
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $data['username'],
                'Đăng ký tài khoản',
                'Thành công'
            );
            
            return ['success' => true, 'message' => 'Đăng ký thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function forgotPassword($email) {
        // Kiểm tra email tồn tại
        $sql = "SELECT Id, TenDangNhap, HoTen FROM nguoidung WHERE Email = ? AND TranThai = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email không tồn tại trong hệ thống'];
        }
        
        // Tạo token reset password
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Lưu token vào database
        $sql = "UPDATE nguoidung SET reset_token = ?, reset_expires = ? WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $token, $expires, $user['Id']);
        
        if ($stmt->execute()) {
            // Gửi email reset password
            $reset_link = "http://{$_SERVER['HTTP_HOST']}/index.php?module=auth&action=reset_password&token=$token";
            $to = $email;
            $subject = "Yêu cầu đặt lại mật khẩu";
            $message = "Xin chào {$user['HoTen']},\n\n";
            $message .= "Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản {$user['TenDangNhap']}.\n\n";
            $message .= "Vui lòng click vào link sau để đặt lại mật khẩu:\n";
            $message .= $reset_link . "\n\n";
            $message .= "Link này sẽ hết hạn sau 1 giờ.\n\n";
            $message .= "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.\n\n";
            $message .= "Trân trọng,\nCLB Học sinh Tình nguyện";
            
            mail($to, $subject, $message);
            
            // Ghi log
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $user['TenDangNhap'],
                'Yêu cầu reset password',
                'Thành công'
            );
            
            return ['success' => true, 'message' => 'Vui lòng kiểm tra email để đặt lại mật khẩu'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function resetPassword($token, $password) {
        // Kiểm tra token hợp lệ và chưa hết hạn
        $sql = "SELECT Id, TenDangNhap FROM nguoidung 
                WHERE reset_token = ? AND reset_expires > NOW() AND TranThai = 1 
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!$user) {
            return ['success' => false, 'message' => 'Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn'];
        }
        
        // Hash mật khẩu mới
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Cập nhật mật khẩu và xóa token
        $sql = "UPDATE nguoidung 
                SET MatKhauHash = ?, reset_token = NULL, reset_expires = NULL 
                WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $user['Id']);
        
        if ($stmt->execute()) {
            // Ghi log
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $user['TenDangNhap'],
                'Reset password',
                'Thành công'
            );
            
            return ['success' => true, 'message' => 'Đặt lại mật khẩu thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function changePassword($user_id, $current_password, $new_password) {
        // Kiểm tra mật khẩu hiện tại
        $sql = "SELECT MatKhauHash, TenDangNhap FROM nguoidung WHERE Id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        
        if (!password_verify($current_password, $user['MatKhauHash'])) {
            return ['success' => false, 'message' => 'Mật khẩu hiện tại không chính xác'];
        }
        
        // Hash mật khẩu mới
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Cập nhật mật khẩu
        $sql = "UPDATE nguoidung SET MatKhauHash = ? WHERE Id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $user_id);
        
        if ($stmt->execute()) {
            // Ghi log
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $user['TenDangNhap'],
                'Đổi mật khẩu',
                'Thành công'
            );
            
            return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
    
    public function updateProfile($user_id, $data) {
        // Validate dữ liệu
        $required_fields = ['fullname', 'email'];
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'];
            }
        }
        
        // Kiểm tra email đã tồn tại
        $sql = "SELECT Id FROM nguoidung WHERE Email = ? AND Id != ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $data['email'], $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email đã tồn tại'];
        }
        
        // Cập nhật thông tin
        $sql = "UPDATE nguoidung 
                SET HoTen = ?, Email = ?, GioiTinh = ?, NgaySinh = ? 
                WHERE Id = ?";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssisi", 
            $data['fullname'],
            $data['email'],
            $data['gender'],
            $data['birthday'],
            $user_id
        );
        
        if ($stmt->execute()) {
            // Cập nhật session
            $_SESSION['user']['fullname'] = $data['fullname'];
            $_SESSION['user']['email'] = $data['email'];
            
            // Ghi log
            logActivity(
                $_SERVER['REMOTE_ADDR'],
                $_SESSION['user']['username'],
                'Cập nhật thông tin cá nhân',
                'Thành công'
            );
            
            return ['success' => true, 'message' => 'Cập nhật thông tin thành công'];
        }
        
        return ['success' => false, 'message' => 'Có lỗi xảy ra, vui lòng thử lại'];
    }
} 