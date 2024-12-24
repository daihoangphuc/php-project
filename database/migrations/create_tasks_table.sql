CREATE TABLE nhiemvu (
    Id INT PRIMARY KEY AUTO_INCREMENT,
    TieuDe VARCHAR(255) NOT NULL,
    MoTa TEXT,
    NgayBatDau DATE NOT NULL,
    NgayKetThuc DATE NOT NULL,
    TrangThai TINYINT DEFAULT 0, -- 0: Mới, 1: Đang làm, 2: Hoàn thành
    NguoiTaoId INT,
    NguoiThucHienId INT,
    ThoiGianTao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (NguoiTaoId) REFERENCES nguoidung(Id),
    FOREIGN KEY (NguoiThucHienId) REFERENCES nguoidung(Id)
); 