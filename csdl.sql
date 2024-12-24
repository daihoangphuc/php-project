-- Tạo database
CREATE DATABASE IF NOT EXISTS clb_hstv CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clb_hstv;

-- Bảng khoa trường
CREATE TABLE khoatruong (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TenKhoaTruong VARCHAR(255) NOT NULL,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng lớp học
CREATE TABLE lophoc (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TenLop VARCHAR(50) NOT NULL,
    KhoaTruongId INT,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (KhoaTruongId) REFERENCES khoatruong(Id)
);

-- Bảng chức vụ
CREATE TABLE chucvu (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TenChucVu VARCHAR(50) NOT NULL,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng vai trò
CREATE TABLE vaitro (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TenVaiTro VARCHAR(50) NOT NULL,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng người dùng
CREATE TABLE nguoidung (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    MaSinhVien VARCHAR(20) UNIQUE,
    TenDangNhap VARCHAR(50) UNIQUE NOT NULL,
    MatKhauHash VARCHAR(255) NOT NULL,
    HoTen VARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE NOT NULL,
    GioiTinh TINYINT DEFAULT 1, -- 1: Nam, 0: Nữ
    NgaySinh DATE,
    ChucVuId INT,
    LopHocId INT,
    reset_token VARCHAR(100),
    reset_expires DATETIME,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    TranThai TINYINT DEFAULT 1, -- 1: Hoạt động, 0: Khóa
    FOREIGN KEY (ChucVuId) REFERENCES chucvu(Id),
    FOREIGN KEY (LopHocId) REFERENCES lophoc(Id)
);
CREATE TABLE tailieu (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TenTaiLieu VARCHAR(255) NOT NULL,
    MoTa TEXT,
    DuongDan VARCHAR(255) NOT NULL,
    LoaiTaiLieu VARCHAR(50),
    NguoiTaoId INT,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (NguoiTaoId) REFERENCES nguoidung(Id)
);

CREATE TABLE phanquyentailieu (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TaiLieuId INT,
    VaiTroId INT,
    Quyen TINYINT DEFAULT 1, -- 1: Xem, 2: Tải xuống
    FOREIGN KEY (TaiLieuId) REFERENCES tailieu(Id),
    FOREIGN KEY (VaiTroId) REFERENCES vaitro(Id)
);
-- Bảng vai trò người dùng
CREATE TABLE vaitronguoidung (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    VaiTroId INT,
    NguoiDungId INT,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (VaiTroId) REFERENCES vaitro(Id),
    FOREIGN KEY (NguoiDungId) REFERENCES nguoidung(Id)
);

-- Bảng hoạt động
CREATE TABLE hoatdong (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TenHoatDong VARCHAR(255) NOT NULL,
    MoTa TEXT,
    NgayBatDau DATETIME NOT NULL,
    NgayKetThuc DATETIME NOT NULL,
    DiaDiem VARCHAR(255),
    ToaDo VARCHAR(50), -- Định dạng: "latitude,longitude"
    SoLuong INT DEFAULT 0,
    TrangThai TINYINT DEFAULT 1, -- 0: Đã hủy, 1: Đang diễn ra, 2: Đã kết thúc
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng đăng ký hoạt động
CREATE TABLE danhsachdangky (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    NguoiDungId INT,
    HoatDongId INT,
    TrangThai TINYINT DEFAULT 1, -- 1: Đã đăng ký, 0: Đã hủy
    ThoiGianDangKy DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (NguoiDungId) REFERENCES nguoidung(Id),
    FOREIGN KEY (HoatDongId) REFERENCES hoatdong(Id)
);

-- Bảng tham gia hoạt động (điểm danh)
CREATE TABLE danhsachthamgia (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    NguoiDungId INT,
    HoatDongId INT,
    DiemDanhLuc DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (NguoiDungId) REFERENCES nguoidung(Id),
    FOREIGN KEY (HoatDongId) REFERENCES hoatdong(Id)
);

-- Bảng nhiệm vụ
CREATE TABLE nhiemvu (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TenNhiemVu VARCHAR(255) NOT NULL,
    MoTa TEXT,
    NgayBatDau DATETIME NOT NULL,
    NgayKetThuc DATETIME NOT NULL,
    TrangThai TINYINT DEFAULT 0, -- 0: Chưa bắt đầu, 1: Đang thực hiện, 2: Đã hoàn thành
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Bảng phân công nhiệm vụ
CREATE TABLE phancongnhiemvu (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    NguoiDungId INT,
    NhiemVuId INT,
    NguoiPhanCong VARCHAR(50) NOT NULL,
    NgayPhanCong DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (NguoiDungId) REFERENCES nguoidung(Id),
    FOREIGN KEY (NhiemVuId) REFERENCES nhiemvu(Id)
);

-- Bảng tin tức
CREATE TABLE tintuc (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TieuDe VARCHAR(255) NOT NULL,
    NoiDung TEXT,
    FileDinhKem VARCHAR(255),
    NguoiTaoId INT,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (NguoiTaoId) REFERENCES nguoidung(Id)
);

-- Bảng tài chính
CREATE TABLE taichinh (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    LoaiGiaoDich TINYINT NOT NULL, -- 1: Thu, 0: Chi
    SoTien BIGINT NOT NULL,
    MoTa TEXT,
    NgayGiaoDich DATETIME NOT NULL,
    NguoiDungId INT,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (NguoiDungId) REFERENCES nguoidung(Id)
);

-- Bảng nhật ký hoạt động
CREATE TABLE nhatkyhoatdong (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    IP VARCHAR(45),
    NguoiDung VARCHAR(50),
    HanhDong VARCHAR(255),
    KetQua VARCHAR(50),
    ChiTiet TEXT,
    NgayTao DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Thêm dữ liệu mẫu
INSERT INTO vaitro (TenVaiTro) VALUES 
('admin'),
('member');

INSERT INTO chucvu (TenChucVu) VALUES 
('Chủ nhiệm'),
('Phó chủ nhiệm'),
('Thư ký'),
('Thành viên');

INSERT INTO khoatruong (TenKhoaTruong) VALUES 
('Khoa Công nghệ Thông tin'),
('Khoa Kinh tế'),
('Khoa Ngoại ngữ');

INSERT INTO lophoc (TenLop, KhoaTruongId) VALUES 
('CNTT1', 1),
('CNTT2', 1),
('KT1', 2),
('KT2', 2),
('NN1', 3),
('NN2', 3);

-- Thêm tài khoản admin mặc định
-- Mật khẩu: admin123 (đã hash)
INSERT INTO nguoidung (MaSinhVien, TenDangNhap, MatKhauHash, HoTen, Email, ChucVuId, LopHocId, TranThai) VALUES 
('ADMIN', 'admin', '$2y$10$8Uj.2qFp1P6h2bZMLxZIyOO9v4EX.rz5h3rqxjrz91XBtCGqGQ9Uy', 'Administrator', 'admin@example.com', 1, 1, 1);

INSERT INTO vaitronguoidung (VaiTroId, NguoiDungId) VALUES 
(1, 1); -- Gán quyền admin cho tài khoản admin
