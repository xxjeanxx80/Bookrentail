-- Tạo bảng admin_tokens để lưu token cho Remember Me của admin
CREATE TABLE IF NOT EXISTS admin_tokens (
  id SERIAL PRIMARY KEY,
  admin_id INTEGER NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NOT NULL
);

-- Xóa các token đã hết hạn (có thể chạy định kỳ)
-- DELETE FROM admin_tokens WHERE expires_at < NOW();

