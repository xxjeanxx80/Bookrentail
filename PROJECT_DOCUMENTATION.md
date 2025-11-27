# 📚 TÀI LIỆU DỰ ÁN BOOK RENTAL SYSTEM

## 📋 MỤC LỤC
1. [Tổng Quan Dự Án](#tổng-quan-dự-án)
2. [Các Actor (Người Dùng)](#các-actor-người-dùng)
3. [Flow (Luồng Hoạt Động)](#flow-luồng-hoạt-động)
4. [Các Function Chính](#các-function-chính)
5. [Cấu Trúc Database](#cấu-trúc-database)
6. [Các Trang Chính](#các-trang-chính)
7. [Tính Năng Đặc Biệt](#tính-năng-đặc-biệt)
8. [Cấu Trúc Thư Mục](#cấu-trúc-thư-mục)

---

## 🎯 TỔNG QUAN DỰ ÁN

**Book Rental System** là một hệ thống cho thuê sách trực tuyến được xây dựng bằng:
- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5

Hệ thống cho phép người dùng thuê sách trực tuyến và quản trị viên quản lý toàn bộ hoạt động của hệ thống.

---

## 👥 CÁC ACTOR (NGƯỜI DÙNG)

Dự án có **2 actor chính**:

### 1. 👤 User (Khách hàng)

**Quyền hạn:**
- ✅ Đăng ký tài khoản mới
- ✅ Đăng nhập/Đăng xuất
- ✅ Xem danh sách sách (sách mới, sách phổ biến)
- ✅ Tìm kiếm sách theo tên hoặc tác giả
- ✅ Xem sách theo danh mục
- ✅ Xem chi tiết sách (mô tả, giá, số lượng)
- ✅ Thuê sách (chọn thời gian thuê từ 10-200 ngày)
- ✅ Thanh toán (hiện tại chỉ hỗ trợ COD - Cash on Delivery)
- ✅ Xem lịch sử đơn hàng của mình
- ✅ Hủy đơn hàng (nếu chưa được xử lý)
- ✅ Cập nhật thông tin profile
- ✅ Gửi phản hồi/Liên hệ

**Các trang chính:**
- Trang chủ, Đăng ký, Đăng nhập
- Xem sách, Chi tiết sách
- Thanh toán, Đơn hàng của tôi
- Profile, Liên hệ

---

### 2. 🔐 Admin (Quản trị viên)

**Quyền hạn:**
- ✅ Đăng nhập vào hệ thống quản trị
- ✅ Quản lý sách:
  - Thêm sách mới
  - Sửa thông tin sách
  - Xem danh sách sách
  - Quản lý số lượng sách
- ✅ Quản lý danh mục sách:
  - Thêm danh mục mới
  - Sửa danh mục
  - Xóa/Ẩn danh mục
- ✅ Quản lý đơn hàng:
  - Xem tất cả đơn hàng
  - Cập nhật trạng thái đơn hàng
  - Theo dõi quá trình giao hàng
- ✅ Quản lý người dùng:
  - Xem danh sách người dùng
  - Xem thông tin chi tiết người dùng
- ✅ Xem phản hồi từ khách hàng
- ✅ Quản lý ngày trả sách

**Các trang chính:**
- Đăng nhập Admin
- Quản lý Sách, Danh mục
- Quản lý Đơn hàng, Người dùng
- Phản hồi, Ngày trả sách

---

## 🔄 FLOW (LUỒNG HOẠT ĐỘNG)

### 📱 Flow cho User (Khách hàng)

```
┌─────────────────────────────────────────────────────────────┐
│ 1. TRUY CẬP TRANG CHỦ (index.php)                            │
│    - Xem carousel giới thiệu                                  │
│    - Xem sách mới (New Arrivals)                             │
│    - Xem sách phổ biến (Most Viewed)                         │
└──────────────────────┬────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. TÌM KIẾM / XEM SÁCH                                       │
│    - Tìm kiếm theo tên sách hoặc tác giả (search.php)       │
│    - Xem sách theo danh mục (bookCategory.php)               │
└──────────────────────┬────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 3. XEM CHI TIẾT SÁCH (book.php)                              │
│    - Xem thông tin: ISBN, Tác giả, Giá thuê, Mô tả          │
│    - Kiểm tra số lượng còn lại                               │
│    - Nếu hết hàng → Hiển thị "out of stock"                  │
│    - Nếu còn hàng → Hiển thị nút "Rent"                      │
└──────────────────────┬────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 4. CHỌN THUÊ SÁCH                                            │
│    - Click nút "Rent"                                        │
│    - Nhập số ngày thuê (10-200 ngày)                         │
│    - Click "Rent" để xác nhận                                │
└──────────────────────┬────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 5. KIỂM TRA ĐĂNG NHẬP                                        │
│    - Nếu CHƯA đăng nhập:                                     │
│      → Lưu thông tin checkout vào session                    │
│      → Chuyển đến SignIn.php                                 │
│      → Sau khi đăng nhập → Tự động chuyển đến checkout       │
│    - Nếu ĐÃ đăng nhập:                                       │
│      → Chuyển trực tiếp đến checkout.php                     │
└──────────────────────┬────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 6. THANH TOÁN (checkout.php)                                 │
│    - Xem lại thông tin sách:                                 │
│      • Tên sách, Giá thuê/ngày, Số ngày thuê                 │
│      • Tổng tiền thuê, Tiền cọc (Security Deposit)          │
│      • Tổng thanh toán                                       │
│    - Nhập địa chỉ giao hàng:                                 │
│      • Address Line 1 (bắt buộc)                             │
│      • Address Line 2 (tùy chọn)                             │
│      • Pin Code (bắt buộc)                                   │
│    - Chọn phương thức thanh toán:                            │
│      • COD (Cash on Delivery) - Hiện tại chỉ hỗ trợ này      │
│      • Online Payment (đang bị disable)                      │
│    - Click "Place Your Order"                                │
└──────────────────────┬────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 7. XỬ LÝ ĐẶT HÀNG                                            │
│    - Tạo đơn hàng mới trong bảng orders                     │
│    - Tạo chi tiết đơn hàng trong bảng order_detail          │
│    - Giảm số lượng sách (qty - 1)                           │
│    - Chuyển đến thankYou.php                                 │
└──────────────────────┬────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 8. XEM ĐƠN HÀNG (myOrder.php)                                │
│    - Xem tất cả đơn hàng của mình                            │
│    - Thông tin: OrderID, Ngày đặt, Tên sách, Giá,           │
│      Thời gian thuê, Địa chỉ, Trạng thái thanh toán,        │
│      Trạng thái đơn hàng                                     │
│    - Có thể hủy đơn nếu:                                     │
│      • Trạng thái = Pending, Processing, hoặc Shipped       │
│    - Không thể hủy nếu:                                      │
│      • Trạng thái = Delivered, Returned, hoặc Cancelled      │
└─────────────────────────────────────────────────────────────┘
```

### 🔐 Flow cho Admin

```
┌─────────────────────────────────────────────────────────────┐
│ 1. ĐĂNG NHẬP ADMIN (Admin/login.php)                        │
│    - Nhập email và password                                  │
│    - Xác thực với bảng admin                                 │
│    - Lưu session ADMIN_LOGIN và ADMIN_email                   │
└──────────────────────┬────────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ 2. DASHBOARD ADMIN                                           │
│    - Sau khi đăng nhập → Chuyển đến categories.php           │
│    - Menu điều hướng:                                        │
│      • Categories (Danh mục)                                 │
│      • Books (Sách)                                          │
│      • Orders (Đơn hàng)                                     │
│      • Users (Người dùng)                                    │
│      • Feedback (Phản hồi)                                    │
│      • Return Date (Ngày trả sách)                           │
└──────────────────────┬────────────────────────────────────────┘
                       │
        ┌──────────────┴──────────────┐
        │                             │
        ▼                             ▼
┌──────────────────┐        ┌──────────────────┐
│ QUẢN LÝ SÁCH     │        │ QUẢN LÝ ĐƠN HÀNG│
│                  │        │                  │
│ • Xem danh sách  │        │ • Xem tất cả     │
│ • Thêm sách mới  │        │   đơn hàng       │
│ • Sửa thông tin  │        │ • Cập nhật       │
│ • Upload ảnh     │        │   trạng thái:    │
│ • Quản lý số     │        │   - Pending      │
│   lượng          │        │   - Processing   │
└──────────────────┘        │   - Shipped      │
                            │   - Delivered    │
                            │   - Returned     │
                            │   - Cancelled    │
                            │ • Tự động cập    │
                            │   nhật số lượng  │
                            │   khi trả/hủy    │
                            └──────────────────┘
```

---

## ⚙️ CÁC FUNCTION CHÍNH

### 📄 Functions trong `function.php` (User Side)

#### 1. `getSafeValue($con, $str)`
**Mục đích:** Làm sạch và bảo mật dữ liệu đầu vào từ form

**Tham số:**
- `$con`: Kết nối database
- `$str`: Chuỗi cần làm sạch

**Chức năng:**
- Kiểm tra nếu rỗng → return empty string
- Loại bỏ khoảng trắng thừa (`trim`)
- Loại bỏ backslashes (`stripslashes`)
- Chuyển đổi ký tự đặc biệt thành HTML entities (`htmlspecialchars`)
- Escape string để tránh SQL injection (`mysqli_real_escape_string`)

**Ví dụ sử dụng:**
```php
$email = getSafeValue($con, $_POST['email']);
$password = getSafeValue($con, $_POST['password']);
```

**✅ Đã cải thiện:** Luôn return string (không return null nếu rỗng)

---

#### 2. `getProduct($con, $limit = '', $categoryId = '', $bookId = '', $orderBy = '')`
**Mục đích:** Lấy danh sách sách từ database với các điều kiện lọc

**Tham số:**
- `$con`: Kết nối database
- `$limit`: Số lượng sách muốn lấy (ví dụ: 4, 8)
- `$categoryId`: ID danh mục để lọc
- `$bookId`: ID sách cụ thể (ưu tiên cao hơn categoryId)
- `$orderBy`: Cách sắp xếp (ví dụ: 'id desc', 'name asc')

**Trả về:** Mảng chứa thông tin các sách (empty array nếu lỗi)

**Ví dụ sử dụng:**
```php
// Lấy 4 sách mới nhất
$getProduct = getProduct($con, 4, '', '', 'id desc');

// Lấy sách theo danh mục
$getProduct = getProduct($con, '', 7, '', '');

// Lấy chi tiết 1 sách
$getProduct = getProduct($con, '', '', 5, '');
```

**✅ Đã cải thiện:**
- Validate input bằng `(int)` để tránh SQL injection
- Ưu tiên `bookId` nếu có (bỏ qua `categoryId`)
- Error handling: check query result và return empty array nếu lỗi
- Sử dụng `[]` thay vì `array()`

---

#### 3. `getBook($con, $limit = 8)`
**Mục đích:** Lấy sách bán chạy (best seller)

**Tham số:**
- `$con`: Kết nối database
- `$limit`: Số lượng sách muốn lấy (mặc định: 8)

**Trả về:** Mảng chứa thông tin các sách bán chạy

**Ví dụ sử dụng:**
```php
$getBook = getBook($con); // Lấy 8 sách bán chạy
$getBook = getBook($con, 10); // Lấy 10 sách bán chạy
```

**✅ Mới thêm:** Function này được di chuyển từ inline code trong `index.php` và `search.php`

---

#### 4. `searchBooks($con, $keyword)`
**Mục đích:** Tìm kiếm sách theo tên hoặc tác giả

**Tham số:**
- `$con`: Kết nối database
- `$keyword`: Từ khóa tìm kiếm

**Trả về:** Mảng chứa thông tin các sách khớp

**Ví dụ sử dụng:**
```php
$results = searchBooks($con, 'Harry Potter');
```

**✅ Mới thêm:** Function này được tạo để thay thế inline code trong `search.php`

---

### 📄 Functions trong `Admin/function.php` (Admin Side)

#### 1. `pr($arr)`
**Mục đích:** In mảng để debug (không dừng script)

**Ví dụ:**
```php
$data = ['name' => 'John', 'age' => 30];
pr($data);
// Output: Array ( [name] => John [age] => 30 )
```

---

#### 2. `prx($arr)`
**Mục đích:** In mảng và dừng script (để debug)

**Ví dụ:**
```php
$data = ['name' => 'John'];
prx($data);
// Output: Array ( [name] => John )
// Script dừng tại đây
```

**✅ Đã cải thiện:** Thêm đóng tag `</pre>` để format đúng

---

#### 3. `getSafeValue($con, $str)`
**Mục đích:** Làm sạch và bảo mật dữ liệu đầu vào (phiên bản đơn giản cho admin)

**Chức năng:**
- Kiểm tra nếu rỗng → return empty string
- Loại bỏ khoảng trắng thừa (`trim`)
- Escape string để tránh SQL injection (`mysqli_real_escape_string`)

**✅ Đã cải thiện:** Luôn return string (không return null nếu rỗng)

---

## 🗄️ CẤU TRÚC DATABASE

### Bảng `users`
Lưu thông tin người dùng

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID người dùng (Primary Key, Auto Increment) |
| name | varchar(80) | Tên người dùng |
| email | varchar(50) | Email (unique) |
| mobile | bigint(20) | Số điện thoại |
| doj | datetime | Ngày tham gia (Date of Join) |
| password | varchar(255) | Mật khẩu (đã hash MD5) |

---

### Bảng `admin`
Lưu thông tin quản trị viên

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID admin (Primary Key, Auto Increment) |
| email | varchar(50) | Email admin |
| password | varchar(255) | Mật khẩu (lưu plain text - không an toàn) |

**Lưu ý:** Mật khẩu admin đang lưu plain text, nên cải thiện bảo mật.

---

### Bảng `books`
Lưu thông tin sách

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID sách (Primary Key, Auto Increment) |
| category_id | int(11) | ID danh mục (Foreign Key) |
| ISBN | varchar(20) | Mã ISBN của sách |
| name | varchar(255) | Tên sách |
| img | varchar(255) | Tên file ảnh |
| author | varchar(75) | Tác giả |
| vnd | int(11) | Giá VND (có vẻ không dùng) |
| security | int(11) | Tiền cọc (Security Deposit) |
| rent | int(11) | Giá thuê mỗi ngày |
| qty | int(11) | Số lượng còn lại |
| best_seller | int(11) | Có phải sách bán chạy không (1 = có, 0 = không) |
| short_desc | varchar(2000) | Mô tả ngắn |
| description | text | Mô tả chi tiết |
| status | tinyint(4) | Trạng thái (1 = active, 0 = inactive) |
| price | int(3) | Giá tổng (Virtual Column = security + rent) |

---

### Bảng `categories`
Lưu danh mục sách

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | mediumint(9) | ID danh mục (Primary Key, Auto Increment) |
| category | varchar(50) | Tên danh mục |
| status | tinyint(4) | Trạng thái (1 = active, 0 = inactive) |

**Ví dụ danh mục:**
- Computing, Internet & Digital Media
- Action & Adventure
- Business & Economics
- Biographies, Diaries & True Accounts
- Romance
- etc.

---

### Bảng `orders`
Lưu thông tin đơn hàng

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID đơn hàng (Primary Key, Auto Increment) |
| user_id | int(11) | ID người dùng (Foreign Key) |
| address | varchar(100) | Địa chỉ dòng 1 |
| address2 | varchar(100) | Địa chỉ dòng 2 (tùy chọn) |
| pin | int(11) | Mã pin code |
| payment_method | varchar(20) | Phương thức thanh toán (COD, payU) |
| total | int(11) | Tổng tiền (rent + security) |
| payment_status | varchar(20) | Trạng thái thanh toán (pending, success) |
| order_status | int(11) | Trạng thái đơn hàng (Foreign Key → order_status.id) |
| date | datetime | Ngày đặt hàng |
| duration | int(11) | Số ngày thuê |

---

### Bảng `order_detail`
Lưu chi tiết đơn hàng (sách nào được thuê)

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID chi tiết (Primary Key, Auto Increment) |
| order_id | int(11) | ID đơn hàng (Foreign Key) |
| book_id | int(11) | ID sách (Foreign Key) |
| price | float | Giá tại thời điểm đặt hàng |
| time | int(11) | Số ngày thuê |

---

### Bảng `order_status`
Lưu các trạng thái đơn hàng

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID trạng thái (Primary Key, Auto Increment) |
| status_name | varchar(15) | Tên trạng thái (Unique) |

**Các trạng thái:**
1. **Pending** - Chờ xử lý
2. **Processing** - Đang xử lý
3. **Shipped** - Đã giao hàng
4. **Delivered** - Đã nhận hàng
5. **Returned** - Đã trả sách
6. **Cancelled** - Đã hủy

---

### Bảng `contact_us`
Lưu phản hồi từ khách hàng

| Cột | Kiểu | Mô tả |
|-----|------|-------|
| id | int(11) | ID phản hồi (Primary Key, Auto Increment) |
| name | varchar(70) | Tên người gửi |
| email | varchar(70) | Email người gửi |
| mobile | bigint(10) | Số điện thoại |
| message | text | Nội dung phản hồi |
| date | datetime | Ngày gửi |

---

## 📄 CÁC TRANG CHÍNH

### 👤 User Pages

#### 1. `index.php` - Trang Chủ
- Hiển thị carousel giới thiệu
- Hiển thị 4 sách mới nhất (New Arrivals)
- Hiển thị 8 sách phổ biến nhất (Most Viewed - best_seller = 1)
- Mỗi sách hiển thị: Ảnh, Tên, Giá thuê/ngày

---

#### 2. `SignIn.php` - Đăng Nhập
- Form đăng nhập với email và password
- Xác thực với bảng `users`
- Lưu session: `USER_LOGIN`, `USER_ID`, `USER_NAME`
- Nếu có `BeforeCheckoutLogin` trong session → Chuyển đến checkout sau khi đăng nhập
- Link đến trang đăng ký

**✅ Đã cải thiện:**
- Sử dụng PHP `header()` redirect thay vì JavaScript (tốt hơn cho SEO)
- Đơn giản hóa login logic
- Sử dụng null coalescing operator `??` cho redirect

---

#### 3. `register.php` - Đăng Ký
- Form đăng ký với:
  - Tên (chỉ chữ cái và khoảng trắng)
  - Email (validate format)
  - Số điện thoại
  - Mật khẩu
- Kiểm tra email đã tồn tại chưa
- Hash password bằng MD5
- Lưu ngày tham gia (doj)
- Sau khi đăng ký thành công → Chuyển đến SignIn.php

**✅ Đã cải thiện:**
- Đơn giản hóa validation code (giảm ~34% số dòng)
- Sử dụng `elseif` chain thay vì nested if
- Sử dụng null coalescing `??` cho POST data
- Sử dụng PHP `header()` redirect thay vì JavaScript

---

#### 4. `book.php` - Chi Tiết Sách
- Hiển thị thông tin chi tiết:
  - Ảnh sách
  - Tên sách, ISBN, Tác giả
  - Giá thuê/ngày
  - Mô tả ngắn và mô tả chi tiết (accordion)
- Kiểm tra số lượng:
  - Nếu `qty = 0` → Hiển thị "Currently out of stock"
  - Nếu `qty > 0` → Hiển thị nút "Rent"
- Form thuê sách:
  - Nhập số ngày thuê (10-200 ngày)
  - Submit → Chuyển đến checkout.php

**✅ Đã cải thiện:**
- Đơn giản hóa GET parameter handling
- Xóa query qty thừa (đã có trong getProduct)
- Sử dụng short syntax `if/else:` cho dễ đọc
- Thêm validation cho duration (10-200 days)
- Sử dụng PHP `header()` redirect thay vì JavaScript

---

#### 5. `bookCategory.php` - Xem Sách Theo Danh Mục
- Hiển thị tất cả danh mục (status = 1)
- Click vào danh mục → Xem tất cả sách trong danh mục đó
- Sử dụng `getProduct($con, '', $categoryId, '', '')`

---

#### 6. `checkout.php` - Thanh Toán
**Yêu cầu:** Phải đăng nhập

**Hiển thị:**
- Bên trái: Form nhập địa chỉ
  - Address Line 1 (bắt buộc)
  - Address Line 2 (tùy chọn)
  - Pin Code (bắt buộc)
  - Phương thức thanh toán (COD hoặc Online - hiện chỉ COD hoạt động)

- Bên phải: Thông tin đơn hàng
  - Tên sách
  - Giá thuê/ngày
  - Số ngày thuê
  - Tổng tiền thuê
  - Tiền cọc (Security Deposit)
  - Tổng thanh toán
  - Điều khoản về tiền cọc

**Xử lý khi submit:**
1. Tạo đơn hàng mới trong `orders`
2. Tạo chi tiết đơn hàng trong `order_detail`
3. Giảm số lượng sách (`qty - 1`)
4. Chuyển đến `thankYou.php`

**✅ Đã cải thiện:**
- Thêm validation cho bookId và duration
- Đơn giản hóa xử lý order (giảm ~20% số dòng)
- Sử dụng biến `$book` thay vì `$getProduct[0]` cho dễ đọc
- Sử dụng PHP `header()` redirect thay vì JavaScript
- Validate input bằng `(int)` cho số
- Sử dụng toán tử ternary cho payment status

---

#### 7. `myOrder.php` - Đơn Hàng Của Tôi
**Yêu cầu:** Phải đăng nhập

**Hiển thị:**
- Bảng danh sách đơn hàng của user hiện tại
- Thông tin: OrderID, Ngày đặt, Tên sách, Giá, Thời gian thuê, Địa chỉ, Phương thức thanh toán, Trạng thái thanh toán, Trạng thái đơn hàng
- Nút "Cancel" nếu đơn hàng chưa được giao/trả/hủy

**Xử lý hủy đơn:**
- Cập nhật `order_status = 4` (Cancelled)
- Tăng lại số lượng sách (`qty + 1`)

**✅ Đã cải thiện:**
- Đơn giản hóa cancel logic (giảm ~11% số dòng)
- Sử dụng short syntax `while(): endwhile;`
- Sử dụng `in_array()` thay vì multiple `===` checks
- Sử dụng PHP `header()` redirect
- Cải thiện SQL query formatting

---

#### 8. `profile.php` - Cập Nhật Profile
**Yêu cầu:** Phải đăng nhập

- Form cập nhật thông tin: Tên, Email, Số điện thoại, Mật khẩu (để xác thực)
- Validation tương tự register.php
- Cập nhật session ngay sau khi update thành công

**✅ Đã cải thiện:**
- Đơn giản hóa validation code (giảm ~28% số dòng)
- Cập nhật session ngay sau khi update
- Refresh form data sau khi update
- Cải thiện error messages

---

#### 9. `search.php` - Tìm Kiếm
- Tìm kiếm sách theo tên hoặc tác giả
- Sử dụng function `searchBooks()` từ `function.php`
- Hiển thị kết quả tìm kiếm

**✅ Đã cải thiện:**
- Di chuyển logic tìm kiếm vào function `searchBooks()`
- Đơn giản hóa code (giảm ~64% số dòng)
- Sử dụng `isset()` để check GET parameter

---

#### 10. `contactUs.php` - Liên Hệ
- Form gửi phản hồi:
  - Tên, Email, Số điện thoại, Nội dung
- Lưu vào bảng `contact_us`
- Tự động điền thông tin nếu user đã đăng nhập

**✅ Đã cải thiện:**
- Đơn giản hóa auto-fill logic (giảm ~13% số dòng)
- Sử dụng ternary operator cho message
- Cải thiện SQL query formatting

---

#### 11. `aboutUs.php` - Giới Thiệu
- Trang giới thiệu về hệ thống

---

#### 12. `termsAndCondition.php` - Điều Khoản
- Trang điều khoản sử dụng

---

#### 13. `thankYou.php` - Cảm Ơn
- Hiển thị sau khi đặt hàng thành công
- Hiển thị OrderID

---

#### 14. `logout.php` - Đăng Xuất
- Hủy session `USER_LOGIN`, `USER_ID`, `USER_NAME`
- Chuyển về trang chủ

---

### 🔐 Admin Pages

#### 1. `Admin/login.php` - Đăng Nhập Admin
- Form đăng nhập với email và password
- Xác thực với bảng `admin`
- Lưu session: `ADMIN_LOGIN`, `ADMIN_email`
- Sau khi đăng nhập → Chuyển đến `categories.php`

---

#### 2. `Admin/categories.php` - Danh Sách Danh Mục
- Hiển thị bảng tất cả danh mục
- Có nút "Add" để thêm danh mục mới
- Có nút "Edit" để sửa danh mục

---

#### 3. `Admin/manageCategories.php` - Thêm/Sửa Danh Mục
- Form thêm hoặc sửa danh mục
- Thông tin: Tên danh mục, Trạng thái (active/inactive)
- Nếu có `?id=...` trong URL → Sửa danh mục đó
- Nếu không → Thêm danh mục mới

---

#### 4. `Admin/books.php` - Danh Sách Sách
- Hiển thị bảng tất cả sách
- Thông tin: ID, Tên, Tác giả, Giá thuê, Số lượng, Trạng thái
- Có nút "Add" để thêm sách mới
- Có nút "Edit" để sửa sách

---

#### 5. `Admin/manageBooks.php` - Thêm/Sửa Sách
- Form thêm hoặc sửa sách
- Thông tin:
  - ISBN, Danh mục, Tên sách, Tác giả
  - MRP, Tiền cọc (Security), Giá thuê/ngày
  - Số lượng, Ảnh sách
  - Mô tả ngắn, Mô tả chi tiết
- Upload ảnh: Tên file = timestamp + tên file gốc
- Nếu có `?id=...` trong URL → Sửa sách đó
- Nếu không → Thêm sách mới

**✅ Đã cải thiện:**
- **SỬA LỖI:** SQL UPDATE thiếu WHERE clause
- Đơn giản hóa GET parameter handling
- Validate input bằng `(int)` cho số
- Đơn giản hóa check duplicate book name
- Sử dụng `time()` thay vì `rand()` cho filename
- Sử dụng PHP `header()` redirect
- Cải thiện error handling

---

#### 6. `Admin/orders.php` - Quản Lý Đơn Hàng
- Hiển thị bảng tất cả đơn hàng
- Thông tin: OrderID, Ngày đặt, Tên sách, Giá, Thời gian thuê, Địa chỉ, Phương thức thanh toán, Trạng thái thanh toán, Trạng thái đơn hàng
- Form cập nhật trạng thái đơn hàng:
  - Dropdown chọn trạng thái mới
  - Khi cập nhật:
    - Nếu trạng thái = "Returned" (6) hoặc "Cancelled" (4):
      - Tự động tăng lại số lượng sách (`qty + 1`)
    - Cập nhật `order_status` trong bảng `orders`
    - Redirect về trang orders sau khi update

**✅ Đã cải thiện:**
- Đơn giản hóa update status logic (giảm ~6% số dòng)
- Sử dụng `in_array()` thay vì multiple checks
- Sử dụng short syntax cho loops
- Cải thiện SQL query formatting
- Thêm redirect sau khi update

---

#### 7. `Admin/users.php` - Quản Lý Người Dùng
- Hiển thị bảng tất cả người dùng
- Thông tin: ID, Tên, Email, Số điện thoại, Ngày tham gia

---

#### 8. `Admin/feedback.php` - Phản Hồi
- Hiển thị tất cả phản hồi từ khách hàng (bảng `contact_us`)
- Thông tin: ID, Tên, Email, Số điện thoại, Nội dung, Ngày gửi

---

#### 9. `Admin/returnDate.php` - Ngày Trả Sách
- Quản lý ngày trả sách (có vẻ chưa được implement đầy đủ)

---

#### 10. `Admin/logout.php` - Đăng Xuất Admin
- Hủy session `ADMIN_LOGIN`, `ADMIN_email`
- Chuyển về trang đăng nhập admin

---

#### 11. `Admin/topNav.php` - Navigation Bar Admin
- Menu điều hướng cho admin
- Hiển thị tên admin, các link đến các trang quản lý

---

## ✨ TÍNH NĂNG ĐẶC BIỆT

### 1. 💰 Security Deposit (Tiền Cọc)
- Người dùng phải đặt cọc khi thuê sách
- Tiền cọc có thể hoàn lại khi trả sách trong tình trạng tốt
- Tổng thanh toán = (Giá thuê/ngày × Số ngày) + Tiền cọc

**Ví dụ:**
- Giá thuê: 10₫/ngày
- Số ngày: 20 ngày
- Tiền cọc: 300₫
- **Tổng thanh toán = (10 × 20) + 300 = 500₫**

---

### 2. 📅 Rent Duration (Thời Gian Thuê)
- Người dùng tự chọn số ngày thuê
- Giới hạn: **10 - 200 ngày**
- Giá thuê tính theo ngày
- Tổng tiền thuê = Giá thuê/ngày × Số ngày

---

### 3. 📦 Quantity Management (Quản Lý Số Lượng)
- Tự động giảm số lượng khi có đơn hàng mới:
  ```sql
  UPDATE books SET qty = qty - 1 WHERE id = bookId
  ```
- Tự động tăng lại số lượng khi:
  - Đơn hàng bị hủy (Cancelled)
  - Sách được trả lại (Returned)
  ```sql
  UPDATE books SET qty = qty + 1 WHERE id = bookId
  ```
- Hiển thị "out of stock" nếu `qty = 0`

---

### 4. 📊 Order Status Tracking (Theo Dõi Trạng Thái Đơn Hàng)
**Luồng trạng thái bình thường:**
```
Pending → Processing → Shipped → Delivered
```

**Các trạng thái khác:**
- **Cancelled**: Đơn hàng bị hủy (bởi user hoặc admin)
- **Returned**: Sách đã được trả lại

**Quy tắc:**
- User chỉ có thể hủy đơn nếu trạng thái = Pending, Processing, hoặc Shipped
- Admin có thể thay đổi trạng thái bất kỳ lúc nào
- Khi đơn hàng = Returned hoặc Cancelled → Tự động tăng lại số lượng sách

---

### 5. ⭐ Best Seller (Sách Bán Chạy)
- Sách được đánh dấu `best_seller = 1` sẽ hiển thị ở phần "Most Viewed" trên trang chủ
- Admin có thể set/unset best seller khi thêm/sửa sách

---

### 6. 🌙 Dark Mode
- Có nút chuyển đổi Dark Mode
- Sử dụng JavaScript để toggle class `dark-mode` trên body
- Lưu preference trong localStorage (có thể implement)

---

### 7. 🔒 Session Management
**User Session:**
- `USER_LOGIN`: Xác nhận user đã đăng nhập
- `USER_ID`: ID của user
- `USER_NAME`: Tên của user
- `BeforeCheckoutLogin`: Lưu URL checkout khi user chưa đăng nhập

**Admin Session:**
- `ADMIN_LOGIN`: Xác nhận admin đã đăng nhập
- `ADMIN_email`: Email của admin

---

### 8. 🔍 Search Functionality
- Tìm kiếm theo tên sách hoặc tác giả
- Case-insensitive search
- Hiển thị tất cả kết quả khớp

---

### 9. 📱 Responsive Design
- Sử dụng Bootstrap 5
- Có file `responsive.css` để tùy chỉnh responsive
- Hỗ trợ mobile, tablet, desktop

---

### 10. 🛡️ Security Features
- `getSafeValue()` để làm sạch input (tránh SQL injection, XSS)
- Session-based authentication
- Password hashing (MD5 - nên nâng cấp lên bcrypt)
- Kiểm tra đăng nhập trước khi truy cập các trang cần authentication

**⚠️ Lưu ý bảo mật:**
- Admin password đang lưu plain text → Nên hash
- User password dùng MD5 → Nên nâng cấp lên bcrypt/argon2
- Nên thêm CSRF protection
- Nên validate input phía server kỹ hơn

---

## 📁 CẤU TRÚC THỰ MỤC

```
Book-rental/
│
├── Admin/                          # Thư mục quản trị
│   ├── books.php                   # Danh sách sách
│   ├── manageBooks.php             # Thêm/sửa sách
│   ├── categories.php              # Danh sách danh mục
│   ├── manageCategories.php        # Thêm/sửa danh mục
│   ├── orders.php                  # Quản lý đơn hàng
│   ├── users.php                   # Quản lý người dùng
│   ├── feedback.php                # Phản hồi
│   ├── returnDate.php              # Ngày trả sách
│   ├── login.php                   # Đăng nhập admin
│   ├── logout.php                  # Đăng xuất admin
│   ├── topNav.php                  # Navigation bar admin
│   ├── connection.php              # Kết nối database (admin)
│   ├── function.php                # Functions cho admin
│   ├── css/
│   │   └── admin.css               # CSS cho admin
│   └── js/
│       └── admin.js                # JavaScript cho admin
│
├── css/                            # CSS cho user
│   ├── Style.css                   # CSS chính
│   └── responsive.css              # CSS responsive
│
├── Img/                            # Thư mục ảnh
│   ├── books/                      # Ảnh sách
│   ├── carousel/                   # Ảnh carousel
│   ├── Logois.png                  # Logo
│   └── logovnu.png                 # Logo VNU
│
├── Database/
│   └── mini_project.sql            # File SQL tạo database
│
├── index.php                       # Trang chủ
├── SignIn.php                      # Đăng nhập user
├── register.php                    # Đăng ký user
├── logout.php                      # Đăng xuất user
├── book.php                        # Chi tiết sách
├── bookCategory.php                # Xem sách theo danh mục
├── checkout.php                    # Thanh toán
├── myOrder.php                     # Đơn hàng của tôi
├── profile.php                     # Cập nhật profile
├── search.php                      # Tìm kiếm
├── contactUs.php                   # Liên hệ
├── aboutUs.php                     # Giới thiệu
├── termsAndCondition.php           # Điều khoản
├── thankYou.php                    # Cảm ơn sau khi đặt hàng
├── header.php                      # Header (navigation)
├── footer.php                      # Footer
├── sidebar.php                     # Sidebar (nếu có)
├── connection.php                  # Kết nối database (user)
└── function.php                    # Functions cho user
```

---

## 🔧 CẤU HÌNH VÀ SETUP

### Yêu Cầu Hệ Thống
- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx web server
- Bootstrap 5 (CDN)
- Font Awesome (CDN)

### Cài Đặt
1. Import database:
   ```sql
   mysql -u root -p < Database/mini_project.sql
   ```
   Hoặc import file `Database/mini_project.sql` qua phpMyAdmin

2. Cấu hình kết nối database:
   - Sửa file `connection.php` và `Admin/connection.php`
   - Thay đổi: host, username, password, database name

3. Cấu hình đường dẫn:
   - Sửa `SERVER_PATH` và `SITE_PATH` trong `connection.php` theo đường dẫn thực tế

4. Upload ảnh:
   - Đảm bảo thư mục `Img/books/` có quyền ghi

5. Truy cập:
   - User: `http://localhost/ins3064/Book-rental/`
   - Admin: `http://localhost/ins3064/Book-rental/Admin/login.php`

### Tài Khoản Mặc Định
**Admin:**
- Email: `tqhien614@gmail.com` hoặc `tienduc@gmail.com`
- Password: `123`

**Lưu ý:** Nên đổi mật khẩu admin sau khi setup!

---

## 🔄 CODE IMPROVEMENTS & REFACTORING

### ✅ Các Cải Thiện Đã Thực Hiện

#### 1. **Functions Optimization**
- ✅ Sửa `getSafeValue()`: Luôn return string (không return null)
- ✅ Sửa `getProduct()`: Validate input, error handling, logic cải thiện
- ✅ Thêm `getBook()`: Function lấy sách bán chạy
- ✅ Thêm `searchBooks()`: Function tìm kiếm sách
- ✅ Xóa `updateProfile()`: Không được sử dụng và có lỗi
- ✅ Cải thiện `pr()` và `prx()`: Thêm đóng tag `</pre>`

#### 2. **Code Simplification**
Đã đơn giản hóa **9 file chính**, giảm tổng cộng **~15-20%** số dòng code:

- ✅ **checkout.php**: Giảm ~20% (validation, redirect, code cleanup)
- ✅ **book.php**: Giảm ~9% (qty check, redirect)
- ✅ **SignIn.php**: Giảm ~13% (redirect logic)
- ✅ **register.php**: Giảm ~34% (validation code)
- ✅ **profile.php**: Giảm ~28% (validation code)
- ✅ **myOrder.php**: Giảm ~11% (cancel logic)
- ✅ **contactUs.php**: Giảm ~13% (auto-fill logic)
- ✅ **Admin/orders.php**: Giảm ~6% (update logic)
- ✅ **Admin/manageBooks.php**: Giảm ~10% + sửa lỗi SQL

#### 3. **Code Style Improvements**
- ✅ Thay JavaScript redirect bằng PHP `header()` (tốt hơn cho SEO)
- ✅ Sử dụng short syntax `if/else:` thay vì `if { echo }`
- ✅ Sử dụng ternary operator `?:` và null coalescing `??`
- ✅ Sử dụng `in_array()` thay vì multiple `===` checks
- ✅ Format SQL queries cho dễ đọc
- ✅ Sử dụng `[]` thay vì `array()`

#### 4. **Security & Validation**
- ✅ Validate input bằng `(int)` cho số để tránh SQL injection
- ✅ Sử dụng `isset()` và null coalescing `??`
- ✅ Kiểm tra dữ liệu trước khi sử dụng
- ✅ Cải thiện error handling

#### 5. **Bug Fixes**
- ✅ **Admin/manageBooks.php**: Sửa SQL UPDATE thiếu WHERE clause
- ✅ **checkout.php**: Thêm validation cho bookId và duration
- ✅ **book.php**: Xóa query qty thừa
- ✅ **myOrder.php**: Đơn giản hóa cancel logic

### 📊 Tổng Kết
- **Số dòng code giảm:** ~15-20%
- **Functions được tối ưu:** 5 functions
- **Lỗi đã sửa:** 4 lỗi nghiêm trọng
- **Files được cải thiện:** 9 files

---

## 📝 GHI CHÚ QUAN TRỌNG

### ⚠️ Vấn Đề Bảo Mật
1. **Password Storage:**
   - User password: Đang dùng MD5 (không an toàn) → Nên nâng cấp lên bcrypt
   - Admin password: Đang lưu plain text → Cần hash ngay!

2. **SQL Injection:**
   - Đã có `getSafeValue()` nhưng cần kiểm tra kỹ hơn
   - Nên sử dụng Prepared Statements

3. **XSS Protection:**
   - Đã có `htmlspecialchars()` trong `getSafeValue()`
   - Cần kiểm tra output trên tất cả các trang

4. **Session Security:**
   - Nên set `session_regenerate_id()` sau khi login
   - Nên set session timeout

### 🔄 Cải Thiện Đề Xuất
1. Thêm pagination cho danh sách sách
2. Thêm filter/sort cho danh sách sách
3. Thêm tính năng đánh giá sách
4. Thêm tính năng wishlist
5. Thêm email notification khi đặt hàng
6. Thêm tính năng thanh toán online (PayU, PayPal, etc.)
7. Thêm tính năng quản lý kho (inventory management)
8. Thêm báo cáo thống kê cho admin
9. Thêm tính năng tìm kiếm nâng cao
10. Thêm tính năng đặt lại mật khẩu

---

## 📞 LIÊN HỆ VÀ HỖ TRỢ

Nếu có thắc mắc hoặc cần hỗ trợ, vui lòng liên hệ qua:
- Email: `tqhien614@gmail.com`
- Hoặc sử dụng form liên hệ trong hệ thống

---

**Tài liệu này được tạo tự động dựa trên phân tích code của dự án Book Rental System.**

*Cập nhật lần cuối: 2024*

