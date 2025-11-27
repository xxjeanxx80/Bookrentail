-- Tạo bảng admin_tokens để lưu token cho Remember Me của admin
CREATE TABLE IF NOT EXISTS `admin_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `admin_id` (`admin_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Xóa các token đã hết hạn (có thể chạy định kỳ)
-- DELETE FROM admin_tokens WHERE expires_at < NOW();

