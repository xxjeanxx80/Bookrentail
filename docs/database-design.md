# IV. DATABASE DESIGN

## 1. Tổng quan cơ sở dữ liệu

- **Tên CSDL**: `mini_project`
- **Hệ quản trị**: MySQL 8.0
- **Mã hóa ký tự**: `utf8mb4`
- **Mục tiêu thiết kế**:
  - Lưu trữ thông tin quản lý hệ thống thuê sách online: sách, danh mục, người dùng, đơn hàng, chi tiết đơn hàng, trạng thái đơn, phản hồi, liên hệ.
  - Hỗ trợ các chức năng nghiệp vụ chính: đăng ký/đăng nhập, duyệt sách, tạo đơn hàng, quản lý tồn kho, đánh giá sách, quản trị hệ thống.

Các bảng chính:
- `admin`, `admin_tokens`
- `users`, `user_tokens`
- `categories`
- `books`
- `orders`, `order_detail`, `order_status`
- `feedback`
- `contact_us`

---

## 2. Mô tả chi tiết các bảng

### 2.1 Bảng `admin`
- **Mục đích**: Lưu thông tin tài khoản quản trị hệ thống.
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT): Khóa chính, định danh admin.
  - `email` (varchar(50), NOT NULL): Email đăng nhập của admin.
  - `password` (varchar(255), NOT NULL): Mật khẩu được mã hóa (MD5 trong dữ liệu mẫu).
- **Primary Key**: (`id`)
- **Ghi chú**: Dữ liệu mẫu có 2 admin.

### 2.2 Bảng `admin_tokens`
- **Mục đích**: Lưu token "Remember Me" cho admin (đăng nhập tự động).
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT)
  - `admin_id` (int, NOT NULL): Tham chiếu logic tới `admin.id`.
  - `token` (varchar(64), NOT NULL): Chuỗi token duy nhất.
  - `expires_at` (datetime, NOT NULL): Thời điểm hết hạn token.
  - `created_at` (datetime, NOT NULL): Thời điểm tạo token.
- **Primary Key**: (`id`)
- **Unique Key**: `token` (`token`)
- **Index**:
  - `admin_id` (`admin_id`)
  - `expires_at` (`expires_at`)
- **Quan hệ logic**:
  - 1 admin có thể có nhiều token (`admin` 1–N `admin_tokens`).

### 2.3 Bảng `users`
- **Mục đích**: Lưu thông tin tài khoản người dùng (khách hàng).
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT)
  - `name` (varchar(80), NOT NULL): Tên người dùng.
  - `email` (varchar(50), NOT NULL): Email đăng nhập.
  - `mobile` (bigint, NOT NULL): Số điện thoại.
  - `doj` (datetime, NOT NULL): Ngày tạo tài khoản (date of joining).
  - `password` (varchar(255), NOT NULL): Mật khẩu được mã hóa.
- **Primary Key**: (`id`)
- **Ghi chú**: Chưa khai báo UNIQUE trên `email` nhưng trong nghiệp vụ thường coi email là duy nhất.

### 2.4 Bảng `user_tokens`
- **Mục đích**: Lưu token "Remember Me" cho người dùng.
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT)
  - `user_id` (int, NOT NULL): Tham chiếu logic tới `users.id`.
  - `token` (varchar(64), NOT NULL): Chuỗi token duy nhất.
  - `expires_at` (datetime, NOT NULL)
  - `created_at` (datetime, NOT NULL)
- **Primary Key**: (`id`)
- **Unique Key**: `token` (`token`)
- **Index**:
  - `user_id` (`user_id`)
  - `expires_at` (`expires_at`)
- **Quan hệ logic**:
  - 1 user có thể có nhiều token (`users` 1–N `user_tokens`).

### 2.5 Bảng `categories`
- **Mục đích**: Lưu danh mục sách.
- **Cấu trúc trường**:
  - `id` (mediumint, PK, AUTO_INCREMENT)
  - `category` (varchar(50), NOT NULL): Tên danh mục.
  - `status` (tinyint, NOT NULL): Trạng thái (1: Active, 0: Inactive).
- **Primary Key**: (`id`)
- **Quan hệ logic**:
  - 1 danh mục có thể chứa nhiều sách (`categories` 1–N `books`).

### 2.6 Bảng `books`
- **Mục đích**: Lưu thông tin chi tiết các cuốn sách dùng để thuê.
- **Cấu trúc trường**:
  - `id` (int, AUTO_INCREMENT)
  - `category_id` (int, NOT NULL): Tham chiếu logic tới `categories.id`.
  - `ISBN` (varchar(20), NOT NULL): Mã ISBN của sách.
  - `name` (varchar(255), NOT NULL): Tên sách.
  - `img` (varchar(255), NOT NULL): Tên file ảnh bìa sách.
  - `author` (varchar(75), NOT NULL): Tác giả.
  - `vnd` (int, NOT NULL): Giá trị tiền VNĐ (dùng cho hiển thị/tham khảo).
  - `security` (int, NOT NULL): Tiền cọc (security charges).
  - `rent` (int, NOT NULL): Tiền thuê mỗi ngày.
  - `qty` (int, NOT NULL): Số lượng tồn kho.
  - `best_seller` (int, DEFAULT NULL): Đánh dấu sách bán/chạy tốt.
  - `short_desc` (varchar(2000), NOT NULL): Mô tả ngắn.
  - `description` (text, NOT NULL): Mô tả chi tiết.
  - `status` (tinyint, NOT NULL, DEFAULT 1): Trạng thái hiển thị (Active/Inactive).
  - `price` (int, VIRTUAL GENERATED): Cột ảo, giá = `security` + `rent`.
- **Primary Key**: (`id`, `ISBN`) – khóa chính tổng hợp gồm id và ISBN.
- **Ghi chú thiết kế**:
  - Sử dụng cột ảo `price` (GENERATED ALWAYS AS) giúp tránh trùng lặp dữ liệu và luôn đồng bộ với `security` và `rent`.
  - `category_id` kết nối logic với `categories` để phân loại sách.

### 2.7 Bảng `orders`
- **Mục đích**: Lưu thông tin đơn hàng (đơn thuê sách).
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT)
  - `user_id` (int, NOT NULL): Tham chiếu logic tới `users.id`.
  - `address` (varchar(100), NOT NULL): Địa chỉ giao hàng chính.
  - `address2` (varchar(100), DEFAULT NULL): Địa chỉ phụ.
  - `pin` (int, NOT NULL): Mã bưu chính/khu vực.
  - `payment_method` (varchar(20), DEFAULT NULL): Hình thức thanh toán (VD: COD).
  - `total` (int, NOT NULL): Tổng tiền đơn hàng.
  - `payment_status` (varchar(20), NOT NULL): Trạng thái thanh toán (VD: success).
  - `order_status` (int, NOT NULL): Tham chiếu logic tới `order_status.id`.
  - `date` (datetime, NOT NULL): Thời điểm tạo đơn.
  - `duration` (int, NOT NULL): Thời gian thuê (số ngày).
- **Primary Key**: (`id`)
- **Quan hệ logic**:
  - 1 user có nhiều orders (`users` 1–N `orders`).
  - 1 orders có nhiều `order_detail` (`orders` 1–N `order_detail`).
  - `order_status` cho biết trạng thái (Pending, Processing, Shipped,...).

### 2.8 Bảng `order_detail`
- **Mục đích**: Lưu chi tiết từng sách trong một đơn hàng.
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT)
  - `order_id` (int, NOT NULL): Tham chiếu logic tới `orders.id`.
  - `book_id` (int, NOT NULL): Tham chiếu logic tới `books.id`.
  - `price` (float, NOT NULL): Giá áp dụng cho cuốn sách trong đơn (đã nhân thời gian thuê nếu cần).
  - `time` (int, NOT NULL): Thời gian thuê (ngày) cho cuốn sách đó.
- **Primary Key**: (`id`)
- **Quan hệ logic**:
  - `orders` 1–N `order_detail`.
  - `books` 1–N `order_detail`.

### 2.9 Bảng `order_status`
- **Mục đích**: Danh mục trạng thái đơn hàng.
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT)
  - `status_name` (varchar(15), NOT NULL): Tên trạng thái (Pending, Processing, Shipped, Delivered, Cancelled, Returned).
- **Primary Key**: (`id`)
- **Unique Key**: `order_status_name_uindex` (`status_name`)
- **Quan hệ logic**:
  - 1 trạng thái có thể áp dụng cho nhiều orders (`order_status` 1–N `orders`).

### 2.10 Bảng `feedback`
- **Mục đích**: Lưu đánh giá và nhận xét của khách hàng về sách sau khi thuê.
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT)
  - `order_id` (int, NOT NULL): Liên kết tới đơn hàng.
  - `book_id` (int, NOT NULL): Liên kết tới sách.
  - `user_id` (int, NOT NULL): Liên kết tới người dùng.
  - `rating` (tinyint(1), NOT NULL): Điểm đánh giá 1–5 sao.
  - `comment` (text, NULL): Nội dung nhận xét.
  - `created_at` (datetime, NOT NULL, DEFAULT CURRENT_TIMESTAMP): Thời điểm tạo feedback.
- **Primary Key**: (`id`)
- **Unique Key**: `unique_feedback` (`order_id`, `book_id`, `user_id`)
- **Ý nghĩa unique key**:
  - Mỗi user chỉ được đánh giá **một lần** cho một book trong một order cụ thể → tránh spam/đánh giá trùng lặp.

### 2.11 Bảng `contact_us`
- **Mục đích**: Lưu câu hỏi, góp ý, liên hệ từ người dùng.
- **Cấu trúc trường**:
  - `id` (int, PK, AUTO_INCREMENT)
  - `name` (varchar(70), NOT NULL): Tên người gửi.
  - `email` (varchar(70), NOT NULL)
  - `mobile` (bigint, NOT NULL)
  - `message` (text, NOT NULL): Nội dung liên hệ.
  - `date` (datetime, DEFAULT NULL): Thời điểm gửi.
- **Primary Key**: (`id`)

---

## 3. Quan hệ giữa các bảng (Logical Relationships)

Mặc dù file SQL không khai báo `FOREIGN KEY` rõ ràng, các quan hệ nghiệp vụ có thể mô tả như sau:

- **User – Order – Order Detail**
  - `users.id` **1–N** `orders.user_id`
  - `orders.id` **1–N** `order_detail.order_id`
  - `books.id` **1–N** `order_detail.book_id`

- **Order – Order Status**
  - `order_status.id` **1–N** `orders.order_status`

- **User – Feedback – Book – Order**
  - `users.id` **1–N** `feedback.user_id`
  - `books.id` **1–N** `feedback.book_id`
  - `orders.id` **1–N** `feedback.order_id`
  - Ràng buộc duy nhất `unique_feedback (order_id, book_id, user_id)` đảm bảo 1 user chỉ đánh giá 1 lần cho 1 sách trong 1 đơn.

- **User/Admin – Token**
  - `admin.id` **1–N** `admin_tokens.admin_id`
  - `users.id` **1–N** `user_tokens.user_id`

- **Category – Book**
  - `categories.id` **1–N** `books.category_id`

---

## 4. Lý do và tiêu chí thiết kế

- **Chuẩn hoá dữ liệu (Normalization)**
  - Tách bảng `order_status` để tránh lặp chuỗi trạng thái trong nhiều đơn.
  - Sử dụng bảng `order_detail` để mô hình hóa quan hệ **N–N** giữa `orders` và `books`.
  - Tách bảng `feedback` để lưu đánh giá độc lập, không làm phồng bảng `orders` hay `books`.

- **Tối ưu truy vấn**
  - Đánh index trên các trường thường dùng để truy vấn/filter: `admin_id`, `user_id`, `expires_at`, `status_name`.
  - Dùng cột ảo `price` trong `books` để dễ tính toán giá hiển thị mà không cần lưu thêm cột thừa.

- **Đảm bảo toàn vẹn nghiệp vụ**
  - `unique_feedback` tránh đánh giá trùng.
  - Phần code (PHP) khi cập nhật trạng thái đơn `Cancelled`/`Returned` sẽ tăng lại `qty` sách → đảm bảo tính nhất quán giữa đơn hàng và tồn kho.

---

## 5. Hướng phát triển và cải tiến CSDL

- Bổ sung **FOREIGN KEY** thực trên các cột id liên kết (user_id, book_id, order_id, category_id, ...) để:
  - Tự động enforce ràng buộc toàn vẹn.
  - Hỗ trợ `ON DELETE CASCADE`/`ON UPDATE CASCADE` khi cần.
- Thêm **UNIQUE** cho `users.email` để đảm bảo email không trùng.
- Tách thêm các bảng tham chiếu (reference) nếu cần, ví dụ bảng `payment_method`.
- Bổ sung cột `created_at`, `updated_at` cho một số bảng (`books`, `categories`, `orders`, `users`) để hỗ trợ audit/log.

Tài liệu này mô tả chi tiết thiết kế CSDL của hệ thống Bookrentail, tương ứng với file `Database/mini_project.sql`, phục vụ cho phần **IV. Database Design** trong báo cáo đồ án hoặc tài liệu kỹ thuật của dự án.
