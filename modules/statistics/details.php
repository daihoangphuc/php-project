<?php
// Thống kê thành viên theo lớp
$sqlMembersByClass = "SELECT l.TenLop, COUNT(n.Id) as SoLuong
    FROM lophoc l
    LEFT JOIN nguoidung n ON l.Id = n.LopHocId
    GROUP BY l.Id";

// Thống kê hoạt động theo trạng thái và thời gian
$sqlActivityStats = "SELECT 
    COUNT(*) as TongHoatDong,
    SUM(CASE WHEN MONTH(NgayBatDau) = MONTH(NOW()) THEN 1 ELSE 0 END) as HoatDongThangNay,
    COUNT(DISTINCT dst.NguoiDungId) as TongNguoiThamGia,
    (COUNT(DISTINCT dst.NguoiDungId) * 100.0 / COUNT(DISTINCT ddk.NguoiDungId)) as TyLeThamGia
    FROM hoatdong h
    LEFT JOIN danhsachthamgia dst ON h.Id = dst.HoatDongId
    LEFT JOIN danhsachdangky ddk ON h.Id = ddk.HoatDongId";

// Thống kê nhiệm vụ theo người thực hiện
$sqlTasksByUser = "SELECT 
    nd.HoTen,
    COUNT(pcnv.Id) as TongNhiemVu,
    SUM(CASE WHEN nv.TrangThai = 2 THEN 1 ELSE 0 END) as NhiemVuHoanThanh
    FROM nguoidung nd
    LEFT JOIN phancongnhiemvu pcnv ON nd.Id = pcnv.NguoiDungId
    LEFT JOIN nhiemvu nv ON pcnv.NhiemVuId = nv.Id
    GROUP BY nd.Id"; 