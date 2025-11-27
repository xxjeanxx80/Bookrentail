# 📖 HƯỚNG DẪN ĐỌC HIỂU CODE - PHẦN ADMIN

## 📋 MỤC LỤC
1. [Tổng Quan](#tổng-quan)
2. [Cấu Trúc File Admin](#cấu-trúc-file-admin)
3. [Session Management](#session-management)
4. [Cấu Trúc Chuẩn Của File Admin](#cấu-trúc-chuẩn-của-file-admin)
5. [Chi Tiết Login](#chi-tiết-login)
6. [Chi Tiết Logout](#chi-tiết-logout)
7. [Chi Tiết TopNav](#chi-tiết-topnav)
8. [Chi Tiết Users](#chi-tiết-users)
9. [Chi Tiết Orders](#chi-tiết-orders)
10. [Chi Tiết Categories](#chi-tiết-categories)
11. [Chi Tiết ManageCategories](#chi-tiết-managecategories)
12. [Chi Tiết Books](#chi-tiết-books)
13. [Chi Tiết ManageBooks](#chi-tiết-managebooks)

---

## 🎯 TỔNG QUAN

Hệ thống Admin Panel cho phép quản trị viên quản lý toàn bộ hệ thống Book Rental:

- ✅ Đăng nhập vào hệ thống quản trị (với Remember Me)
- ✅ Quản lý sách (thêm, sửa, xóa, thay đổi trạng thái)
- ✅ Quản lý danh mục sách
- ✅ Quản lý đơn hàng
- ✅ Quản lý người dùng

**Công nghệ sử dụng:**
- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5, MDB (Material Design for Bootstrap)

**Tính năng bảo mật:**
- ✅ Password được hash bằng MD5 (giống user)
- ✅ Remember Me với cookie và database token
- ✅ Session management với kiểm tra token tự động
- ✅ Đơn giản hóa cho mục đích demo/giáo dục: chỉ dùng `trim()` và type casting

### Cài Đặt Database

Để sử dụng đầy đủ tính năng admin, cần chạy các file SQL sau:

1. **Tạo bảng admin_tokens:**
   ```sql
   -- Chạy file: Database/add_admin_tokens_table.sql
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
   ```

2. **Cập nhật password admin thành hash:**
   ```sql
   -- Chạy file: Database/update_admin_password_hash.sql
   -- Password '123' được hash thành MD5: 202cb962ac59075b964b07152d234b70
   UPDATE `admin` SET `password` = '202cb962ac59075b964b07152d234b70' 
   WHERE `email` = 'tqhien614@gmail.com';
   
   UPDATE `admin` SET `password` = '202cb962ac59075b964b07152d234b70' 
   WHERE `email` = 'tienduc@gmail.com';
   ```

**Lưu ý:**
- ✅ Password admin mặc định: `123` (đã hash thành MD5)
- ✅ Email admin: `tqhien614@gmail.com` hoặc `tienduc@gmail.com`
- ✅ Sau khi chạy SQL, admin có thể đăng nhập với password `123`

---

## 📁 CẤU TRÚC FILE ADMIN

### Thư Mục Admin
```
Book-rental/
└── Admin/
    ├── login.php              # Đăng nhập admin ⭐ (Đã giải thích)
    ├── logout.php             # Đăng xuất admin ⭐ (Đã giải thích)
    ├── topNav.php             # Header/Navigation chung cho admin ⭐ (Đã giải thích)
    ├── categories.php         # Quản lý danh mục sách ⭐ (Đã giải thích)
    ├── manageCategories.php   # Thêm/Sửa danh mục ⭐ (Đã giải thích)
    ├── books.php              # Danh sách sách ⭐ (Đã giải thích)
    ├── manageBooks.php        # Thêm/Sửa sách ⭐ (Đã giải thích)
    ├── orders.php             # Quản lý đơn hàng ⭐ (Đã giải thích)
    ├── users.php              # Quản lý người dùng ⭐ (Đã giải thích)
    ├── css/
    │   └── admin.css          # CSS riêng cho admin
    └── js/
        └── admin.js           # JavaScript riêng cho admin
```

**Lưu ý:** Tất cả các file PHP chính đã được giải thích chi tiết trong tài liệu này.

---

## 🔐 SESSION MANAGEMENT

### Các Session Variable

#### `$_SESSION['ADMIN_LOGIN']`
- **Giá trị:** `'yes'` (string)
- **Set khi:** Đăng nhập thành công hoặc Remember Me token hợp lệ
- **Dùng để:** Kiểm tra admin đã đăng nhập chưa
- **Unset khi:** Logout
- **Kiểm tra trong:** Tất cả các trang admin (trừ login.php)

#### `$_SESSION['ADMIN_email']`
- **Giá trị:** Email của admin (string)
- **Set khi:** Đăng nhập thành công hoặc Remember Me token hợp lệ
- **Dùng để:** Hiển thị email admin trên navigation bar
- **Unset khi:** Logout
- **Lưu ý:** ✅ Đã thống nhất dùng `ADMIN_email` (chữ thường) trong toàn bộ hệ thống

### Cookie và Remember Me

#### Cookie `admin_remember_token`
- **Tên:** `admin_remember_token`
- **Tạo khi:** Admin chọn "Remember Me" khi đăng nhập
- **Thời hạn:** 30 ngày
- **Mục đích:** Tự động đăng nhập lại khi có cookie hợp lệ
- **Xóa khi:** Logout hoặc token hết hạn

#### Cơ Chế Remember Me

1. **Khi đăng nhập với Remember Me:**
   - Tạo token ngẫu nhiên (64 ký tự hex)
   - Lưu token vào bảng `admin_tokens` trong database
   - Lưu token vào cookie `admin_remember_token`
   - Token hết hạn sau 30 ngày

2. **Khi truy cập trang admin:**
   - Kiểm tra session `ADMIN_LOGIN` trước
   - Nếu chưa có session, kiểm tra cookie `admin_remember_token`
   - Nếu cookie hợp lệ và chưa hết hạn → Tự động đăng nhập
   - Tạo session `ADMIN_LOGIN` và `ADMIN_email`

3. **Khi logout:**
   - Xóa cookie `admin_remember_token`
   - Xóa token khỏi database
   - Xóa session variables

### Cơ Chế Bảo Vệ Trang Admin

Tất cả các trang admin (trừ `login.php`) đều được bảo vệ bằng cách kiểm tra session và Remember Me token:

**Cách 1: Kiểm tra Remember Me token và session trong file**
```php
// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] != 'yes') {
    header('Location: login.php');
    exit;
}
```

**Cách 2: Kiểm tra trong topNav.php**
```php
// topNav.php được require ở đầu mỗi trang admin
// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] != 'yes') {
    header('location:login.php');
    die();
}
```

**Lưu ý:**
- ✅ Nếu chưa có session → Kiểm tra Remember Me token trước
- ✅ Nếu có cookie hợp lệ → Tự động đăng nhập
- ✅ Nếu không có session và cookie → Redirect về `login.php`
- ✅ Session được kiểm tra ở nhiều nơi để đảm bảo an toàn

---

## 🔑 CHI TIẾT LOGIN

### File: `Admin/login.php`

**Mục đích:** Xử lý đăng nhập cho quản trị viên

### Flow Hoạt Động

```
[Admin truy cập login.php]
    ↓
[Kiểm tra Remember Me token nếu chưa có session]
    ├─→ Có token hợp lệ → Tự động đăng nhập → Redirect đến categories.php
    └─→ Không có token → Hiển thị form đăng nhập
    ↓
[Admin nhập email và password]
    ↓
[Chọn Remember Me (nếu muốn)]
    ↓
[Submit form (POST)]
    ↓
[Hash password bằng MD5]
    ↓
[Kiểm tra email và password hash trong database]
    ↓
[Nếu đúng → Set session và lưu token (nếu chọn Remember Me)]
    ├─→ Redirect đến categories.php
[Nếu sai → Hiển thị lỗi "Invalid Username/Password"]
```

### Code Chi Tiết

#### 1. Include các file cần thiết

```php
require(__DIR__ . '/../config/connection.php');
require(__DIR__ . '/../includes/function.php');
```

**Giải thích:**
- `connection.php`: Kết nối database và khởi động session
- `function.php`: Các function hỗ trợ (checkAdminRememberToken, saveAdminRememberToken, ...)

#### 2. Kiểm tra Remember Me token

```php
// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}
```

**Giải thích:**
- Kiểm tra xem có session `ADMIN_LOGIN` chưa
- Nếu chưa có → Gọi `checkAdminRememberToken()` để kiểm tra cookie
- Nếu có cookie hợp lệ → Tự động đăng nhập và tạo session

#### 3. Redirect nếu đã đăng nhập

```php
// Nếu đã đăng nhập, chuyển đến categories.php
if (isset($_SESSION['ADMIN_LOGIN'])) {
    header('location:categories.php');
    die();
}
```

**Giải thích:**
- Nếu đã có session (từ Remember Me hoặc đăng nhập trước đó)
- Tự động redirect đến `categories.php`
- Không cần hiển thị form đăng nhập

#### 4. Khởi tạo biến

```php
$msg = '';
```

**Giải thích:**
- `$msg`: Lưu thông báo lỗi (nếu có)

#### 5. Xử lý form đăng nhập

```php
if (isset($_POST['submit'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $rememberMe = isset($_POST['remember_me']) ? true : false;
    
    // Hash password bằng MD5 (giống user)
    $passwordHash = md5($password);
    
    $sql = "SELECT * FROM admin WHERE email='$email' AND password='$passwordHash'";
    $res = mysqli_query($con, $sql);
    
    if ($res && mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_assoc($res);
        $_SESSION['ADMIN_LOGIN'] = 'yes';
        $_SESSION['ADMIN_email'] = $email;
        
        // Nếu chọn Remember Me, lưu token
        if ($rememberMe) {
            saveAdminRememberToken($con, $row['id']);
        }
        
        header('location:categories.php');
        die();
    } else {
        $msg = "Invalid Username/Password";
    }
}
```

**Giải thích từng bước:**

1. **Kiểm tra form đã submit:**
   ```php
   if (isset($_POST['submit']))
   ```
   - Kiểm tra nút "Login" đã được click chưa

2. **Lấy và làm sạch dữ liệu:**
   ```php
   $email = trim($_POST['email']);
   $password = trim($_POST['password']);
   $rememberMe = isset($_POST['remember_me']) ? true : false;
   ```
   - Lấy email và password từ form
   - Lấy trạng thái Remember Me checkbox
   - Sử dụng `trim()` để loại bỏ khoảng trắng đầu/cuối
   - **Lưu ý:** Đơn giản hóa cho mục đích demo/giáo dục, không dùng SQL escape

3. **Hash password:**
   ```php
   $passwordHash = md5($password);
   ```
   - ✅ Hash password bằng MD5 (giống user)
   - Password trong database đã được hash, không lưu plain text

4. **Query database:**
   ```php
   $sql = "SELECT * FROM admin WHERE email='$email' AND password='$passwordHash'";
   $res = mysqli_query($con, $sql);
   ```
   - Tìm admin trong database với email và password hash khớp
   - Sử dụng `mysqli_fetch_assoc()` để lấy dữ liệu

5. **Nếu tìm thấy (đăng nhập thành công):**
   ```php
   if ($res && mysqli_num_rows($res) > 0) {
       $row = mysqli_fetch_assoc($res);
       $_SESSION['ADMIN_LOGIN'] = 'yes';
       $_SESSION['ADMIN_email'] = $email;
       
       // Nếu chọn Remember Me, lưu token
       if ($rememberMe) {
           saveAdminRememberToken($con, $row['id']);
       }
       
       header('location:categories.php');
       die();
   }
   ```
   - Set 2 session variables:
     - `$_SESSION['ADMIN_LOGIN'] = 'yes'`: Đánh dấu đã đăng nhập
     - `$_SESSION['ADMIN_email'] = $email`: Lưu email để hiển thị (chữ thường)
   - ✅ Nếu chọn Remember Me → Lưu token vào cookie và database
   - Redirect đến `categories.php` (trang quản lý danh mục)
   - `die()`: Dừng script để đảm bảo không có code nào chạy sau redirect

6. **Nếu không tìm thấy (đăng nhập thất bại):**
   ```php
   else {
       $msg = "Invalid Username/Password";
   }
   ```
   - Gán thông báo lỗi vào `$msg`
   - Thông báo này sẽ được hiển thị trên form

#### 6. Hiển thị form đăng nhập

```php
<form class="mx-1 mx-md-4" method="post">
    <div class="d-flex align-items-center mb-4">
        <i class="fas fa-envelope fa-lg me-3 fa-fw"></i>
        <div class="form-floating flex-fill">
            <input type="email" name="email" class="form-control" id="email"
                placeholder="name@example.com" required />
            <label for="email">Email address</label>
        </div>
    </div>
    <div class="d-flex align-items-center mb-1">
        <i class="fas fa-lock fa-lg me-3 fa-fw"></i>
        <div class="form-floating flex-fill">
            <input type="password" name="password" class="form-control" id="Password"
                placeholder="Password" required />
            <label for="Password">Password</label>
        </div>
    </div>
    <div class="d-flex align-items-center mb-3">
        <div class="form-check ms-5">
            <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me" value="1">
            <label class="form-check-label" for="remember_me">
                Remember Me
            </label>
        </div>
    </div>
    <div class="mt-2 mb-1 d-flex justify-content-center field_error" style="color: red">
        <?php echo $msg ?>
    </div>
    <div class="d-flex justify-content-center mt-3 mb-3 mb-lg-4">
        <button type="submit" name="submit" class="btn btn-primary btn-lg">Login</button>
    </div>
</form>
```

**Giải thích:**
- Form sử dụng method `POST`
- Có 3 input:
  - `email`: Type email (có validation tự động)
  - `password`: Type password (ẩn ký tự)
  - ✅ `remember_me`: Checkbox "Remember Me" (giống user)
- Hiển thị thông báo lỗi `$msg` (màu đỏ, nếu có)
- Nút submit có name="submit" để trigger xử lý PHP
- ✅ Nếu chọn Remember Me → Token sẽ được lưu vào cookie và database

### Database Schema

#### Bảng `admin`

Lưu thông tin quản trị viên

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID admin (Primary Key, Auto Increment) |
| email | varchar(50) | Email đăng nhập (Unique) |
| password | varchar(255) | Mật khẩu đã hash bằng MD5 ✅ |

**Lưu ý bảo mật:**
- ✅ Password đã được hash bằng MD5 (giống user)
- ✅ Không lưu plain text (an toàn hơn)
- ✅ Đơn giản hóa cho demo/giáo dục: chỉ dùng `trim()` để làm sạch input
- ✅ Password hash: MD5('123') = '202cb962ac59075b964b07152d234b70'

**Sử dụng trong:**
- `login.php`: SELECT (kiểm tra đăng nhập với password hash)

#### Bảng `admin_tokens`

Lưu token Remember Me cho admin

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID token (Primary Key, Auto Increment) |
| admin_id | int(11) | ID admin (Foreign Key) |
| token | varchar(64) | Token ngẫu nhiên (64 ký tự hex, Unique) |
| expires_at | datetime | Thời gian hết hạn (30 ngày) |
| created_at | datetime | Thời gian tạo token |

**Lưu ý:**
- ✅ Token được tạo ngẫu nhiên (64 ký tự hex)
- ✅ Token hết hạn sau 30 ngày
- ✅ Token được lưu trong cookie `admin_remember_token`
- ✅ Khi logout, token được xóa khỏi database và cookie

**Sử dụng trong:**
- `login.php`: INSERT (lưu token khi chọn Remember Me)
- `logout.php`: DELETE (xóa token khi logout)
- `function.php`: SELECT (kiểm tra token khi truy cập trang admin)

### So Sánh Với Customer Login

| Đặc điểm | Admin Login | Customer Login |
|----------|-------------|----------------|
| **File** | `Admin/login.php` | `pages/SignIn.php` |
| **Bảng database** | `admin` | `users` |
| **Bảng tokens** | `admin_tokens` | `user_tokens` |
| **Session variables** | `ADMIN_LOGIN`, `ADMIN_email` | `USER_LOGIN`, `USER_ID`, `USER_NAME` |
| **Password hash** | ✅ MD5 | ✅ MD5 |
| **Remember Me** | ✅ Có | ✅ Có |
| **Cookie name** | `admin_remember_token` | `remember_token` |
| **Token hết hạn** | 30 ngày | 30 ngày |
| **Redirect sau login** | `categories.php` | `index.php` hoặc checkout |
| **Validation** | Chỉ kiểm tra email/password | Có validation đầy đủ |
| **Auto login** | ✅ Kiểm tra token khi truy cập | ✅ Kiểm tra token trong header.php |

### Lưu Ý Quan Trọng

1. **Bảo mật:**
   - ✅ Đơn giản hóa cho demo/giáo dục: chỉ dùng `trim()` để làm sạch input
   - ✅ Password đã được hash bằng MD5 (giống user)
   - ✅ Session được kiểm tra ở nhiều nơi
   - ✅ Remember Me token được kiểm tra trước khi kiểm tra session

2. **Remember Me:**
   - ✅ Token được tạo ngẫu nhiên (64 ký tự hex)
   - ✅ Token được lưu trong cookie và database
   - ✅ Token hết hạn sau 30 ngày
   - ✅ Tự động đăng nhập khi có cookie hợp lệ

3. **Redirect:**
   - Sau khi đăng nhập thành công → Redirect đến `categories.php`
   - Nếu đã đăng nhập (từ Remember Me) → Redirect đến `categories.php`
   - Sử dụng `header()` và `die()` để đảm bảo redirect hoạt động

4. **Error Handling:**
   - Hiển thị thông báo lỗi rõ ràng (màu đỏ)
   - Không tiết lộ thông tin chi tiết về lỗi (bảo mật)

5. **Session Variable:**
   - ✅ Đã thống nhất dùng `$_SESSION['ADMIN_email']` (chữ thường) trong toàn bộ hệ thống

### Các Hàm Hỗ Trợ Trong `function.php`

#### `saveAdminRememberToken($con, $adminId)`
- **Mục đích:** Lưu token Remember Me vào cookie và database
- **Tham số:**
  - `$con`: Kết nối database
  - `$adminId`: ID của admin
- **Chức năng:**
  - Tạo token ngẫu nhiên (64 ký tự hex)
  - Lưu token vào bảng `admin_tokens` trong database
  - Lưu token vào cookie `admin_remember_token` (30 ngày)
  - Token hết hạn sau 30 ngày
- **Sử dụng trong:** `Admin/login.php` (khi chọn Remember Me)

#### `deleteAdminRememberToken($con, $token)`
- **Mục đích:** Xóa token Remember Me khỏi cookie và database
- **Tham số:**
  - `$con`: Kết nối database
  - `$token`: Token cần xóa
- **Chức năng:**
  - Xóa token khỏi bảng `admin_tokens` trong database
  - Xóa cookie `admin_remember_token`
- **Sử dụng trong:** `Admin/logout.php` (khi logout)

#### `checkAdminRememberToken($con)`
- **Mục đích:** Kiểm tra token Remember Me và tự động đăng nhập
- **Tham số:**
  - `$con`: Kết nối database
- **Chức năng:**
  - Kiểm tra cookie `admin_remember_token`
  - Nếu có cookie → Tìm token trong database
  - Nếu token hợp lệ và chưa hết hạn → Tự động đăng nhập
  - Tạo session `ADMIN_LOGIN` và `ADMIN_email`
  - Nếu token không hợp lệ → Xóa cookie
- **Sử dụng trong:** 
  - `Admin/login.php` (kiểm tra token khi truy cập)
  - `Admin/topNav.php` (kiểm tra token khi truy cập trang admin)
  - `Admin/categories.php`, `Admin/books.php` (kiểm tra token trước khi kiểm tra session)

#### `deleteAllAdminTokens($con, $adminId)`
- **Mục đích:** Xóa tất cả token của admin (khi đổi password hoặc logout tất cả thiết bị)
- **Tham số:**
  - `$con`: Kết nối database
  - `$adminId`: ID của admin
- **Chức năng:**
  - Xóa tất cả token của admin khỏi bảng `admin_tokens`
- **Sử dụng trong:** Có thể sử dụng khi admin đổi password (chưa implement)

---

## 🚪 CHI TIẾT LOGOUT

### File: `Admin/logout.php`

**Mục đích:** Xóa session và đăng xuất admin

### Flow Hoạt Động

```
[Admin click Logout]
    ↓
[logout.php được gọi]
    ↓
[Xóa session variables]
    ↓
[Redirect về login.php]
```

### Code Chi Tiết

```php
<?php
require(__DIR__ . '/../config/connection.php');
require(__DIR__ . '/../includes/function.php');

session_start();

// Xóa token Remember Me nếu có
if (isset($_COOKIE['admin_remember_token'])) {
    deleteAdminRememberToken($con, $_COOKIE['admin_remember_token']);
}

// Xóa session (thống nhất sử dụng ADMIN_email - chữ thường)
unset($_SESSION['ADMIN_LOGIN']);
unset($_SESSION['ADMIN_email']);

header('location:login.php');
die();
?>
```

**Giải thích từng dòng:**

1. **Include các file cần thiết:**
   ```php
   require(__DIR__ . '/../config/connection.php');
   require(__DIR__ . '/../includes/function.php');
   ```
   - Include connection để có kết nối database
   - Include function để có `deleteAdminRememberToken()`

2. **Khởi động session:**
   ```php
   session_start();
   ```
   - Bắt đầu hoặc tiếp tục session hiện tại
   - Cần thiết để có thể unset session variables

3. **Xóa token Remember Me:**
   ```php
   if (isset($_COOKIE['admin_remember_token'])) {
       deleteAdminRememberToken($con, $_COOKIE['admin_remember_token']);
   }
   ```
   - ✅ Kiểm tra xem có cookie `admin_remember_token` không
   - ✅ Nếu có → Gọi `deleteAdminRememberToken()` để:
     - Xóa token khỏi database
     - Xóa cookie `admin_remember_token`

4. **Xóa session variables:**
   ```php
   unset($_SESSION['ADMIN_LOGIN']);
   unset($_SESSION['ADMIN_email']);
   ```
   - Xóa `ADMIN_LOGIN`: Đánh dấu đã logout
   - ✅ Xóa `ADMIN_email`: Xóa thông tin email (thống nhất chữ thường)
   - ✅ Đã sửa từ `ADMIN_EMAIL` thành `ADMIN_email` để thống nhất

5. **Redirect về trang login:**
   ```php
   header('location:login.php');
   die();
   ```
   - Redirect về `login.php`
   - `die()`: Dừng script để đảm bảo không có code nào chạy sau redirect

### So Sánh Với Customer Logout

| Đặc điểm | Admin Logout | Customer Logout |
|----------|--------------|-----------------|
| **File** | `Admin/logout.php` | `pages/logout.php` |
| **Session variables** | `ADMIN_LOGIN`, `ADMIN_email` | `USER_LOGIN`, `USER_ID`, `USER_NAME`, `BeforeCheckoutLogin` |
| **Cookie** | ✅ Xóa cookie `admin_remember_token` | ✅ Xóa cookie `remember_token` |
| **Database** | ✅ Xóa token khỏi `admin_tokens` | ✅ Xóa token khỏi `user_tokens` |
| **Function** | `deleteAdminRememberToken()` | `deleteRememberToken()` |
| **Redirect** | `login.php` | `index.php` |

### Các Cải Thiện Đã Thực Hiện

#### 1. ✅ Thống nhất tên session variable

**Trước:**
- Khi set: `$_SESSION['ADMIN_email']` (chữ thường)
- Khi unset: `$_SESSION['ADMIN_EMAIL']` (chữ hoa) ⚠️

**Sau:**
- ✅ Thống nhất dùng `$_SESSION['ADMIN_email']` (chữ thường) trong toàn bộ hệ thống

#### 2. ✅ Thêm xóa cookie Remember Me

**Trước:**
- ❌ Không xóa cookie khi logout
- ❌ Token vẫn còn trong database

**Sau:**
- ✅ Xóa cookie `admin_remember_token` khi logout
- ✅ Xóa token khỏi database `admin_tokens`
- ✅ Sử dụng function `deleteAdminRememberToken()`

#### 3. ✅ Thêm require connection và function

**Trước:**
- ❌ File không require `connection.php` và `function.php`

**Sau:**
- ✅ Require `connection.php` và `function.php`
- ✅ Có thể sử dụng `deleteAdminRememberToken()` để xóa token

### Lưu Ý Quan Trọng

1. **Bảo mật:**
   - ✅ Xóa tất cả session variables
   - ✅ Xóa cookie Remember Me
   - ✅ Xóa token khỏi database
   - ✅ Redirect về trang login
   - ✅ Sử dụng `die()` để đảm bảo không có code nào chạy sau

2. **Session và Cookie:**
   - Session được xóa hoàn toàn
   - Cookie được xóa hoàn toàn
   - Token được xóa khỏi database
   - Admin phải đăng nhập lại để truy cập (trừ khi có Remember Me mới)

3. **Remember Me:**
   - ✅ Khi logout, token Remember Me cũng bị xóa
   - ✅ Admin không thể tự động đăng nhập lại bằng token cũ
   - ✅ Phải đăng nhập lại và chọn Remember Me để tạo token mới

---

## 🧭 CHI TIẾT TOPNAV

### File: `Admin/topNav.php`

**Mục đích:** Header/Navigation chung cho tất cả trang admin

### Flow Hoạt Động

```
[Trang admin được load]
    ↓
[require topNav.php]
    ↓
[Kiểm tra session ADMIN_LOGIN]
    ├─→ Có session → Hiển thị navigation
    └─→ Không có session → Redirect đến login.php
```

### Code Chi Tiết

#### 1. Include và Kiểm Tra Session

```php
require_once(__DIR__ . '/../config/connection.php');
require_once(__DIR__ . '/../includes/function.php');

// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] != 'yes') {
    header('location:login.php');
    die();
}
```

**Giải thích:**
- `require_once`: Include connection và function (chỉ 1 lần, tránh duplicate)
- ✅ **Kiểm tra Remember Me token:**
  - Nếu chưa có session `ADMIN_LOGIN` → Gọi `checkAdminRememberToken()`
  - Nếu có cookie `admin_remember_token` hợp lệ → Tự động đăng nhập
  - Tạo session `ADMIN_LOGIN` và `ADMIN_email`
- **Kiểm tra session:**
  - Nếu có session và giá trị là `'yes'` → Cho phép tiếp tục
  - Nếu không có hoặc không đúng → Redirect về `login.php`
- Đây là cơ chế bảo vệ chính cho tất cả trang admin
- ✅ Đã cải thiện: Kiểm tra Remember Me token trước khi kiểm tra session

#### 2. HTML Head

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Admin Panel</title>
    <!-- CSS files -->
</head>
```

**Giải thích:**
- Cấu trúc HTML chuẩn
- Include các CSS:
  - Font Awesome (icons)
  - Google Fonts Roboto
  - MDB (Material Design for Bootstrap)
  - Custom admin.css

#### 3. Navigation Bar

```php
<nav class="navbar navbar-expand-lg sticky-top navbar-light bg-light">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="../pages/index.php">
            <img src="../assets/img/logovnu.png" height="40" alt="Book Rental Logo" />
        </a>
        
        <!-- Menu Items -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" href="categories.php">Categories</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="books.php">Books list</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="orders.php">Orders</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php">Users</a>
            </li>
        </ul>
        
        <!-- User Dropdown -->
        <div class="d-flex align-items-center nav-item">
            <?php
            $userName = $_SESSION['ADMIN_email'];
            echo '<div class="btn-group shadow-0">
                        <button type="button" class="btn btn-light dropdown-toggle" 
                                data-mdb-toggle="dropdown">' . $userName . '</button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                  </div>';
            ?>
        </div>
    </div>
</nav>
```

**Giải thích:**
- **Logo:** Link về trang chủ customer (`../pages/index.php`)
- **Menu Items:** 4 menu chính:
  - Categories: Quản lý danh mục
  - Books list: Danh sách sách
  - Orders: Quản lý đơn hàng
  - Users: Quản lý người dùng
  - Feedbacks: Phản hồi
- **User Dropdown:**
  - Hiển thị email admin từ `$_SESSION['ADMIN_email']`
  - Dropdown menu với option Logout

### Cách Sử Dụng

**Trong các trang admin:**

```php
// Xử lý logic trước (nếu có)
require('topNav.php');
// HTML content
```

**Lưu ý:**
- `topNav.php` tự động kiểm tra session
- Nếu chưa đăng nhập → Tự động redirect
- Các trang admin chỉ cần `require('topNav.php')` là đã có navigation

### Styling

File có CSS inline để tùy chỉnh:
- Gradient background cho navbar
- Hover effects cho menu items
- Responsive design với Bootstrap

---

## 👥 CHI TIẾT USERS

### File: `Admin/users.php`

**Mục đích:** Quản lý danh sách người dùng (customers)

### Flow Hoạt Động

```
[Admin truy cập users.php]
    ↓
[Kiểm tra Remember Me token và session]
    ↓
[Xử lý xóa user (nếu có GET)]
    ├─→ Xóa user khỏi database
    └─→ Redirect về users.php (để tránh resubmit)
    ↓
[Lấy danh sách users từ database]
    ↓
[Hiển thị bảng danh sách users]
```

### Code Chi Tiết

#### 1. Require Connection và Function

```php
require_once(__DIR__ . '/../config/connection.php');
require_once(__DIR__ . '/../includes/function.php');
```

**Giải thích:**
- Require connection và function trước để có `$con` và các hàm hỗ trợ

#### 2. Kiểm Tra Remember Me Token và Session

```php
// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] != 'yes') {
    header('Location: login.php');
    exit;
}
```

**Giải thích:**
- Kiểm tra Remember Me token trước (tự động đăng nhập nếu có cookie hợp lệ)
- Kiểm tra session sau (redirect về login nếu chưa đăng nhập)

#### 3. Xử Lý Xóa User

```php
// Xử lý action (delete user)
if (isset($_GET['type']) && $_GET['type'] != ' ') {
    $type = trim($_GET['type']);
    
    if ($type == 'delete') {
        $id = (int)$_GET['id'];
        $deleteSql = "DELETE FROM users WHERE id=$id";
        mysqli_query($con, $deleteSql);
        // Redirect để tránh resubmit form
        header('Location: users.php');
        exit;
    }
}

// Sau khi xử lý xong, mới require topNav
require('topNav.php');
```

**Giải thích:**
- Kiểm tra có action delete không
- Lấy ID user cần xóa (type casting `(int)` để bảo mật)
- Xóa user khỏi database
- **✅ Redirect sau khi xóa** để tránh resubmit form (POST-REDIRECT-GET pattern)
- `exit` sau `header('Location: ...')` để dừng script
- **✅ Require topNav SAU KHI** xử lý logic (tránh lỗi "headers already sent")

#### 3. Lấy Danh Sách Users

```php
$sql = "select * from users order by id desc";
$res = mysqli_query($con, $sql);
```

**Giải thích:**
- Lấy tất cả users
- Sắp xếp theo ID giảm dần (user mới nhất trước)

#### 4. Hiển Thị Bảng

```php
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Mobile</th>
            <th>Date of Joining</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td><?php echo $row['id'] ?></td>
            <td><?php echo $row['name'] ?></td>
            <td><?php echo $row['email'] ?></td>
            <td><?php echo $row['mobile'] ?></td>
            <td><?php echo $row['doj'] ?></td>
            <td>
                <a class='link-white btn btn-danger px-2 py-1' 
                   href='?type=delete&id=<?php echo $row['id'] ?>'>Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
```

**Giải thích:**
- Hiển thị bảng với các cột: ID, Name, Email, Mobile, Date of Joining
- Mỗi dòng có nút Delete
- Click Delete → Gọi lại trang với `?type=delete&id=X`

### Database Schema

#### Bảng `users`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID user (Primary Key) |
| name | varchar(80) | Tên user |
| email | varchar(50) | Email |
| mobile | bigint(20) | Số điện thoại |
| doj | datetime | Ngày tham gia (Date of Join) |
| password | varchar(255) | Mật khẩu |

**Sử dụng trong:**
- `users.php`: SELECT (lấy danh sách), DELETE (xóa user)

### Lưu Ý Quan Trọng

1. **Bảo mật:**
   - ✅ Đơn giản hóa cho demo/giáo dục: chỉ dùng `trim()` và type casting
   - ⚠️ Không có xác nhận trước khi xóa (có thể thêm confirm dialog)
   - ⚠️ Xóa user sẽ xóa luôn các đơn hàng liên quan? (Cần kiểm tra foreign key)

2. **Chức năng:**
   - Chỉ có chức năng xem và xóa
   - Không có chức năng sửa user
   - Không có chức năng thêm user (user tự đăng ký)

3. **UI/UX:**
   - Bảng đơn giản, dễ đọc
   - Nút Delete màu đỏ để cảnh báo

---

## 📦 CHI TIẾT ORDERS

### File: `Admin/orders.php`

**Mục đích:** Quản lý đơn hàng - Xem và cập nhật trạng thái đơn hàng

### Flow Hoạt Động

```
[Admin truy cập orders.php]
    ↓
[Kiểm tra session]
    ↓
[Xử lý cập nhật trạng thái đơn hàng (nếu có POST)]
    ├─→ Nếu hủy/trả → Tăng lại số lượng sách
    └─→ Cập nhật order_status
    ↓
[Lấy danh sách đơn hàng từ database]
    ↓
[Hiển thị bảng với form cập nhật trạng thái]
```

### Code Chi Tiết

#### 1. Kiểm Tra Session

```php
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] == ' ') {
    header('Location: login.php');
    exit;
}
```

**Giải thích:**
- Kiểm tra session trước khi xử lý logic
- Đảm bảo chỉ admin mới truy cập được

#### 2. Xử Lý Cập Nhật Trạng Thái

```php
if (isset($_POST['status_id'])) {
    $orderId = (int)$_POST['orderId'];
    $statusId = (int)$_POST['status_id'];
    
    // Nếu đơn hàng bị hủy hoặc trả lại, tăng lại số lượng sách
    if (in_array($statusId, [4, 6])) {
        $qtyRes = mysqli_query($con, "SELECT books.id FROM orders
                                       JOIN order_detail ON orders.id=order_detail.order_id
                                       JOIN books ON order_detail.book_id=books.id
                                       WHERE order_detail.order_id=$orderId");
        if ($qtyRow = mysqli_fetch_assoc($qtyRes)) {
            mysqli_query($con, "UPDATE books SET qty = qty + 1 WHERE id={$qtyRow['id']}");
        }
    }
    
    mysqli_query($con, "UPDATE orders SET order_status=$statusId WHERE id=$orderId");
    header('Location: orders.php');
    exit;
}
```

**Giải thích:**
- Nhận `orderId` và `statusId` từ form POST
- **Logic đặc biệt:** Nếu status là 4 (Cancelled) hoặc 6 (Returned):
  - Tìm sách trong đơn hàng
  - Tăng lại số lượng sách (`qty + 1`)
- Cập nhật `order_status` trong bảng `orders`
- Redirect về `orders.php` để hiển thị cập nhật

#### 3. Lấy Danh Sách Đơn Hàng

```php
$res = mysqli_query($con, "SELECT orders.*, name, status_name FROM orders
                            JOIN order_detail ON orders.id=order_detail.order_id
                            JOIN books ON order_detail.book_id=books.id
                            JOIN order_status ON orders.order_status=order_status.id
                            ORDER BY date DESC");
```

**Giải thích:**
- JOIN 3 bảng:
  - `orders`: Thông tin đơn hàng
  - `order_detail`: Chi tiết đơn hàng (để lấy book_id)
  - `books`: Thông tin sách (để lấy tên sách)
  - `order_status`: Trạng thái đơn hàng (để lấy tên trạng thái)
- Sắp xếp theo ngày giảm dần (đơn hàng mới nhất trước)

#### 4. Hiển Thị Bảng Với Form

```php
<table class="table">
    <thead>
        <tr>
            <th>OrderID</th>
            <th>Order Date</th>
            <th>Book Name</th>
            <th>Book Price</th>
            <th>Rent Duration</th>
            <th>Address</th>
            <th>Payment Method</th>
            <th>Payment Status</th>
            <th>Order Status</th>
            <th>Change Order Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($res)): 
            $canChange = !in_array($row['status_name'], ['Returned', 'Cancelled']);
        ?>
        <tr>
            <td><?php echo $row['id'] ?></td>
            <td><?php echo $row['date'] ?></td>
            <td><?php echo $row['name'] ?></td>
            <td>₫<?php echo $row['total'] ?></td>
            <td><?php echo $row['duration'] ?> days</td>
            <td><?php echo $row['address'] ?><?php echo $row['address2'] ? ', ' . $row['address2'] : '' ?></td>
            <td><?php echo $row['payment_method'] ?></td>
            <td><?php echo $row['payment_status'] ?></td>
            <td><?php echo $row['status_name'] ?></td>
            <td>
                <?php if ($canChange): ?>
                <form method="post">
                    <input type="hidden" name="orderId" value="<?php echo $row['id'] ?>">
                    <select class="form-select" name="status_id">
                        <option value="">Select Status</option>
                        <?php
                        $statusSql = mysqli_query($con, "SELECT * FROM order_status ORDER BY status_name");
                        while ($statusRow = mysqli_fetch_assoc($statusSql)):
                        ?>
                        <option value="<?php echo $statusRow['id'] ?>"><?php echo $statusRow['status_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <input type="submit" value="Submit" class="btn btn-primary mt-2">
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
```

**Giải thích:**
- Hiển thị đầy đủ thông tin đơn hàng
- **Logic đặc biệt:** Chỉ cho phép thay đổi trạng thái nếu đơn hàng chưa "Returned" hoặc "Cancelled"
- Form cập nhật:
  - Hidden input chứa `orderId`
  - Dropdown chọn trạng thái mới (lấy từ bảng `order_status`)
  - Nút Submit để cập nhật

### Database Schema

#### Bảng `orders`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID đơn hàng |
| user_id | int(11) | ID user |
| address | varchar(100) | Địa chỉ |
| address2 | varchar(100) | Địa chỉ 2 |
| pin | int(11) | Mã pin |
| payment_method | varchar(20) | Phương thức thanh toán |
| total | int(11) | Tổng tiền |
| payment_status | varchar(20) | Trạng thái thanh toán |
| order_status | int(11) | Trạng thái đơn hàng (FK → order_status.id) |
| date | datetime | Ngày đặt hàng |
| duration | int(11) | Số ngày thuê |

#### Bảng `order_status`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID trạng thái |
| status_name | varchar(15) | Tên trạng thái |

**Các trạng thái:**
1. Pending (id=1)
2. Processing (id=2)
3. Shipped (id=3)
4. Cancelled (id=4)
5. Delivered (id=5)
6. Returned (id=6)

**Sử dụng trong:**
- `orders.php`: SELECT (lấy danh sách), UPDATE (cập nhật trạng thái)
- `books`: UPDATE (tăng số lượng khi hủy/trả)

### Lưu Ý Quan Trọng

1. **Logic nghiệp vụ:**
   - ✅ Tự động tăng số lượng sách khi hủy/trả đơn
   - ✅ Không cho phép thay đổi trạng thái đơn đã hủy/trả
   - ✅ Hiển thị đầy đủ thông tin đơn hàng

2. **Bảo mật:**
   - ✅ Type casting cho ID: `(int)$_POST['orderId']`
   - ✅ Kiểm tra session trước khi xử lý

3. **UI/UX:**
   - Form inline trong bảng, dễ sử dụng
   - Dropdown chọn trạng thái rõ ràng
   - Ẩn form nếu đơn đã hủy/trả

---

## 📚 CHI TIẾT CATEGORIES

### File: `Admin/categories.php`

**Mục đích:** Quản lý danh sách danh mục sách - Xem, thay đổi trạng thái, xóa

### Flow Hoạt Động

```
[Admin truy cập categories.php]
    ↓
[Kiểm tra session]
    ↓
[Xử lý action (nếu có GET)]
    ├─→ Thay đổi status (active/deactive)
    └─→ Xóa category
    ↓
[Lấy danh sách categories từ database]
    ↓
[Hiển thị bảng danh sách]
```

### Code Chi Tiết

#### 1. Kiểm Tra Session

```php
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] == ' ') {
    header('Location: login.php');
    exit;
}
```

**Giải thích:**
- Kiểm tra session trước khi xử lý logic

#### 2. Xử Lý Action

```php
if (isset($_GET['type']) && $_GET['type'] != ' ') {
    $type = trim($_GET['type']);
    $id = (int)$_GET['id'];
    
    if ($type == 'status') {
        $operation = trim($_GET['operation']);
        $status = ($operation == 'active') ? 1 : 0;
        mysqli_query($con, "UPDATE categories SET status=$status WHERE id=$id");
    } elseif ($type == 'delete') {
        mysqli_query($con, "DELETE FROM categories WHERE id=$id");
    }
    
    header('Location: categories.php');
    exit;
}
```

**Giải thích:**
- **Thay đổi status:**
  - Nhận `operation` (active/deactive)
  - Chuyển thành số: active = 1, deactive = 0
  - Cập nhật trong database
- **Xóa category:**
  - Xóa category khỏi database
- Redirect về `categories.php` sau khi xử lý

#### 3. Lấy Danh Sách Categories

```php
$sql = "select * from categories order by category asc";
$res = mysqli_query($con, $sql);
```

**Giải thích:**
- Lấy tất cả categories
- Sắp xếp theo tên tăng dần (A-Z)

#### 4. Hiển Thị Bảng

```php
<table class="table">
    <thead>
        <tr>
            <th>Categories</th>
            <th>Status</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['category']) ?></td>
            <td>
                <?php if ($row['status'] == 1): ?>
                    <a href="?type=status&operation=deactive&id=<?php echo $row['id'] ?>">Active</a>
                <?php else: ?>
                    <a href="?type=status&operation=active&id=<?php echo $row['id'] ?>">Inactive</a>
                <?php endif; ?>
            </td>
            <td>
                <a href="manageCategories.php?id=<?php echo $row['id'] ?>">Edit</a>
            </td>
            <td>
                <a href="?type=delete&id=<?php echo $row['id'] ?>" 
                   onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
```

**Giải thích:**
- Hiển thị tên category (dùng `htmlspecialchars()` để bảo mật)
- **Status:** Hiển thị link để toggle status
  - Nếu Active → Link "Active" (click để deactive)
  - Nếu Inactive → Link "Inactive" (click để active)
- **Edit:** Link đến `manageCategories.php?id=X`
- **Delete:** Link xóa với confirm dialog JavaScript

### Database Schema

#### Bảng `categories`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID category (Primary Key) |
| category | varchar(50) | Tên danh mục |
| status | int(11) | Trạng thái (1 = active, 0 = inactive) |

**Sử dụng trong:**
- `categories.php`: SELECT (lấy danh sách), UPDATE (status), DELETE (xóa)
- `manageCategories.php`: SELECT (lấy 1 category), INSERT, UPDATE

### Lưu Ý Quan Trọng

1. **Bảo mật:**
   - ✅ Đơn giản hóa cho demo/giáo dục: chỉ dùng `trim()` và type casting
   - ✅ Sử dụng `htmlspecialchars()` cho output
   - ✅ Có confirm dialog trước khi xóa

2. **Chức năng:**
   - Xem danh sách categories
   - Toggle status (active/inactive)
   - Xóa category
   - Link đến trang thêm/sửa

3. **UI/UX:**
   - Bảng đơn giản, dễ đọc
   - Status có thể click để toggle
   - Có confirm dialog khi xóa

---

## ✏️ CHI TIẾT MANAGECATEGORIES

### File: `Admin/manageCategories.php`

**Mục đích:** Thêm mới hoặc sửa danh mục sách

### Flow Hoạt Động

```
[Admin truy cập manageCategories.php]
    ↓
[Kiểm tra Remember Me token và session]
    ↓
[Lấy thông tin category nếu đang edit (có ID, chỉ khi không có POST submit)]
    ↓
[Xử lý form submit (nếu có POST)]
    ├─→ Kiểm tra duplicate (trừ category hiện tại nếu đang edit)
    ├─→ Thêm mới hoặc cập nhật
    ├─→ Redirect về categories.php (nếu thành công)
    └─→ Giữ lại giá trị form (nếu có lỗi)
    ↓
[Require topNav và hiển thị form]
```

### Code Chi Tiết

#### 1. Require Connection và Function

```php
require_once(__DIR__ . '/../config/connection.php');
require_once(__DIR__ . '/../includes/function.php');
```

**Giải thích:**
- Require connection và function trước để có `$con` và các hàm hỗ trợ

#### 2. Kiểm Tra Remember Me Token và Session

```php
// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] != 'yes') {
    header('Location: login.php');
    exit;
}
```

**Giải thích:**
- Kiểm tra Remember Me token trước (tự động đăng nhập nếu có cookie hợp lệ)
- Kiểm tra session sau (redirect về login nếu chưa đăng nhập)

#### 3. Lấy Thông Tin Category (Nếu Đang Edit)

```php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$categories = '';
$msg = '';
$res = '';

// Lấy thông tin category nếu đang edit (chỉ khi không có POST submit - tránh mất dữ liệu khi có lỗi)
if ($id > 0 && !isset($_POST['submit'])) {
    $sql = mysqli_query($con, "SELECT * FROM categories WHERE id=$id");
    if ($row = mysqli_fetch_assoc($sql)) {
        $categories = $row['category'];
    } else {
        header('Location: categories.php');
        exit;
    }
}
```

**Giải thích:**
- Lấy `id` từ GET (nếu có)
- **✅ Chỉ lấy dữ liệu từ database khi KHÔNG có POST submit** (tránh mất dữ liệu khi có lỗi)
- Nếu có `id` và không có POST:
  - Query database lấy thông tin category
  - Gán vào biến `$categories` để hiển thị trong form
  - Nếu không tìm thấy → Redirect về `categories.php`

#### 4. Xử Lý Form Submit

```php
// Xử lý submit form
if (isset($_POST['submit'])) {
    $category = trim($_POST['category']);
    $categories = $category; // Giữ giá trị từ POST để hiển thị lại trong form nếu có lỗi
    
    // Check duplicate (trừ category hiện tại nếu đang edit)
    $checkSql = mysqli_query($con, "SELECT id FROM categories WHERE category='$category'");
    if (mysqli_num_rows($checkSql) > 0) {
        $existing = mysqli_fetch_assoc($checkSql);
        if (!$id || $existing['id'] != $id) {
            $msg = "Category already exists";
        }
    }
    
    // Thực hiện query và redirect (nếu không có lỗi)
    if (empty($msg)) {
        if ($id > 0) {
            $sql = "UPDATE categories SET category='$category' WHERE id=$id";
        } else {
            $sql = "INSERT INTO categories(category, status) VALUES('$category', 1)";
        }
        
        if (mysqli_query($con, $sql)) {
            header('Location: categories.php');
            exit;
        } else {
            $res = "Error: " . mysqli_error($con);
        }
    }
}

// Sau khi xử lý xong tất cả logic, mới require topNav để hiển thị HTML
require('topNav.php');
```

**Giải thích:**
- **Lấy dữ liệu từ form:**
  - `trim()` để loại bỏ khoảng trắng đầu/cuối
  - **✅ Giữ giá trị từ POST** để hiển thị lại trong form nếu có lỗi (`$categories = $category`)
  - **Lưu ý:** Đơn giản hóa cho demo/giáo dục, không dùng SQL escape
- **Kiểm tra duplicate:**
  - Tìm category có tên trùng
  - Nếu đang edit (`$id > 0`): Cho phép trùng với chính nó
  - Nếu đang thêm mới: Không cho phép trùng
- **Thêm mới hoặc cập nhật:**
  - Nếu có `id` → UPDATE
  - Nếu không có `id` → INSERT (status mặc định = 1)
- **✅ Redirect về `categories.php` sau khi thành công** (với `exit` sau `header('Location: ...')`)
- **✅ Require topNav SAU KHI** xử lý logic (tránh lỗi "headers already sent")

#### 4. Hiển Thị Form

```php
<form method="post">
    <div class="form-outline mb-4 mx-5">
        <input type="text" name="category" value="<?php echo $categories ?>" id="category" class="form-control" required />
        <label class="form-label" for="category">Enter category name</label>
    </div>
    <div class="mb-1 d-flex justify-content-center field_error">
        <?php echo $msg ?>
    </div>
    <div class="mb-1 d-flex justify-content-center">
        <?php echo $res ?>
    </div>
    <div class="text-center">
        <button type="submit" name="submit" class="btn btn-primary mx-5">Submit</button>
    </div>
</form>
```

**Giải thích:**
- Form đơn giản với 1 input: tên category
- Auto-fill giá trị nếu đang edit
- Hiển thị thông báo lỗi nếu có

### Database Schema

Sử dụng bảng `categories` (đã giải thích ở phần Categories)

**Sử dụng trong:**
- `manageCategories.php`: SELECT (lấy 1 category), INSERT (thêm mới), UPDATE (sửa)

### Lưu Ý Quan Trọng

1. **Logic thêm/sửa:**
   - Cùng 1 form cho cả thêm và sửa
   - Phân biệt bằng cách kiểm tra `$id`
   - Kiểm tra duplicate thông minh (cho phép trùng với chính nó khi edit)

2. **Bảo mật:**
   - ✅ Đơn giản hóa cho demo/giáo dục: chỉ dùng `trim()` và type casting
   - ✅ Type casting cho ID: `(int)$_GET['id']`

3. **UI/UX:**
   - Form đơn giản, dễ sử dụng
   - Auto-fill khi edit
   - Hiển thị thông báo lỗi rõ ràng

---

## 📖 CHI TIẾT BOOKS

### File: `Admin/books.php`

**Mục đích:** Quản lý danh sách sách - Xem, thay đổi trạng thái, xóa

### Flow Hoạt Động

```
[Admin truy cập books.php]
    ↓
[Kiểm tra session]
    ↓
[Xử lý action (nếu có GET)]
    ├─→ Thay đổi status (active/inactive)
    ├─→ Thay đổi best_seller (Most Viewed/Normal)
    └─→ Xóa sách
    ↓
[Lấy danh sách sách từ database]
    ↓
[Hiển thị bảng danh sách]
```

### Code Chi Tiết

#### 1. Kiểm Tra Session

```php
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] == ' ') {
    header('Location: login.php');
    exit;
}
```

#### 2. Xử Lý Action

```php
if (isset($_GET['type']) && $_GET['type'] != ' ') {
    $type = trim($_GET['type']);
    $id = (int)$_GET['id'];
    
    if ($type == 'status') {
        $status = ($_GET['operation'] == 'active') ? 1 : 0;
        mysqli_query($con, "UPDATE books SET status=$status WHERE id=$id");
    } elseif ($type == 'best_seller') {
        $bestSeller = ($_GET['operation'] == 'active') ? 1 : 0;
        mysqli_query($con, "UPDATE books SET best_seller=$bestSeller WHERE id=$id");
    } elseif ($type == 'delete') {
        mysqli_query($con, "DELETE FROM books WHERE id=$id");
    }
    
    header('Location: books.php');
    exit;
}

// Sau khi xử lý xong, mới require topNav để hiển thị HTML
require('topNav.php');
```

**Giải thích:**
- **Thay đổi status:**
  - Active/Deactive sách (hiển thị/ẩn trên website)
- **Thay đổi best_seller:**
  - Đánh dấu sách là "Most Viewed" (hiển thị trên trang chủ)
- **Xóa sách:**
  - Xóa sách khỏi database
- **✅ Redirect sau khi xử lý** để tránh resubmit form
- **✅ Require topNav SAU KHI** xử lý logic (tránh lỗi "headers already sent")

#### 4. Lấy Danh Sách Sách

```php
$sql = "SELECT books.*, categories.category 
        FROM books 
        LEFT JOIN categories ON books.category_id=categories.id 
        ORDER BY books.name ASC";
$res = mysqli_query($con, $sql);
```

**Giải thích:**
- JOIN với bảng `categories` để lấy tên danh mục
- LEFT JOIN: Vẫn hiển thị sách dù không có category
- Sắp xếp theo tên sách tăng dần

#### 4. Hiển Thị Bảng

```php
<table class="table">
    <thead>
        <tr>
            <th>ISBN</th>
            <th>Category</th>
            <th>img</th>
            <th>Name</th>
            <th>Author</th>
            <th>Security</th>
            <th>Rent</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['ISBN']) ?></td>
            <td><?php echo htmlspecialchars($row['category'] ?? 'N/A') ?></td>
            <td>
                <img src="<?php echo BOOK_IMAGE_SITE_PATH . $row['img'] ?>" 
                     class="card-img" height="60px" width="80px" alt="Book cover">
            </td>
            <td><?php echo htmlspecialchars($row['name']) ?></td>
            <td><?php echo htmlspecialchars($row['author']) ?></td>
            <td>₫<?php echo number_format($row['security']) ?></td>
            <td>₫<?php echo number_format($row['rent']) ?>/day</td>
            <td>₫<?php echo number_format($row['price']) ?></td>
            <td><?php echo $row['qty'] ?></td>
            <td>
                <?php if ($row['best_seller'] == 1): ?>
                    <a href="?type=best_seller&operation=deactive&id=<?php echo $row['id'] ?>">Most Viewed</a>
                <?php else: ?>
                    <a href="?type=best_seller&operation=active&id=<?php echo $row['id'] ?>">Normal</a>
                <?php endif; ?>
            </td>
            <td>
                <a href="manageBooks.php?id=<?php echo $row['id'] ?>">Edit</a> | 
                <a href="?type=delete&id=<?php echo $row['id'] ?>" 
                   onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
```

**Giải thích:**
- Hiển thị đầy đủ thông tin sách
- **Ảnh sách:** Hiển thị thumbnail
- **Status:** Hiển thị "Most Viewed" hoặc "Normal" (có thể click để toggle)
- **Actions:** Edit và Delete
- Sử dụng `htmlspecialchars()` và `number_format()` để format dữ liệu

### Database Schema

#### Bảng `books`

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID sách (Primary Key) |
| ISBN | varchar(20) | ISBN sách |
| category_id | int(11) | ID danh mục (FK → categories.id) |
| name | varchar(200) | Tên sách |
| author | varchar(100) | Tác giả |
| security | float | Tiền đặt cọc |
| rent | float | Giá thuê/ngày |
| price | float | Giá bán (nếu có) |
| qty | int(11) | Số lượng |
| status | int(11) | Trạng thái (1 = active, 0 = inactive) |
| best_seller | int(11) | Bán chạy (1 = Most Viewed, 0 = Normal) |
| img | varchar(200) | Tên file ảnh |
| short_desc | text | Mô tả ngắn |
| description | text | Mô tả chi tiết |

**Sử dụng trong:**
- `books.php`: SELECT (lấy danh sách), UPDATE (status, best_seller), DELETE (xóa)
- `manageBooks.php`: SELECT (lấy 1 sách), INSERT, UPDATE

### Lưu Ý Quan Trọng

1. **Chức năng:**
   - Xem danh sách sách đầy đủ
   - Toggle status (active/inactive)
   - Toggle best_seller (Most Viewed/Normal)
   - Xóa sách
   - Link đến trang thêm/sửa

2. **Bảo mật:**
   - ✅ Đơn giản hóa cho demo/giáo dục: chỉ dùng `trim()` và type casting
   - ✅ Sử dụng `htmlspecialchars()` cho output
   - ✅ Có confirm dialog khi xóa

3. **UI/UX:**
   - Hiển thị ảnh thumbnail
   - Format số tiền dễ đọc
   - Status có thể click để toggle

---

## 📝 CHI TIẾT MANAGEBOOKS

### File: `Admin/manageBooks.php`

**Mục đích:** Thêm mới hoặc sửa sách

### Flow Hoạt Động

```
[Admin truy cập manageBooks.php]
    ↓
[Kiểm tra Remember Me token và session]
    ↓
[Lấy ảnh cũ từ database (nếu đang edit)]
    ↓
[Lấy thông tin sách nếu đang edit (có ID, chỉ khi không có POST submit)]
    ↓
[Xử lý form submit (nếu có POST)]
    ├─→ Kiểm tra duplicate (trừ book hiện tại nếu đang edit)
    ├─→ Upload ảnh mới (nếu có) hoặc giữ ảnh cũ (nếu edit)
    ├─→ Thêm mới hoặc cập nhật
    ├─→ Redirect về books.php (nếu thành công)
    └─→ Giữ lại giá trị form và ảnh cũ (nếu có lỗi)
    ↓
[Require topNav và hiển thị form]
```

### Code Chi Tiết

#### 1. Require Connection và Function

```php
require_once(__DIR__ . '/../config/connection.php');
require_once(__DIR__ . '/../includes/function.php');
```

**Giải thích:**
- Require connection và function trước để có `$con` và các hàm hỗ trợ

#### 2. Kiểm Tra Remember Me Token và Session

```php
// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] != 'yes') {
    header('Location: login.php');
    exit;
}
```

**Giải thích:**
- Kiểm tra Remember Me token trước (tự động đăng nhập nếu có cookie hợp lệ)
- Kiểm tra session sau (redirect về login nếu chưa đăng nhập)

#### 3. Lấy Ảnh Cũ (Nếu Đang Edit)

```php
// Biến để lưu ảnh cũ khi edit (cần lấy trước để dùng khi có lỗi hoặc không upload ảnh mới)
$currentImg = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $oldImgSql = mysqli_query($con, "SELECT img FROM books WHERE id=$id");
    if ($oldImgRow = mysqli_fetch_assoc($oldImgSql)) {
        $currentImg = $oldImgRow['img'];
    } else {
        // Book không tồn tại, redirect về books.php
        header('Location: books.php');
        exit;
    }
}
```

**Giải thích:**
- Lấy ảnh cũ từ database **TRƯỚC KHI** xử lý POST
- Dùng khi có lỗi hoặc khi không upload ảnh mới (giữ ảnh cũ)
- Kiểm tra book có tồn tại không (redirect nếu không tồn tại)

#### 4. Lấy Thông Tin Sách (Nếu Đang Edit)

```php
// Lấy thông tin book nếu đang edit (chỉ khi không có POST submit - tránh mất dữ liệu khi có lỗi)
if ($id > 0 && !isset($_POST['submit'])) {
    $sql = mysqli_query($con, "SELECT * FROM books WHERE id=$id");
    if ($row = mysqli_fetch_assoc($sql)) {
        $category_id = $row['category_id'];
        $ISBN = $row['ISBN'];
        $name = $row['name'];
        $author = $row['author'];
        $security = $row['security'];
        $rent = $row['rent'];
        $qty = $row['qty'];
        $short_desc = $row['short_desc'];
        $description = $row['description'];
        $img = $row['img']; // Lưu ảnh cũ để hiển thị trong form
    }
}
```

**Giải thích:**
- Chỉ lấy dữ liệu từ database khi **KHÔNG có POST submit**
- Tránh mất dữ liệu khi có lỗi (giữ lại giá trị từ POST)
- Lưu ảnh cũ vào biến `$img` để hiển thị trong form

#### 5. Xử Lý Form Submit

```php
if (isset($_POST['submit'])) {
    $category_id = (int)$_POST['category_id'];
    $ISBN = trim($_POST['ISBN']);
    $name = trim($_POST['name']);
    $author = trim($_POST['author']);
    $security = (int)$_POST['security'];
    $rent = (int)$_POST['rent'];
    $qty = (int)$_POST['qty'];
    $short_desc = trim($_POST['short_desc']);
    $description = trim($_POST['description']);
    
    // Check if book name already exists (except current book)
    $checkSql = mysqli_query($con, "SELECT id FROM books WHERE name='$name'");
    if (mysqli_num_rows($checkSql) > 0) {
        $existing = mysqli_fetch_assoc($checkSql);
        if (!$id || $existing['id'] != $id) {
            $msg = "Book already exists";
        }
    }
    
    if (empty($msg)) {
        if ($id > 0) {
            // Update existing book
            // Nếu có upload ảnh mới, sử dụng ảnh mới
            if (!empty($_FILES['img']['name'])) {
                $img = time() . '_' . $_FILES['img']['name'];
                move_uploaded_file($_FILES['img']['tmp_name'], BOOK_IMAGE_SERVER_PATH . $img);
                $sql = "UPDATE books SET category_id=$category_id, ISBN='$ISBN', name='$name', author='$author', 
                        security=$security, rent=$rent, qty=$qty, short_desc='$short_desc', 
                        description='$description', img='$img' WHERE id=$id";
            } else {
                // Không upload ảnh mới, giữ nguyên ảnh cũ (không cập nhật field img)
                $sql = "UPDATE books SET category_id=$category_id, ISBN='$ISBN', name='$name', author='$author', 
                        security=$security, rent=$rent, qty=$qty, short_desc='$short_desc', 
                        description='$description' WHERE id=$id";
                // Giữ lại ảnh cũ để hiển thị trong form
                $img = $currentImg;
            }
        } else {
            // Insert new book - bắt buộc phải có ảnh
            if (!empty($_FILES['img']['name'])) {
                $img = time() . '_' . $_FILES['img']['name'];
                move_uploaded_file($_FILES['img']['tmp_name'], BOOK_IMAGE_SERVER_PATH . $img);
                $sql = "INSERT INTO books(category_id, ISBN, name, author, security, rent, qty, short_desc, description, status, img)
                        VALUES ($category_id, '$ISBN', '$name', '$author', $security, $rent, $qty, '$short_desc', '$description', 1, '$img')";
            } else {
                $msg = "Please upload book image";
            }
        }
        
        // Thực hiện query và redirect (nếu không có lỗi)
        if (empty($msg)) {
            if (mysqli_query($con, $sql)) {
                header('Location: books.php');
                exit;
            } else {
                $error = "Error: " . mysqli_error($con);
            }
        }
    }
    
    // Nếu có lỗi và đang edit, giữ lại ảnh cũ để hiển thị trong form
    if (!empty($msg) && $id > 0 && empty($img) && !empty($currentImg)) {
        $img = $currentImg;
    }
}

// Sau khi xử lý xong tất cả logic, mới require topNav để hiển thị HTML
require('topNav.php');
```

**Giải thích:**
- **Lấy dữ liệu từ form:**
  - Type casting cho số: `(int)$_POST[...]`
  - `trim()` để loại bỏ khoảng trắng đầu/cuối cho chuỗi
  - **Lưu ý:** Đơn giản hóa cho demo/giáo dục, không dùng SQL escape
- **Kiểm tra duplicate:**
  - Kiểm tra tên sách đã tồn tại chưa
  - Cho phép trùng với chính nó khi edit
- **Upload ảnh:**
  - **Khi edit:** Nếu có upload ảnh mới → Cập nhật ảnh mới; Nếu không → Giữ nguyên ảnh cũ
  - **Khi thêm mới:** Bắt buộc phải có ảnh (báo lỗi nếu không có)
- **Thêm mới hoặc cập nhật:**
  - Nếu có `id` → UPDATE (có thể cập nhật ảnh nếu upload mới)
  - Nếu không có `id` → INSERT (bắt buộc có ảnh)
- **Giữ lại giá trị khi có lỗi:**
  - Nếu có lỗi và đang edit → Giữ lại ảnh cũ để hiển thị trong form
  - Giữ lại tất cả giá trị từ POST để người dùng không phải nhập lại
- **✅ Require topNav SAU KHI** xử lý logic (tránh lỗi "headers already sent")

#### 4. Hiển Thị Form

```php
<form method="post" enctype="multipart/form-data">
    <!-- ISBN và Category -->
    <div class="row g-3">
        <div class="col-sm-8">
            <input type="text" name="ISBN" value="<?php echo $ISBN ?>" required />
        </div>
        <div class="col-sm">
            <select class="form-select" name="category_id">
                <option>Select Category</option>
                <?php
                $categorySql = mysqli_query($con, "select id, category from categories order by category asc");
                while ($row = mysqli_fetch_assoc($categorySql)) {
                    if ($row['id'] == $category_id) {
                        echo "<option selected value=" . $row['id'] . ">" . $row['category'] . "</option>";
                    } else {
                        echo "<option value=" . $row['id'] . ">" . $row['category'] . "</option>";
                    }
                }
                ?>
            </select>
        </div>
    </div>
    
    <!-- Các trường khác: name, author, security, rent, qty, img, short_desc, description -->
    
    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
</form>
```

**Giải thích:**
- Form có `enctype="multipart/form-data"` để upload file
- **Category dropdown:**
  - Lấy danh sách categories từ database
  - Auto-select category hiện tại khi edit
- **Các trường:**
  - ISBN, Name, Author: Text input
  - Security, Rent, Qty: Number input
  - Image: File input (chỉ bắt buộc khi thêm mới)
  - Short Description, Description: Textarea
- Auto-fill giá trị khi edit

### Database Schema

Sử dụng bảng `books` (đã giải thích ở phần Books)

**Sử dụng trong:**
- `manageBooks.php`: SELECT (lấy 1 sách), INSERT (thêm mới), UPDATE (sửa)
- `categories`: SELECT (lấy danh sách categories cho dropdown)

### Lưu Ý Quan Trọng

1. **Upload ảnh:**
   - Chỉ bắt buộc khi thêm mới
   - Tên file: `time() . '_' . tên gốc` (tránh trùng)
   - Lưu vào `BOOK_IMAGE_SERVER_PATH`

2. **Logic thêm/sửa:**
   - Cùng 1 form cho cả thêm và sửa
   - Khi edit: Không cập nhật ảnh (giữ nguyên ảnh cũ)
   - Khi thêm: Bắt buộc upload ảnh

3. **Bảo mật:**
   - ✅ Đơn giản hóa cho demo/giáo dục: chỉ dùng `trim()` cho chuỗi
   - ✅ Type casting cho số: `(int)$_POST[...]`
   - ✅ Kiểm tra duplicate
   - ⚠️ Không validate file type (nên thêm)

4. **UI/UX:**
   - Form đầy đủ các trường
   - Auto-fill khi edit
   - Dropdown category dễ chọn

---

## 📊 TÓM TẮT

### Authentication
- **Login**: Xác thực admin, set session, redirect đến categories.php
- **Logout**: Xóa session, redirect về login.php
- **TopNav**: Navigation chung, tự động kiểm tra session

### Quản Lý Người Dùng
- **Users**: Xem danh sách users, xóa user

### Quản Lý Đơn Hàng
- **Orders**: Xem danh sách đơn hàng, cập nhật trạng thái, tự động tăng số lượng sách khi hủy/trả

### Quản Lý Danh Mục
- **Categories**: Xem danh sách categories, toggle status, xóa
- **ManageCategories**: Thêm mới hoặc sửa category, kiểm tra duplicate

### Quản Lý Sách
- **Books**: Xem danh sách sách, toggle status/best_seller, xóa
- **ManageBooks**: Thêm mới hoặc sửa sách, upload ảnh (chỉ khi thêm mới, giữ ảnh cũ khi edit)

### Điểm Chung

**Bảo mật:**
- ✅ Kiểm tra session ở tất cả trang (trừ login.php)
- ✅ Đơn giản hóa cho demo/giáo dục: chỉ dùng `trim()` để làm sạch input
- ✅ Type casting cho ID: `(int)$_GET['id']`
- ✅ Sử dụng `htmlspecialchars()` cho output (hầu hết các file)

**Pattern chung:**
- ✅ Xử lý logic (GET/POST) **TRƯỚC KHI** require topNav (để tránh lỗi "headers already sent")
- ✅ Kiểm tra Remember Me token và session trước khi xử lý logic
- ✅ Redirect sau khi xử lý thành công (với `exit` sau `header('Location: ...')`)
- ✅ Hiển thị bảng với các action (Edit, Delete, Toggle status)
- ✅ Giữ lại giá trị form khi có lỗi (tránh mất dữ liệu)

---

## 🔄 FLOW TỔNG QUAN

```
[Admin truy cập trang admin]
    ↓
[Kiểm tra session]
    ├─→ Có session → Cho phép truy cập
    └─→ Không có session → Redirect đến login.php
         ↓
    [Admin đăng nhập]
         ↓
    [Set session]
         ↓
    [Redirect đến categories.php]
         ↓
    [Admin làm việc...]
         ↓
    [Click Logout]
         ↓
    [Xóa session]
         ↓
    [Redirect về login.php]
```

---

## 📝 GHI CHÚ

- Tài liệu này đã giải thích đầy đủ:
  - ✅ Login và Logout
  - ✅ TopNav (Navigation)
  - ✅ Users (Quản lý người dùng)
  - ✅ Orders (Quản lý đơn hàng)
  - ✅ Categories (Quản lý danh mục)
  - ✅ ManageCategories (Thêm/Sửa danh mục)
  - ✅ Books (Quản lý sách)
  - ✅ ManageBooks (Thêm/Sửa sách)

---

**Tài liệu này giúp bạn hiểu rõ cách hoạt động của các phần chính trong hệ thống Admin. Chúc bạn code vui vẻ! 🚀**

