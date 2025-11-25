# 🍪 Remember Me - Simple Educational Implementation

## 🎯 Mục Tiêu
Thêm nút "Remember Me" vào form login cho dự án giáo dục. Giữ đơn giản, dễ hiểu cho học sinh.

## 📋 Cách Hoạt Động

### 🔧 Cookie System Hiện Có
```php
// Đã có sẵn trong connection.php
if (isset($_COOKIE['user_auth'])) {
    $cookie_data = base64_decode($_COOKIE['user_auth']);
    $cookie_parts = explode('|', $cookie_data);
    // Auto-login logic...
}
```

### ✅ Thay Đổi Đã Thêm
1. **Checkbox vào login form**
2. **Cookie chỉ set khi checkbox được chọn**
3. **Giữ nguyên logic auto-login hiện có**

## 📝 Code Thêm Vào

### Admin Login (login.php)
```php
// Sau khi login thành công
if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
    $cookie_name = 'admin_auth';
    $cookie_value = base64_encode($row['id'] . '|' . $email . '|' . md5($email . $password));
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
}
```

### Customer Login (SignIn.php)
```php
// Sau khi login thành công
if (isset($_POST['remember_me']) && $_POST['remember_me'] == '1') {
    $cookie_name = 'user_auth';
    $cookie_value = base64_encode($row['id'] . '|' . $email . '|' . md5($email . $password));
    setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
}
```

### HTML Checkbox
```html
<div class="form-check">
    <input class="form-check-input" type="checkbox" name="remember_me" value="1" id="rememberMe">
    <label class="form-check-label" for="rememberMe">
        Remember me for 30 days
    </label>
</div>
```

## 🧪 Cách Test

1. **Login với "Remember Me" checked**
2. **Đóng trình duyệt hoàn toàn**
3. **Mở lại trình duyệt, vào trang admin/customer**
4. **Should auto-login thành công**

## ⚠️ Lưu Ý Bảo Mật

**Đây là implementation đơn giản cho mục đích giáo dục:**
- ✅ Dễ hiểu cho học sinh
- ✅ Hoạt động tốt cho demo
- ⚠️ Không an toàn cho production
- ⚠️ Cookie có thể bị edit để impersonate user

**Nếu cần security cao hơn, xem file `AUTHENTICATION_GUIDE.md`**

## 📚 Giá Trị Giáo Dục

1. **Session vs Cookie:** Hiểu cách lưu trạng thái login
2. **Cookie Management:** Set/get cookies trong PHP
3. **Auto-login Logic:** Kiểm tra cookie khi session hết
4. **Form Handling:** Xử lý checkbox trong PHP form

## 🎓 Điểm Học Tập

- **`setcookie()` function:** Set cookie với expiration
- **`base64_encode()`:** Encode data cho cookie
- **`$_COOKIE` superglobal:** Đọc cookie value
- **Conditional logic:** Chỉ set cookie khi user chọn

## ✅ Hoàn Thành

- [x] Thêm checkbox vào admin login form
- [x] Thêm checkbox vào customer login form  
- [x] Logic set cookie khi checkbox checked
- [x] Giữ nguyên auto-login hiện có
- [x] Documentation đơn giản

**Kết quả:** User có thể chọn "Remember Me" và được auto-login khi quay lại trang sau 30 ngày. 🎯

---
*Perfect cho student project - đơn giản, dễ hiểu, hoạt động tốt!* 📚
