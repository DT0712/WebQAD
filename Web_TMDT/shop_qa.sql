-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 02, 2025 lúc 10:22 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `shop_qa`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_san_pham`
--

CREATE TABLE `chi_tiet_san_pham` (
  `id` int(11) NOT NULL,
  `id_san_pham` int(11) NOT NULL,
  `mau_sac` varchar(50) DEFAULT NULL,
  `kich_co` varchar(10) DEFAULT NULL,
  `so_luong` int(11) NOT NULL,
  `gia` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id` int(11) NOT NULL,
  `ten_danh_muc` varchar(100) NOT NULL,
  `id_cha` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`id`, `ten_danh_muc`, `id_cha`) VALUES
(1, 'Thời trang nam', NULL),
(2, 'Thời trang nữ', NULL),
(3, 'Trẻ em', NULL),
(4, 'Áo thun', 1),
(5, 'Áo vest', 1),
(6, 'Áo sơ mi', 1),
(7, 'Hoodie', 1),
(8, 'Quần jeans nam', 1),
(9, 'Quần tây nam', 1),
(10, 'Áo khoác nam', 1),
(11, 'Đồ thể thao nam', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id` int(11) NOT NULL,
  `ho_ten` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id`, `ho_ten`, `email`, `so_dien_thoai`, `mat_khau`) VALUES
(1, 'Trương Đức Duy', 'ducduy102938@gmail.com', NULL, '0712');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham`
--

CREATE TABLE `san_pham` (
  `id_san_pham` int(11) NOT NULL,
  `ten_san_pham` varchar(255) DEFAULT NULL,
  `id_danh_muc` int(11) DEFAULT NULL,
  `gia` decimal(10,2) DEFAULT NULL,
  `mo_ta` text DEFAULT NULL,
  `link_anh` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham`
--

INSERT INTO `san_pham` (`id_san_pham`, `ten_san_pham`, `id_danh_muc`, `gia`, `mo_ta`, `link_anh`) VALUES
(1, 'Áo thun cotton basic', 4, 150000.00, 'Áo thun cotton co giãn 4 chiều', 'ao_thun_cotton.jpg'),
(2, 'Áo thun thể thao', 4, 200000.00, 'Áo thun thể thao thoát mồ hôi nhanh', 'ao_thun_thethao.jpg'),
(3, 'Áo thun oversize', 4, 180000.00, 'Áo thun oversize phong cách Hàn Quốc', 'ao_thun_oversize.jpg'),
(4, 'Áo vest công sở', 5, 1200000.00, 'Áo vest nam lịch lãm', 'ao_vest_congso.jpg'),
(5, 'Áo vest dự tiệc', 5, 1500000.00, 'Áo vest dự tiệc cao cấp', 'ao_vest_dutiec.jpg'),
(6, 'Áo vest slimfit', 5, 1350000.00, 'Áo vest slimfit trẻ trung', 'ao_vest_slimfit.jpg'),
(7, 'Áo sơ mi caro', 6, 300000.00, 'Áo sơ mi caro tay dài', 'ao_somi_caro.jpg'),
(8, 'Áo sơ mi trắng basic', 6, 250000.00, 'Áo sơ mi trắng trơn', 'ao_somi_trang.jpg'),
(9, 'Áo sơ mi ngắn tay', 6, 280000.00, 'Áo sơ mi ngắn tay thoải mái', 'ao_somi_ngantay.jpg'),
(10, 'Hoodie oversize', 7, 450000.00, 'Áo hoodie nỉ bông form rộng', 'hoodie_oversize.jpg'),
(11, 'Hoodie có nón', 7, 480000.00, 'Áo hoodie có nón dây rút', 'hoodie_conon.jpg'),
(12, 'Hoodie zipper', 7, 500000.00, 'Áo hoodie kéo khóa', 'hoodie_zipper.jpg'),
(13, 'Quần jeans slimfit', 8, 400000.00, 'Quần jeans slimfit ôm vừa', 'jeans_slimfit.jpg'),
(14, 'Quần jeans rách gối', 8, 450000.00, 'Quần jeans rách gối cá tính', 'jeans_rachgoi.jpg'),
(15, 'Quần jeans ống rộng', 8, 420000.00, 'Quần jeans ống rộng phong cách', 'jeans_ongrong.jpg'),
(16, 'Quần tây công sở', 9, 500000.00, 'Quần tây nam form đứng', 'quantay_congso.jpg'),
(17, 'Quần tây slimfit', 9, 520000.00, 'Quần tây slimfit trẻ trung', 'quantay_slimfit.jpg'),
(18, 'Quần tây kaki', 9, 480000.00, 'Quần tây kaki co giãn', 'quantay_kaki.jpg'),
(19, 'Áo khoác bomber', 10, 650000.00, 'Áo khoác bomber Hàn Quốc', 'aokhoac_bomber.jpg'),
(20, 'Áo khoác da', 10, 950000.00, 'Áo khoác da cao cấp', 'aokhoac_da.jpg'),
(21, 'Áo khoác gió', 10, 550000.00, 'Áo khoác gió chống nước', 'aokhoac_gio.jpg'),
(22, 'Bộ đồ thể thao Adidas', 11, 900000.00, 'Bộ đồ thể thao Adidas co giãn', 'do_adidas.jpg'),
(23, 'Bộ đồ thể thao Nike', 11, 850000.00, 'Bộ đồ thể thao Nike thoải mái', 'do_nike.jpg'),
(24, 'Bộ đồ thể thao Puma', 11, 800000.00, 'Bộ đồ thể thao Puma phong cách', 'do_puma.jpg');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `chi_tiet_san_pham`
--
ALTER TABLE `chi_tiet_san_pham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_san_pham` (`id_san_pham`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cha` (`id_cha`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Chỉ mục cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`id_san_pham`),
  ADD KEY `id_danh_muc` (`id_danh_muc`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chi_tiet_san_pham`
--
ALTER TABLE `chi_tiet_san_pham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `id_san_pham` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chi_tiet_san_pham`
--
ALTER TABLE `chi_tiet_san_pham`
  ADD CONSTRAINT `chi_tiet_san_pham_ibfk_1` FOREIGN KEY (`id_san_pham`) REFERENCES `san_pham` (`id_san_pham`);

--
-- Các ràng buộc cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD CONSTRAINT `danh_muc_ibfk_1` FOREIGN KEY (`id_cha`) REFERENCES `danh_muc` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Các ràng buộc cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `san_pham_ibfk_1` FOREIGN KEY (`id_danh_muc`) REFERENCES `danh_muc` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
