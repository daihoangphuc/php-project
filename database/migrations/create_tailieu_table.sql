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