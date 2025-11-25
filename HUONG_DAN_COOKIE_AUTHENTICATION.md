# 🍪 HƯỚNG DẪN COOKIE AUTHENTICATION (REMEMBER ME)

## 📋 Tổng Quan

Tính năng **Remember Me** cho phép user đăng nhập một lần và tự động đăng nhập lại trong 30 ngày mà không cần nhập lại email/password.

## 🚀 Cài Đặt

### Bước 1: Tạo bảng trong database

Chạy file SQL sau trong phpMyAdmin hoặc MySQL:

```sql
-- File: Database/add_user_tokens_table.sql
CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Hoặc import file: `Database/add_user_tokens_table.sql`

## ✨ Tính Năng

### 1. **Remember Me Checkbox**
- Thêm checkbox "Remember Me" vào form đăng nhập
- User có thể chọn để lưu đăng nhập

### 2. **Tự Động Đăng Nhập**
- Khi user quay lại website, hệ thống tự động kiểm tra cookie
- Nếu có token hợp lệ → Tự động đăng nhập
- Không cần nhập lại email/password

### 3. **Bảo Mật**
- Token được lưu trong database với thời gian hết hạn
- Cookie có flag `HttpOnly` để chống XSS
- Token tự động hết hạn sau 30 ngày
- Xóa token khi logout

## 📁 Các File Đã Thay Đổi

### 1. `includes/function.php`
Thêm 5 functions mới:

- **`generateToken()`**: Tạo token ngẫu nhiên 64 ký tự
- **`saveRememberToken($con, $userId)`**: Lưu token vào cookie và database
- **`deleteRememberToken($con, $token)`**: Xóa token khỏi cookie và database
- **`checkRememberToken($con)`**: Kiểm tra token và tự động đăng nhập
- **`deleteAllUserTokens($con, $userId)`**: Xóa tất cả token của user

### 2. `includes/header.php`
- Tự động gọi `checkRememberToken()` khi load trang
- Chỉ check nếu chưa có session

### 3. `pages/SignIn.php`
- Thêm checkbox "Remember Me"
- Lưu token khi user chọn Remember Me

### 4. `pages/logout.php`
- Xóa token khi logout

### 5. `Database/add_user_tokens_table.sql`
- File SQL để tạo bảng `user_tokens`

## 🔄 Flow Hoạt Động

### Khi Đăng Nhập với Remember Me:

```
1. User nhập email/password và tick "Remember Me"
2. Hệ thống xác thực thành công
3. Tạo token ngẫu nhiên (64 ký tự)
4. Lưu token vào:
   - Cookie: remember_token (30 ngày)
   - Database: user_tokens table
5. Set session như bình thường
```

### Khi User Quay Lại Website:

```
1. User truy cập bất kỳ trang nào
2. header.php được load
3. Kiểm tra: Có session chưa?
   - Nếu có → Bỏ qua
   - Nếu chưa → Kiểm tra cookie
4. Nếu có cookie remember_token:
   - Tìm token trong database
   - Kiểm tra token còn hạn (expires_at > NOW())
   - Nếu hợp lệ → Tự động set session
   - Nếu không hợp lệ → Xóa cookie
```

### Khi Logout:

```
1. Xóa token khỏi database
2. Xóa cookie
3. Xóa session
```

## 🛠️ Cách Sử Dụng

### Cho User:
1. Đăng nhập với email/password
2. Tick vào checkbox "Remember Me"
3. Click "Login"
4. Lần sau quay lại website sẽ tự động đăng nhập (trong 30 ngày)

### Cho Developer:

#### Xóa tất cả token của user (khi đổi password):
```php
deleteAllUserTokens($con, $userId);
```

#### Kiểm tra token thủ công:
```php
if (checkRememberToken($con)) {
    echo "Đã tự động đăng nhập";
}
```

## 🔒 Bảo Mật

### Các Biện Pháp:
1. ✅ Token ngẫu nhiên 64 ký tự (khó đoán)
2. ✅ Token lưu trong database với thời gian hết hạn
3. ✅ Cookie có flag `HttpOnly` (chống XSS)
4. ✅ Cookie có flag `Secure` (chỉ gửi qua HTTPS - nếu có)
5. ✅ Token tự động hết hạn sau 30 ngày
6. ✅ Xóa token khi logout
7. ✅ Validate token trước khi đăng nhập

### Lưu Ý:
- Token được lưu trong database nên có thể xóa tất cả token của user nếu cần
- Có thể giảm thời gian hết hạn (hiện tại 30 ngày) trong function `saveRememberToken()`
- Nên dọn dẹp token hết hạn định kỳ:
  ```sql
  DELETE FROM user_tokens WHERE expires_at < NOW();
  ```

## 📊 Cấu Trúc Bảng `user_tokens`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID (Primary Key, Auto Increment) |
| user_id | int(11) | ID của user (Foreign Key → users.id) |
| token | varchar(64) | Token (Unique) |
| expires_at | datetime | Thời gian hết hạn |
| created_at | datetime | Thời gian tạo |

## 🧪 Testing

### Test Case 1: Đăng nhập với Remember Me
1. Đăng nhập và tick "Remember Me"
2. Logout
3. Đóng trình duyệt
4. Mở lại website
5. ✅ Kết quả: Tự động đăng nhập

### Test Case 2: Đăng nhập không Remember Me
1. Đăng nhập KHÔNG tick "Remember Me"
2. Đóng trình duyệt
3. Mở lại website
4. ✅ Kết quả: Phải đăng nhập lại

### Test Case 3: Logout
1. Đăng nhập với Remember Me
2. Logout
3. Đóng trình duyệt
4. Mở lại website
5. ✅ Kết quả: Không tự động đăng nhập

### Test Case 4: Token hết hạn
1. Đăng nhập với Remember Me
2. Sửa `expires_at` trong database về quá khứ
3. Đóng trình duyệt
4. Mở lại website
5. ✅ Kết quả: Không tự động đăng nhập, cookie bị xóa

## 📝 Code Example

### Tạo token mới:
```php
$token = generateToken(); // Tạo token 64 ký tự
```

### Lưu token:
```php
if ($rememberMe) {
    saveRememberToken($con, $userId);
}
```

### Kiểm tra token:
```php
if (checkRememberToken($con)) {
    // Đã tự động đăng nhập
}
```

### Xóa token:
```php
deleteRememberToken($con, $token);
```

### Xóa tất cả token của user:
```php
deleteAllUserTokens($con, $userId);
```

## ⚙️ Tùy Chỉnh

### Thay đổi thời gian hết hạn (mặc định 30 ngày):

Sửa trong `includes/function.php`:

```php
// Thay đổi từ 30 ngày thành 7 ngày
$expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
setcookie('remember_token', $token, time() + (7 * 24 * 60 * 60), '/', '', false, true);
```

### Thay đổi tên cookie:

Sửa tất cả chỗ `'remember_token'` thành tên khác (ví dụ: `'book_rental_token'`)

## 🐛 Troubleshooting

### Vấn đề: Không tự động đăng nhập
**Giải pháp:**
1. Kiểm tra bảng `user_tokens` đã được tạo chưa
2. Kiểm tra cookie có được lưu không (F12 → Application → Cookies)
3. Kiểm tra token trong database có hợp lệ không
4. Kiểm tra `expires_at` còn trong tương lai không

### Vấn đề: Cookie không được lưu
**Giải pháp:**
1. Kiểm tra trình duyệt có chặn cookie không
2. Kiểm tra domain/path của cookie
3. Kiểm tra `setcookie()` có được gọi trước khi output HTML không

### Vấn đề: Token không được xóa khi logout
**Giải pháp:**
1. Kiểm tra `logout.php` có gọi `deleteRememberToken()` không
2. Kiểm tra cookie có tồn tại trước khi xóa không

## 📚 Tài Liệu Tham Khảo

- File hướng dẫn customer: `HUONG_DAN_DOC_HIEU_CODE_CUSTOMER.md`
- File SQL: `Database/add_user_tokens_table.sql`
- Functions: `includes/function.php`

---

**Tính năng này đơn giản, nhẹ, dễ hiểu và dễ bảo trì! 🚀**

