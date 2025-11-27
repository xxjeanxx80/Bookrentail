# 📖 HƯỚNG DẪN ĐỌC HIỂU CODE - PHẦN CUSTOMER

## 📋 MỤC LỤC
1. [Tổng Quan](#tổng-quan)
2. [Cấu Trúc File Customer](#cấu-trúc-file-customer)
3. [Flow Hoạt Động Của Customer](#flow-hoạt-động-của-customer)
4. [Chi Tiết Từng File](#chi-tiết-từng-file)
5. [Database Schema Liên Quan](#database-schema-liên-quan)
6. [Session Management](#session-management)
7. [Cookie Authentication (Remember Me)](#cookie-authentication-remember-me)
8. [Các Function Hỗ Trợ](#các-function-hỗ-trợ)

---

## 🎯 TỔNG QUAN

Hệ thống Book Rental cho phép khách hàng (Customer) thực hiện các chức năng:
- ✅ Đăng ký tài khoản mới
- ✅ Đăng nhập/Đăng xuất
- ✅ Xem danh sách sách (trang chủ, danh mục, tìm kiếm)
- ✅ Xem chi tiết sách
- ✅ Thuê sách (chọn thời gian thuê)
- ✅ Thanh toán và đặt hàng
- ✅ Xem lịch sử đơn hàng
- ✅ Hủy đơn hàng
- ✅ Cập nhật thông tin profile

---

## 📁 CẤU TRÚC FILE CUSTOMER

### Thư Mục Chính
```
Book-rental/
├── config/
│   └── connection.php          # Kết nối database và cấu hình
├── includes/
│   ├── header.php              # Header chung (navigation, CSS, JS)
│   ├── footer.php              # Footer chung
│   └── function.php            # Các function hỗ trợ
└── pages/
    ├── index.php               # Trang chủ
    ├── SignIn.php              # Đăng nhập
    ├── register.php            # Đăng ký
    ├── logout.php              # Đăng xuất
    ├── book.php                # Chi tiết sách
    ├── bookCategory.php        # Xem sách theo danh mục
    ├── search.php              # Tìm kiếm sách
    ├── checkout.php            # Thanh toán
    ├── thankYou.php            # Trang cảm ơn sau khi đặt hàng
    ├── myOrder.php             # Lịch sử đơn hàng
    └── profile.php             # Cập nhật profile
```

---

## 🔄 FLOW HOẠT ĐỘNG CỦA CUSTOMER

### 1. Flow Đăng Ký & Đăng Nhập

```
[Trang chủ/Header] 
    ↓
[Click "Login"] 
    ↓
[SignIn.php] 
    ↓ (Nếu chưa có tài khoản)
[register.php] 
    ↓ (Sau khi đăng ký thành công)
[SignIn.php] 
    ↓ (Sau khi đăng nhập thành công)
[Trang chủ hoặc Checkout (nếu có BeforeCheckoutLogin)]
```

### 2. Flow Thuê Sách

```
[Trang chủ (index.php)]
    ↓ (Click vào sách)
[book.php - Chi tiết sách]
    ↓ (Click nút "Rent" và nhập số ngày)
[book.php - Form thuê sách]
    ↓ (Submit form)
[checkout.php - Thanh toán]
    ↓ (Điền địa chỉ và thanh toán)
[thankYou.php - Xác nhận đơn hàng]
```

**Lưu ý:** Nếu chưa đăng nhập khi thuê sách:
```
[book.php] 
    ↓ (Click Rent)
[checkout.php] 
    ↓ (Kiểm tra session - chưa login)
[SignIn.php] 
    ↓ (Sau khi đăng nhập)
[checkout.php] (tự động quay lại)
```

### 3. Flow Xem Đơn Hàng

```
[Header - Click "My Orders"]
    ↓
[myOrder.php]
    ↓ (Có thể hủy đơn nếu chưa xử lý)
[myOrder.php?type=cancel&id=X]
```

### 4. Flow Cập Nhật Profile

```
[Header - Dropdown tên user - "Edit Profile"]
    ↓
[profile.php]
    ↓ (Cập nhật thông tin)
[profile.php] (Hiển thị thông báo thành công)
```

---

## 📄 CHI TIẾT TỪNG FILE

### 1. `config/connection.php`
**Mục đích:** File cấu hình kết nối database và các đường dẫn

**Nội dung chính:**
- Khởi động session nếu chưa có
- Kết nối MySQL database (`mini_project`)
- Định nghĩa các constant:
  - `SERVER_PATH`: Đường dẫn thực tế trên server
  - `SITE_PATH`: URL của website
  - `BOOK_IMAGE_SERVER_PATH`: Đường dẫn thư mục ảnh sách trên server
  - `BOOK_IMAGE_SITE_PATH`: URL ảnh sách

**Code quan trọng:**
```php
$con = mysqli_connect("localhost", "root", "", "mini_project");
define('SITE_PATH', 'http://localhost/ins3064/Book-rental/');
define('BOOK_IMAGE_SITE_PATH', SITE_PATH . 'assets/img/books/');
```

---

### 2. `includes/header.php`
**Mục đích:** Header chung cho tất cả trang customer

**Nội dung:**
- Include `connection.php` và `function.php`
- **Cookie Authentication Check:**
  ```php
  // Kiểm tra Remember Me token nếu chưa có session
  if (!isset($_SESSION['USER_LOGIN'])) {
      checkRememberToken($con); // Tự động đăng nhập nếu có cookie hợp lệ
  }
  ```
- HTML head (CSS, Bootstrap, Font Awesome)
- Navigation bar với:
  - Logo và menu (Home, Book Categories)
  - Thanh tìm kiếm
  - Menu user (nếu đã login): My Orders, Edit Profile, Logout
  - Nút Login (nếu chưa login)

**Logic hiển thị menu:**
```php
if (isset($_SESSION['USER_LOGIN'])) {
    // Hiển thị: Tên user (dropdown), My Orders
} else {
    // Hiển thị: Nút Login
}
```

**Session được sử dụng:**
- `$_SESSION['USER_LOGIN']`: Kiểm tra đã login chưa
- `$_SESSION['USER_NAME']`: Hiển thị tên user

**Cookie được sử dụng:**
- `remember_token`: Token để tự động đăng nhập (nếu có)
- Tự động check khi load trang nếu chưa có session
- Xem thêm phần [Cookie Authentication](#cookie-authentication-remember-me)

---

### 3. `includes/function.php`
**Mục đích:** Chứa các function hỗ trợ dùng chung

**Các function chính:**

**Lưu ý:** Để đơn giản hóa cho mục đích demo/giáo dục, code sử dụng `trim()` và `mysqli_real_escape_string()` trực tiếp thay vì hàm wrapper.

#### `getProduct($con, $limitCount, $categoryId, $bookId, $orderByClause)`
- **Mục đích:** Lấy danh sách sách từ database
- **Tham số:**
  - `$limitCount`: Số lượng sách (ví dụ: 4)
  - `$categoryId`: Lọc theo danh mục
  - `$bookId`: Lấy 1 sách cụ thể
  - `$orderByClause`: Sắp xếp (ví dụ: "id desc")
- **Trả về:** Mảng các sách

#### `getBook($con, $limitCount = 8)`
- **Mục đích:** Lấy sách bán chạy (best_seller = 1)
- **Dùng cho:** Hiển thị "Most Viewed" trên trang chủ

#### `searchBooks($con, $searchKeyword)`
- **Mục đích:** Tìm kiếm sách theo tên hoặc tác giả
- **SQL:** `WHERE name LIKE '%keyword%' OR author LIKE '%keyword%'`

#### Cookie Authentication Functions (Remember Me)
- **`generateToken()`**: Tạo token ngẫu nhiên 64 ký tự
- **`saveRememberToken($con, $userId)`**: Lưu token vào cookie và database
- **`checkRememberToken($con)`**: Kiểm tra cookie và tự động đăng nhập
- **`deleteRememberToken($con, $token)`**: Xóa token khỏi cookie và database
- **`deleteAllUserTokens($con, $userId)`**: Xóa tất cả token của user

**Xem chi tiết:** Phần [Cookie Authentication](#cookie-authentication-remember-me)

---

### 4. `pages/index.php` - Trang Chủ
**Mục đích:** Hiển thị trang chủ với carousel, sách mới, sách phổ biến

**Flow:**
1. Include `header.php`
2. Hiển thị carousel (3 slides)
3. **New Arrivals:** Lấy 4 sách mới nhất
   ```php
   $getProduct = getProduct($con, 4, '', '', 'id desc');
   ```
4. **Most Viewed:** Lấy 8 sách bán chạy
   ```php
   $getBook = getBook($con);
   ```
5. Mỗi sách hiển thị: Ảnh, Tên, Giá thuê/ngày
6. Click vào sách → Chuyển đến `book.php?id=X`

**Không cần đăng nhập** để xem trang chủ.

---

### 5. `pages/SignIn.php` - Đăng Nhập
**Mục đích:** Xử lý đăng nhập customer

**Flow:**
1. **Kiểm tra đã login:**
   ```php
   if (isset($_SESSION['USER_LOGIN'])) {
       header('Location: index.php');
       exit;
   }
   ```

2. **Xử lý form đăng nhập:**
   - Nhận `email` và `password` từ POST
   - Sử dụng `trim()` để loại bỏ khoảng trắng
   - Sử dụng `mysqli_real_escape_string()` để escape SQL
   - Hash password bằng MD5
   - Query database: `SELECT * FROM users WHERE email='...' AND password='...'`

3. **Nếu đăng nhập thành công:**
   - Set session:
     ```php
     $_SESSION['USER_LOGIN'] = 'yes';
     $_SESSION['USER_ID'] = $row['id'];
     $_SESSION['USER_NAME'] = $row['name'];
     ```
   - **Remember Me (Cookie):**
     - Kiểm tra user có tick "Remember Me" không
     - Nếu có → Gọi `saveRememberToken($con, $row['id'])` để lưu token vào cookie và database
     - Token có thời hạn 30 ngày
   - Redirect:
     - Nếu có `$_SESSION['BeforeCheckoutLogin']` → Redirect đến checkout
     - Ngược lại → Redirect đến `index.php`

4. **Nếu sai:** Hiển thị "Invalid Username/Password"

**Database:** 
- Bảng `users` (SELECT)
- Bảng `user_tokens` (INSERT - nếu chọn Remember Me)

**Lưu ý:** 
- Form có checkbox "Remember Me" để user chọn
- Nếu chọn Remember Me, token được lưu để tự động đăng nhập sau này
- Xem thêm phần [Cookie Authentication](#cookie-authentication-remember-me) để hiểu rõ hơn

---

### 6. `pages/register.php` - Đăng Ký
**Mục đích:** Đăng ký tài khoản customer mới

**Flow:**
1. **Kiểm tra đã login:** Nếu đã login → Redirect về trang chủ

2. **Validation:**
   - Tên: Chỉ chữ cái và khoảng trắng (`preg_match("/^[a-zA-Z-' ]*$/")`)
   - Email: Validate format (`filter_var($email, FILTER_VALIDATE_EMAIL)`)
   - Password: Không được rỗng
   - Mobile: Number (min: 1111111111, max: 9999999999)

3. **Kiểm tra email đã tồn tại:**
   ```php
   $check = mysqli_query($con, "SELECT id FROM users WHERE email='$email'");
   if (mysqli_num_rows($check) > 0) {
       $msg = "Email already exists. Please login";
   }
   ```

4. **Nếu hợp lệ:**
   - Hash password: `md5($password)`
   - Lấy ngày hiện tại: `date('Y-m-d H:i:s')`
   - Insert vào database:
     ```php
     INSERT INTO users(name, email, mobile, password, doj) 
     VALUES ('$name', '$email', '$mobile', '$passwordHash', '$doj')
     ```
   - Redirect đến `SignIn.php`

**Database:** Bảng `users` (INSERT)

---

### 7. `pages/book.php` - Chi Tiết Sách
**Mục đích:** Hiển thị thông tin chi tiết sách và form thuê sách

**Flow:**
1. **Lấy ID sách từ GET:**
   ```php
   $bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
   ```

2. **Lấy thông tin sách:**
   ```php
   $getProduct = getProduct($con, '', '', $bookId);
   ```

3. **Hiển thị thông tin:**
   - Ảnh sách
   - Tên, ISBN, Tác giả
   - Giá thuê/ngày
   - Mô tả ngắn và mô tả chi tiết (accordion)

4. **Kiểm tra số lượng:**
   - Nếu `qty = 0` → Hiển thị "Currently out of stock"
   - Nếu `qty > 0` → Hiển thị nút "Rent"

5. **Form thuê sách (JavaScript toggle):**
   - Click "Rent" → Hiển thị form
   - Nhập số ngày thuê (10-200 ngày)
   - Submit form → Chuyển đến `checkout.php?id=X&duration=Y`

6. **Xử lý submit:**
   ```php
   if (isset($_GET['submit'])) {
       $duration = (int)$_GET['duration'];
       $id = (int)$_GET['bookId'];
       if ($duration >= 10 && $duration <= 200) {
           $_SESSION['BeforeCheckoutLogin'] = "checkout.php?id=$id&duration=$duration";
           header("Location: checkout.php?id=$id&duration=$duration");
       }
   }
   ```

**Lưu ý:** 
- `$_SESSION['BeforeCheckoutLogin']` được set để nếu chưa login, sau khi login sẽ quay lại checkout
- Không cần đăng nhập để xem chi tiết sách, nhưng cần login để thuê

**Database:** Bảng `books` (SELECT)

---

### 8. `pages/checkout.php` - Thanh Toán
**Mục đích:** Xử lý thanh toán và tạo đơn hàng

**Flow:**
1. **Kiểm tra đăng nhập:**
   ```php
   if (!isset($_SESSION['USER_LOGIN'])) {
       header('Location: SignIn.php');
       exit;
   }
   ```

2. **Lấy thông tin từ GET:**
   ```php
   $bookId = (int)$_GET['id'];
   $duration = (int)$_GET['duration'];
   ```

3. **Lấy thông tin sách và tính toán:**
   ```php
   $bookData = getProduct($con, '', '', $bookId)[0];
   $totalRent = $bookData['rent'] * $duration;
   $totalPrice = $totalRent + $bookData['security'];
   ```

4. **Hiển thị form thanh toán:**
   - Thông tin sách: Tên, giá thuê, thời gian, tổng tiền
   - Form địa chỉ: Address Line 1, Address Line 2, Pin Code
   - Phương thức thanh toán: COD (mặc định), Online Payment (disabled)

5. **Xử lý submit (POST):**
   ```php
   if (isset($_POST['submit'])) {
       // Lấy dữ liệu form
       $address = trim($_POST['address']);
       $address2 = trim($_POST['address2'] ?? '');
       $pin = (int)$_POST['pin'];
       $paymentMethod = trim($_POST['paymentMethod']);
       
       // Escape cho SQL
       $address = mysqli_real_escape_string($con, $address);
       $address2 = mysqli_real_escape_string($con, $address2);
       $paymentMethod = mysqli_real_escape_string($con, $paymentMethod);
       $userId = (int)$_SESSION['USER_ID'];
       $paymentStatus = ($paymentMethod == 'COD') ? 'success' : 'pending';
       
       // Insert vào bảng orders
       INSERT INTO orders(user_id, address, address2, pin, payment_method, 
                         total, payment_status, order_status, date, duration)
       
       // Lấy order_id vừa tạo
       $orderId = mysqli_insert_id($con);
       
       // Insert vào bảng order_detail
       INSERT INTO order_detail(order_id, book_id, price, time)
       
       // Giảm số lượng sách
       UPDATE books SET qty = qty - 1 WHERE id = $bookId
       
       // Redirect đến thankYou.php
       header("Location: thankYou.php?orderId=$orderId");
   }
   ```

**Database:**
- `orders` (INSERT)
- `order_detail` (INSERT)
- `books` (UPDATE - giảm qty)

**Lưu ý:** 
- `order_status = 1` (Pending) khi tạo đơn
- `payment_status = 'success'` nếu COD, `'pending'` nếu online

---

### 9. `pages/thankYou.php` - Trang Cảm Ơn
**Mục đích:** Hiển thị xác nhận đơn hàng sau khi đặt thành công

**Flow:**
1. Lấy `orderId` từ GET
2. Hiển thị thông báo:
   - "Your order is Confirmed!"
   - Order ID
   - Cảm ơn user
   - Thông tin về email xác nhận

**Đơn giản, chỉ hiển thị thông tin.**

---

### 10. `pages/myOrder.php` - Lịch Sử Đơn Hàng
**Mục đích:** Hiển thị tất cả đơn hàng của customer và cho phép hủy đơn

**Flow:**
1. **Kiểm tra đăng nhập:**
   ```php
   if (!isset($_SESSION['USER_LOGIN'])) {
       header('Location: SignIn.php');
       exit;
   }
   ```

2. **Xử lý hủy đơn (nếu có):**
   ```php
   if (isset($_GET['type']) && $_GET['type'] == 'cancel') {
       $id = (int)$_GET['id'];
       // Cập nhật order_status = 4 (Cancelled)
       UPDATE orders SET order_status=4 WHERE id=$id
       
       // Tăng lại số lượng sách
       UPDATE books SET qty = qty + 1 WHERE id=...
   }
   ```

3. **Lấy danh sách đơn hàng:**
   ```php
   $userId = (int)$_SESSION['USER_ID'];
   SELECT orders.*, name, status_name FROM orders
   JOIN order_detail ON orders.id=order_detail.order_id
   JOIN books ON order_detail.book_id=books.id
   JOIN order_status ON orders.order_status=order_status.id
   WHERE user_id=$userId ORDER BY orders.id DESC
   ```

4. **Hiển thị bảng:**
   - OrderID, Order Date, Book Name, Price, Duration
   - Address, Payment Method, Payment Status, Order Status
   - Nút Cancel (chỉ hiển thị nếu status không phải Cancelled hoặc Returned)

**Database:**
- `orders` (SELECT, UPDATE)
- `order_detail` (SELECT)
- `books` (SELECT, UPDATE)
- `order_status` (SELECT)

---

### 11. `pages/profile.php` - Cập Nhật Profile
**Mục đích:** Cho phép customer cập nhật thông tin cá nhân

**Flow:**
1. **Kiểm tra đăng nhập**

2. **Lấy thông tin user hiện tại:**
   ```php
   $userId = (int)$_SESSION['USER_ID'];
   $res = mysqli_query($con, "SELECT * FROM users WHERE id=$userId");
   $row = mysqli_fetch_assoc($res);
   ```

3. **Auto-fill form** với thông tin hiện tại

4. **Validation (giống register.php):**
   - Tên: Chỉ chữ cái
   - Email: Format hợp lệ
   - Password: Phải nhập đúng password hiện tại để xác nhận

5. **Cập nhật:**
   ```php
   UPDATE users SET name='$name', email='$email', mobile='$mobile' 
   WHERE id=$userId
   ```

6. **Cập nhật session:**
   ```php
   $_SESSION['USER_NAME'] = $name;
   ```

**Database:** Bảng `users` (SELECT, UPDATE)

---

### 12. `pages/logout.php` - Đăng Xuất
**Mục đích:** Xóa session, cookie và đăng xuất hoàn toàn

**Flow:**
```php
require(__DIR__ . '/../config/connection.php');
require(__DIR__ . '/../includes/function.php');

session_start();

// Xóa token Remember Me nếu có
if (isset($_COOKIE['remember_token'])) {
    deleteRememberToken($con, $_COOKIE['remember_token']);
}

// Xóa session
unset($_SESSION['USER_LOGIN']);
unset($_SESSION['USER_ID']);
unset($_SESSION['USER_NAME']);
unset($_SESSION['BeforeCheckoutLogin']);

header('location:index.php');
die();
```

**Xử lý:**
1. Xóa token Remember Me khỏi cookie và database (nếu có)
2. Xóa tất cả session variables
3. Redirect về trang chủ

**Database:**
- Bảng `user_tokens` (DELETE - nếu có cookie)

**Lưu ý:** 
- Xóa cả cookie và session để đảm bảo user logout hoàn toàn
- Sau khi logout, user phải đăng nhập lại (kể cả có Remember Me trước đó)

---

### 13. `pages/bookCategory.php` - Xem Sách Theo Danh Mục
**Mục đích:** Hiển thị sách theo danh mục

**Flow:**
1. Lấy `categoryId` từ GET
2. Lấy danh sách danh mục từ database
3. Hiển thị sidebar với các danh mục
4. Lấy sách theo danh mục:
   ```php
   $getProduct = getProduct($con, '', $categoryId);
   ```
5. Hiển thị grid sách (giống trang chủ)

**Database:** 
- `categories` (SELECT)
- `books` (SELECT với filter category_id)

---

### 14. `pages/search.php` - Tìm Kiếm Sách
**Mục đích:** Tìm kiếm sách theo tên hoặc tác giả

**Flow:**
1. Lấy keyword từ GET: `$_GET['search']`
2. Gọi function: `searchBooks($con, $search)`
3. Hiển thị kết quả (giống trang chủ)

**Database:** Bảng `books` (SELECT với LIKE)

---

---

## 🗄️ DATABASE SCHEMA LIÊN QUAN

### Bảng `users`
Lưu thông tin customer

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID user (Primary Key, Auto Increment) |
| name | varchar(80) | Tên customer |
| email | varchar(50) | Email (Unique) |
| mobile | bigint(20) | Số điện thoại |
| doj | datetime | Ngày tham gia (Date of Join) |
| password | varchar(255) | Mật khẩu (MD5 hash) |

**Sử dụng trong:**
- `register.php`: INSERT
- `SignIn.php`: SELECT
- `profile.php`: SELECT, UPDATE

---

### Bảng `books`
Lưu thông tin sách

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID sách (Primary Key) |
| name | varchar(200) | Tên sách |
| author | varchar(100) | Tác giả |
| ISBN | varchar(20) | ISBN |
| category_id | int(11) | ID danh mục (Foreign Key) |
| rent | float | Giá thuê/ngày |
| security | float | Tiền đặt cọc |
| qty | int(11) | Số lượng còn lại |
| status | int(11) | Trạng thái (1 = active) |
| best_seller | int(11) | Bán chạy (1 = yes) |
| img | varchar(200) | Tên file ảnh |
| short_desc | text | Mô tả ngắn |
| description | text | Mô tả chi tiết |

**Sử dụng trong:**
- `index.php`: SELECT (New Arrivals, Most Viewed)
- `book.php`: SELECT (chi tiết)
- `bookCategory.php`: SELECT (theo danh mục)
- `search.php`: SELECT (tìm kiếm)
- `checkout.php`: SELECT, UPDATE (giảm qty)
- `myOrder.php`: SELECT, UPDATE (tăng qty khi hủy)

---

### Bảng `orders`
Lưu thông tin đơn hàng

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID đơn hàng (Primary Key, Auto Increment) |
| user_id | int(11) | ID customer (Foreign Key → users.id) |
| address | varchar(100) | Địa chỉ dòng 1 |
| address2 | varchar(100) | Địa chỉ dòng 2 |
| pin | int(11) | Mã pin code |
| payment_method | varchar(20) | Phương thức thanh toán (COD, payU) |
| total | int(11) | Tổng tiền (rent + security) |
| payment_status | varchar(20) | Trạng thái thanh toán (pending, success) |
| order_status | int(11) | Trạng thái đơn hàng (Foreign Key → order_status.id) |
| date | datetime | Ngày đặt hàng |
| duration | int(11) | Số ngày thuê |

**Sử dụng trong:**
- `checkout.php`: INSERT
- `myOrder.php`: SELECT, UPDATE (hủy đơn)

---

### Bảng `order_detail`
Lưu chi tiết đơn hàng (sách nào được thuê)

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID chi tiết (Primary Key, Auto Increment) |
| order_id | int(11) | ID đơn hàng (Foreign Key → orders.id) |
| book_id | int(11) | ID sách (Foreign Key → books.id) |
| price | float | Giá tại thời điểm đặt hàng |
| time | int(11) | Số ngày thuê |

**Sử dụng trong:**
- `checkout.php`: INSERT
- `myOrder.php`: SELECT (JOIN để lấy tên sách)

---

### Bảng `order_status`
Lưu các trạng thái đơn hàng

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID trạng thái (Primary Key) |
| status_name | varchar(15) | Tên trạng thái (Unique) |

**Các trạng thái:**
1. Pending (id=1) - Chờ xử lý
2. Processing (id=2) - Đang xử lý
3. Shipped (id=3) - Đã giao hàng
4. Cancelled (id=4) - Đã hủy
5. Delivered (id=5) - Đã nhận hàng
6. Returned (id=6) - Đã trả sách

**Sử dụng trong:**
- `myOrder.php`: SELECT (JOIN để hiển thị tên trạng thái)

---

---

### Bảng `user_tokens`
Lưu token cho tính năng Remember Me (Cookie Authentication)

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID (Primary Key, Auto Increment) |
| user_id | int(11) | ID của user (Foreign Key → users.id) |
| token | varchar(64) | Token ngẫu nhiên (Unique) |
| expires_at | datetime | Thời gian hết hạn (30 ngày sau khi tạo) |
| created_at | datetime | Thời gian tạo token |

**Sử dụng trong:**
- `SignIn.php`: INSERT (khi chọn Remember Me)
- `header.php`: SELECT (kiểm tra token để tự động đăng nhập)
- `logout.php`: DELETE (xóa token khi logout)

**Lưu ý:** Token tự động hết hạn sau 30 ngày. Có thể dọn dẹp token hết hạn định kỳ:
```sql
DELETE FROM user_tokens WHERE expires_at < NOW();
```

---

## 🔐 SESSION MANAGEMENT

### Các Session Variable

#### `$_SESSION['USER_LOGIN']`
- **Giá trị:** `'yes'` (string)
- **Set khi:** Đăng nhập thành công
- **Dùng để:** Kiểm tra customer đã đăng nhập chưa
- **Unset khi:** Logout

#### `$_SESSION['USER_ID']`
- **Giá trị:** ID của user (int)
- **Set khi:** Đăng nhập thành công
- **Dùng để:** 
  - Query database lấy thông tin user
  - Tạo đơn hàng (user_id)
  - Lấy danh sách đơn hàng của user

#### `$_SESSION['USER_NAME']`
- **Giá trị:** Tên của user (string)
- **Set khi:** Đăng nhập thành công, cập nhật profile
- **Dùng để:** Hiển thị tên user trên header

#### `$_SESSION['BeforeCheckoutLogin']`
- **Giá trị:** URL cần redirect sau khi login (string)
- **Set khi:** Click "Rent" trên `book.php` nhưng chưa login
- **Dùng để:** Sau khi login, tự động quay lại checkout
- **Unset khi:** Đăng nhập thành công hoặc logout

### Flow Session

```
[Chưa login] 
    ↓ (Click Rent trên book.php)
[Set $_SESSION['BeforeCheckoutLogin'] = "checkout.php?id=X&duration=Y"]
    ↓ (Redirect đến checkout.php)
[checkout.php kiểm tra session - chưa có USER_LOGIN]
    ↓ (Redirect đến SignIn.php)
[SignIn.php - Đăng nhập thành công]
    ↓ (Lấy $_SESSION['BeforeCheckoutLogin'])
[Redirect đến checkout.php]
    ↓ (Unset $_SESSION['BeforeCheckoutLogin'])
[checkout.php - Hiển thị form thanh toán]
```

---

## 🍪 COOKIE AUTHENTICATION (REMEMBER ME)

### Tổng Quan

Hệ thống sử dụng **2 cơ chế xác thực**:
1. **Session** - Đăng nhập tạm thời (hết hạn khi đóng trình duyệt)
2. **Cookie (Remember Me)** - Đăng nhập lâu dài (30 ngày)

### Sự Khác Biệt Giữa Session và Cookie

| Đặc điểm | Session | Cookie (Remember Me) |
|----------|---------|---------------------|
| **Lưu ở đâu** | Server (trên máy chủ) | Client (trên trình duyệt) |
| **Thời gian sống** | Đến khi đóng trình duyệt | 30 ngày (có thể tùy chỉnh) |
| **Khi nào hết hạn** | Đóng trình duyệt | Sau 30 ngày hoặc logout |
| **Mở tab mới** | ✅ Vẫn đăng nhập (cùng session) | ✅ Vẫn đăng nhập (cùng cookie) |
| **Đóng trình duyệt** | ❌ Mất đăng nhập | ✅ Vẫn đăng nhập (cookie còn) |
| **Mở lại sau vài ngày** | ❌ Phải đăng nhập lại | ✅ Tự động đăng nhập |

### Khi Nào Cookie Phát Huy Tác Dụng?

**Cookie Remember Me chỉ có tác dụng khi:**

1. ✅ **Đóng TẤT CẢ trình duyệt và mở lại**
   - Session đã mất
   - Cookie vẫn còn → Tự động đăng nhập

2. ✅ **Sau khi session hết hạn (thường sau 24-48h không dùng)**
   - Session đã hết hạn
   - Cookie vẫn còn → Tự động đăng nhập

3. ✅ **Mở trình duyệt khác (nhưng cùng domain)**
   - Session không chia sẻ giữa các trình duyệt
   - Cookie có thể chia sẻ (tùy cấu hình) → Tự động đăng nhập

**Cookie KHÔNG có tác dụng khi:**

1. ❌ **Mở tab mới trong cùng trình duyệt**
   - Session vẫn còn → Đã đăng nhập rồi
   - Cookie không cần thiết trong trường hợp này

2. ❌ **Chưa đóng trình duyệt**
   - Session vẫn còn → Đã đăng nhập rồi
   - Cookie chỉ là backup, chưa cần dùng

### Cơ Chế Hoạt Động

#### 1. Khi Đăng Nhập VỚI Remember Me

```
[User đăng nhập + tick "Remember Me"]
    ↓
[SignIn.php xử lý]
    ↓
[Tạo token ngẫu nhiên 64 ký tự]
    ↓
[Lưu vào 2 nơi:]
    ├─→ Cookie: remember_token (30 ngày)
    └─→ Database: user_tokens table
    ↓
[Set Session như bình thường]
    ↓
[User đã đăng nhập]
```

#### 2. Khi User Quay Lại Website

```
[User truy cập bất kỳ trang nào]
    ↓
[header.php được load]
    ↓
[Kiểm tra: Có session chưa?]
    ├─→ CÓ session → Bỏ qua, không check cookie
    └─→ CHƯA có session → Kiểm tra cookie
         ↓
    [Có cookie remember_token?]
         ├─→ CÓ → Tìm token trong database
         │        ↓
         │    [Token hợp lệ và chưa hết hạn?]
         │        ├─→ CÓ → Tự động set session → Đăng nhập
         │        └─→ KHÔNG → Xóa cookie
         └─→ KHÔNG → Không làm gì
```

#### 3. Khi Logout

```
[User click Logout]
    ↓
[logout.php xử lý]
    ↓
[Xóa token khỏi database]
    ↓
[Xóa cookie]
    ↓
[Xóa session]
    ↓
[User đã logout hoàn toàn]
```

### Database Schema

#### Bảng `user_tokens`

Lưu token cho tính năng Remember Me

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID (Primary Key, Auto Increment) |
| user_id | int(11) | ID của user (Foreign Key → users.id) |
| token | varchar(64) | Token ngẫu nhiên (Unique) |
| expires_at | datetime | Thời gian hết hạn (30 ngày sau khi tạo) |
| created_at | datetime | Thời gian tạo token |

**Sử dụng trong:**
- `SignIn.php`: INSERT (khi chọn Remember Me)
- `header.php`: SELECT (kiểm tra token)
- `logout.php`: DELETE (xóa token)

### Các Function Liên Quan

#### `generateToken()`
- **Mục đích:** Tạo token ngẫu nhiên 64 ký tự
- **Cách hoạt động:** `bin2hex(random_bytes(32))`
- **Dùng cho:** Tạo token mới khi đăng nhập với Remember Me

#### `saveRememberToken($con, $userId)`
- **Mục đích:** Lưu token vào cookie và database
- **Tham số:**
  - `$con`: Kết nối database
  - `$userId`: ID của user
- **Xử lý:**
  1. Tạo token mới
  2. Tính thời gian hết hạn (30 ngày)
  3. Lưu vào database
  4. Lưu vào cookie (30 ngày)
- **Dùng cho:** `SignIn.php` khi user chọn Remember Me

#### `checkRememberToken($con)`
- **Mục đích:** Kiểm tra cookie và tự động đăng nhập
- **Tham số:** `$con`: Kết nối database
- **Xử lý:**
  1. Kiểm tra đã có session chưa → Nếu có thì bỏ qua
  2. Kiểm tra có cookie `remember_token` không
  3. Tìm token trong database
  4. Kiểm tra token còn hạn (expires_at > NOW())
  5. Nếu hợp lệ → Set session tự động
  6. Nếu không hợp lệ → Xóa cookie
- **Dùng cho:** `header.php` (tự động gọi khi load trang)

#### `deleteRememberToken($con, $token)`
- **Mục đích:** Xóa token khỏi cookie và database
- **Tham số:**
  - `$con`: Kết nối database
  - `$token`: Token cần xóa
- **Xử lý:**
  1. Xóa token khỏi database
  2. Xóa cookie
- **Dùng cho:** `logout.php`

#### `deleteAllUserTokens($con, $userId)`
- **Mục đích:** Xóa tất cả token của user (khi đổi password)
- **Tham số:**
  - `$con`: Kết nối database
  - `$userId`: ID của user
- **Dùng cho:** Khi cần logout tất cả thiết bị của user

### Ví Dụ Thực Tế

#### Scenario 1: Đăng nhập KHÔNG Remember Me
```
1. User đăng nhập (không tick Remember Me)
2. Mở tab mới → ✅ Vẫn đăng nhập (session còn)
3. Đóng trình duyệt
4. Mở lại → ❌ Phải đăng nhập lại (session đã mất)
```

#### Scenario 2: Đăng nhập CÓ Remember Me
```
1. User đăng nhập (tick Remember Me)
2. Mở tab mới → ✅ Vẫn đăng nhập (session còn)
3. Đóng trình duyệt
4. Mở lại → ✅ Tự động đăng nhập (cookie tự động tạo session)
5. Sau 30 ngày → ❌ Phải đăng nhập lại (cookie hết hạn)
```

#### Scenario 3: Logout
```
1. User đăng nhập với Remember Me
2. Click Logout
3. Cookie và Session đều bị xóa
4. Mở lại → ❌ Phải đăng nhập lại
```

### Code Flow Chi Tiết

#### Trong `header.php`:

```php
// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['USER_LOGIN'])) {
    checkRememberToken($con); // Tự động đăng nhập nếu có cookie hợp lệ
}
```

**Giải thích:**
- Chỉ check cookie khi **chưa có session**
- Nếu có session rồi → Không cần check cookie
- Nếu có cookie hợp lệ → Tự động set session

#### Trong `SignIn.php`:

```php
if ($res && mysqli_num_rows($res) > 0) {
    // Set session
    $_SESSION['USER_LOGIN'] = 'yes';
    $_SESSION['USER_ID'] = $row['id'];
    $_SESSION['USER_NAME'] = $row['name'];
    
    // Nếu chọn Remember Me, lưu token
    if ($rememberMe) {
        saveRememberToken($con, $row['id']);
    }
}
```

**Giải thích:**
- Luôn set session khi đăng nhập thành công
- Chỉ lưu cookie nếu user chọn Remember Me
- Cookie là backup, session vẫn là chính

#### Trong `logout.php`:

```php
// Xóa token Remember Me nếu có
if (isset($_COOKIE['remember_token'])) {
    deleteRememberToken($con, $_COOKIE['remember_token']);
}

// Xóa session
unset($_SESSION['USER_LOGIN']);
unset($_SESSION['USER_ID']);
unset($_SESSION['USER_NAME']);
```

**Giải thích:**
- Xóa cả cookie và session khi logout
- Đảm bảo user logout hoàn toàn

### Lưu Ý Quan Trọng

1. **Session là chính, Cookie là phụ:**
   - Session luôn được ưu tiên
   - Cookie chỉ dùng khi session không có

2. **Cookie không thay thế Session:**
   - Cookie chỉ giúp tự động tạo session
   - Sau khi có session từ cookie, hệ thống dùng session như bình thường

3. **Bảo mật:**
   - Token ngẫu nhiên 64 ký tự (khó đoán)
   - Token có thời gian hết hạn
   - Cookie có flag HttpOnly (chống XSS)
   - Token lưu trong database (có thể xóa khi cần)

4. **Performance:**
   - Chỉ check cookie khi chưa có session
   - Không ảnh hưởng đến hiệu suất khi đã có session

### Tóm Tắt

**Cookie Remember Me giúp:**
- ✅ User không cần đăng nhập lại sau khi đóng trình duyệt
- ✅ Tự động đăng nhập khi quay lại website (trong 30 ngày)
- ✅ Trải nghiệm tốt hơn cho user

**Cookie Remember Me KHÔNG:**
- ❌ Thay thế Session (chỉ giúp tạo session tự động)
- ❌ Có tác dụng khi session còn (vì session được ưu tiên)
- ❌ Làm chậm website (chỉ check khi cần)

---

## 🛠️ CÁC FUNCTION HỖ TRỢ

**Lưu ý về bảo mật dữ liệu đầu vào:**

Để đơn giản hóa cho mục đích demo/giáo dục, code sử dụng:
- `trim()`: Loại bỏ khoảng trắng đầu/cuối
- `mysqli_real_escape_string()`: Escape SQL để chống SQL injection

**Ví dụ:**
```php
$email = trim($_POST['email']);
$email = mysqli_real_escape_string($con, $email);
```

**Dùng cho:** Tất cả dữ liệu từ `$_POST`, `$_GET` trước khi insert/update database

---

### `getProduct($con, $limitCount, $categoryId, $bookId, $orderByClause)`
**Mục đích:** Lấy danh sách sách

**Tham số:**
- `$limitCount`: Số lượng (ví dụ: 4) hoặc '' (không giới hạn)
- `$categoryId`: ID danh mục hoặc '' (tất cả)
- `$bookId`: ID sách cụ thể hoặc '' (tất cả)
- `$orderByClause`: Sắp xếp (ví dụ: "id desc") hoặc '' (không sắp xếp)

**SQL:**
```sql
SELECT * FROM books WHERE status = 1
[AND id = $bookId] (nếu có bookId)
[AND category_id = $categoryId] (nếu có categoryId và không có bookId)
[ORDER BY $orderByClause] (nếu có)
[LIMIT $limitCount] (nếu có)
```

**Ví dụ sử dụng:**
- Trang chủ - New Arrivals: `getProduct($con, 4, '', '', 'id desc')`
- Chi tiết sách: `getProduct($con, '', '', $bookId)`
- Sách theo danh mục: `getProduct($con, '', $categoryId)`

---

### `getBook($con, $limitCount = 8)`
**Mục đích:** Lấy sách bán chạy (best_seller = 1)

**SQL:**
```sql
SELECT * FROM books WHERE best_seller = 1 AND status = 1 LIMIT $limitCount
```

**Dùng cho:** Trang chủ - Most Viewed

---

### `searchBooks($con, $searchKeyword)`
**Mục đích:** Tìm kiếm sách theo tên hoặc tác giả

**SQL:**
```sql
SELECT * FROM books WHERE status = 1 
AND (name LIKE '%$keyword%' OR author LIKE '%$keyword%')
```

**Dùng cho:** Trang search.php

---

## 📊 SƠ ĐỒ FLOW TỔNG QUAN

```
┌─────────────────┐
│   index.php     │ (Trang chủ - Xem sách)
└────────┬────────┘
         │
         ├──→ book.php (Chi tiết sách)
         │         │
         │         ├──→ checkout.php (Thanh toán)
         │         │         │
         │         │         └──→ thankYou.php (Xác nhận)
         │         │
         │         └──→ SignIn.php (Nếu chưa login)
         │
         ├──→ bookCategory.php (Xem theo danh mục)
         │         │
         │         └──→ book.php
         │
         ├──→ search.php (Tìm kiếm)
         │         │
         │         └──→ book.php
         │
         ├──→ SignIn.php (Đăng nhập)
         │         │
         │         ├──→ register.php (Đăng ký)
         │         │         │
         │         │         └──→ SignIn.php
         │         │
         │         └──→ index.php hoặc checkout.php
         │
         ├──→ myOrder.php (Xem đơn hàng - Cần login)
         │         │
         │         └──→ myOrder.php?type=cancel&id=X (Hủy đơn)
         │
         ├──→ profile.php (Cập nhật profile - Cần login)
         │
         └──→ logout.php (Đăng xuất)
                 │
                 └──→ index.php
```

---

## 🔍 CÁC ĐIỂM QUAN TRỌNG CẦN LƯU Ý

### 1. Bảo Mật
- ✅ Sử dụng `trim()` và `mysqli_real_escape_string()` cho tất cả input
- ✅ Type casting cho ID: `(int)$_GET['id']`
- ✅ Kiểm tra session trước khi truy cập trang cần login
- ⚠️ **Lưu ý:** Password được hash bằng MD5 (không an toàn, nên dùng password_hash)

### 2. Validation
- ✅ Validate email format
- ✅ Validate tên (chỉ chữ cái)
- ✅ Validate số ngày thuê (10-200)
- ✅ Kiểm tra email đã tồn tại khi đăng ký
- ✅ Kiểm tra số lượng sách trước khi thuê

### 3. Session
- ✅ Kiểm tra `USER_LOGIN` trước khi truy cập trang cần login
- ✅ Set `BeforeCheckoutLogin` để quay lại checkout sau khi login
- ✅ Unset session khi logout

### 4. Database
- ✅ Sử dụng JOIN để lấy dữ liệu từ nhiều bảng
- ✅ Giảm/tăng số lượng sách khi đặt/hủy đơn
- ✅ Sử dụng `mysqli_insert_id()` để lấy ID vừa insert

### 5. User Experience
- ✅ Auto-fill form nếu đã login (profile)
- ✅ Redirect sau khi đăng nhập/đăng ký
- ✅ Hiển thị thông báo lỗi/thành công
- ✅ Disable nút "Rent" nếu hết hàng

---

## 📝 TÓM TẮT

### File Quan Trọng Nhất
1. **`config/connection.php`**: Kết nối database
2. **`includes/header.php`**: Navigation và layout chung
3. **`includes/function.php`**: Các function hỗ trợ
4. **`pages/SignIn.php`**: Xử lý đăng nhập
5. **`pages/checkout.php`**: Xử lý thanh toán và tạo đơn hàng
6. **`pages/myOrder.php`**: Quản lý đơn hàng

### Flow Quan Trọng Nhất
**Thuê sách:** `index.php` → `book.php` → `checkout.php` → `thankYou.php`

### Database Quan Trọng Nhất
- `users`: Thông tin customer
- `books`: Thông tin sách
- `orders`: Đơn hàng
- `order_detail`: Chi tiết đơn hàng

---

**Tài liệu này giúp bạn hiểu rõ cách hoạt động của phần Customer trong hệ thống Book Rental. Chúc bạn code vui vẻ! 🚀**

