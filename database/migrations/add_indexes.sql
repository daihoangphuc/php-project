-- Tối ưu truy vấn thống kê
ALTER TABLE nguoidung ADD INDEX idx_lophoc (LopHocId);
ALTER TABLE nguoidung ADD INDEX idx_chucvu (ChucVuId);
ALTER TABLE danhsachthamgia ADD INDEX idx_hoatdong_nguoidung (HoatDongId, NguoiDungId);
ALTER TABLE phancongnhiemvu ADD INDEX idx_nhiemvu_nguoidung (NhiemVuId, NguoiDungId);
ALTER TABLE taichinh ADD INDEX idx_ngaygiaodich (NgayGiaoDich); 