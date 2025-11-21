-- Tạo bảng user_tokens để lưu token cho Remember Me
CREATE TABLE IF NOT EXISTS user_tokens (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  token VARCHAR(64) NOT NULL UNIQUE,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NOT NULL
);

-- Xóa các token đã hết hạn (có thể chạy định kỳ)
-- DELETE FROM user_tokens WHERE expires_at < NOW();

