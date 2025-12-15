# SYSTEM ARCHITECTURE AND THEORETICAL FOUNDATION

## 1. Overview of the System

- **Project name**: Online Book Rental System (Bookrentail)
- **Main purpose**: Cung cấp nền tảng cho phép người dùng thuê sách online, quản lý đơn thuê, thanh toán, và quản trị nội dung (sách, danh mục, người dùng, feedback) qua trang admin.
- **Main actors**:
  - **Customer/User**: đăng ký, đăng nhập, xem danh sách sách, đặt thuê, xem lịch sử đơn hàng, gửi đánh giá.
  - **Administrator**: quản lý danh mục, sách, đơn hàng, người dùng, feedback và cấu hình hệ thống.

---

## 2. Technology Stack

### 2.1 Presentation Layer (Front-end)
- **Technologies**:
  - HTML5, CSS3
  - JavaScript (basic DOM manipulation)
  - Bootstrap / MDB (Material Design for Bootstrap) cho layout, responsive UI
- **Responsibilities**:
  - Hiển thị giao diện người dùng cho phần customer (thư mục `pages/`) và admin (thư mục `Admin/`).
  - Thực hiện validate cơ bản phía client (nhập form, yêu cầu trường bắt buộc, định dạng). 
  - Gửi dữ liệu tới server thông qua HTTP (POST/GET form submit).

### 2.2 Application Layer (Back-end)
- **Technology**: PHP (procedural style)
- **Main components**:
  - `config/connection.php`: khởi tạo kết nối MySQL, quản lý session.
  - `includes/function.php`: chứa các hàm tiện ích (utility) như xử lý login, token Remember Me, validate input, truy vấn dữ liệu.
  - `Admin/*.php`: các module chức năng cho admin (categories, books, orders, users, feedbacks, login, ...).
  - `pages/*.php`: các module chức năng cho người dùng (đăng ký, đăng nhập, xem sách, tìm kiếm, thanh toán, hồ sơ, đơn hàng...).
- **Responsibilities**:
  - Xử lý nghiệp vụ (business logic) cho việc thuê sách, quản lý hàng tồn, trạng thái đơn hàng, quản lý nội dung.
  - Kiểm tra quyền truy cập (session `ADMIN_LOGIN`, session user).
  - Thực hiện kiểm tra/validate dữ liệu server-side.
  - Điều hướng (redirect) và render view HTML tương ứng.

### 2.3 Data Layer (Database)
- **Technology**: MySQL / MariaDB
- **Main tables (mang tính khái quát)**:
  - `users`: lưu thông tin người dùng (id, name, email, mobile, password, doj,...).
  - `categories`: danh mục sách (id, category, status,...).
  - `books`: thông tin sách (id, category_id, ISBN, name, author, security, rent, qty, short_desc, description, status, img, vnd,...).
  - `orders`: thông tin đơn hàng (id, user_id, date, total, order_status,...).
  - `order_detail`: chi tiết đơn (order_id, book_id, giá, số lượng,...).
  - `order_status`: danh mục trạng thái đơn (id, status_name,...).
  - `feedback`: đánh giá của khách hàng (id, order_id, user_id, book_id, rating, comment, created_at,...).
- **Responsibilities**:
  - Lưu trữ dữ liệu có cấu trúc cho toàn bộ hệ thống.
  - Đảm bảo tính toàn vẹn giữa các bảng (ràng buộc khóa ngoại logic qua id).

---

## 3. Logical Architecture

Hệ thống được thiết kế theo mô hình 3 lớp cơ bản (3-tier logical architecture):

1. **Presentation Layer** (View)
   - Các file PHP phần view kết hợp HTML/CSS/JS (ví dụ: `pages/index.php`, `Admin/categories.php`).
   - Nhận dữ liệu từ controller (script PHP) và hiển thị dưới dạng bảng, form, thông tin chi tiết.

2. **Application / Business Logic Layer** (Controller + Business)
   - Script PHP xử lý logic ở đầu mỗi file (ví dụ: `Admin/manageBooks.php` chứa phần xử lý POST/GET phía trên, phần HTML ở dưới).
   - Xử lý các quy tắc nghiệp vụ:
     - Kiểm tra trùng tên sách.
     - Cập nhật lại `qty` khi đơn hàng bị hủy hoặc trả (trong `Admin/orders.php`).
     - Quản lý trạng thái `Active/Inactive` cho categories và books.
     - Kiểm soát phân quyền: chỉ admin đã login mới truy cập được module admin.

3. **Data Access Layer** (DAL)
   - Gồm kết nối database và các câu lệnh truy vấn `mysqli_query`, được sử dụng xuyên suốt trong các file.
   - Một số hàm dành cho truy vấn dữ liệu được gom trong `includes/function.php` (ví dụ: lấy danh sách feedback, xử lý token Remember Me,...).

Luồng xử lý điển hình:
- Người dùng gửi request (HTTP GET/POST) → file PHP tương ứng.
- Ở đầu file PHP: kiểm tra session/quyền, đọc input, validate, gọi truy vấn DB, chuẩn bị dữ liệu.
- Sau khi xử lý xong logic: include view (HTML trong cùng file) → render cho trình duyệt.

---

## 4. Deployment Architecture

- **Server environment**: 
  - Local dev: Laragon (Windows) gồm Apache/Nginx + PHP + MySQL.
  - Production (dự kiến): shared hosting hoặc VPS chạy **LAMP**/**LEMP** stack.
- **Folder structure chính**:
  - `/Admin`: giao diện và logic cho admin.
  - `/pages`: giao diện và logic cho khách hàng.
  - `/includes`: file chung (header, footer, sidebar, function...).
  - `/config`: cấu hình kết nối CSDL.
  - `/assets`: CSS, JS, hình ảnh.
  - `/docs`: tài liệu hệ thống (.md).
- **Access pattern**:
  - Khách hàng truy cập qua URL công khai (ví dụ: `/pages/index.php`).
  - Admin truy cập qua `/Admin/login.php`, sau khi đăng nhập sẽ được redirect tới `/Admin/categories.php` (dashboard).

---

## 5. Security Architecture

### 5.1 Authentication & Session Management
- Sử dụng session PHP để lưu trạng thái đăng nhập:
  - `$_SESSION['USER_LOGIN']` (giả định) cho customer.
  - `$_SESSION['ADMIN_LOGIN']` cho admin (xuất hiện trong nhiều file admin).
- Cơ chế **Remember Me** cho admin:
  - Nếu chưa có session admin, hệ thống gọi `checkAdminRememberToken($con)` để kiểm tra token trong cookie.
- Tất cả các trang admin (ví dụ `categories.php`, `manageBooks.php`, `orders.php`, `users.php`) đều kiểm tra session trước khi xử lý và redirect về `login.php` nếu chưa đăng nhập.

### 5.2 Authorization
- Phân tách URL admin và customer.
- Các thao tác nhạy cảm (xóa user, xóa feedback, xóa category, cập nhật trạng thái đơn hàng) chỉ khả dụng trong vùng admin.

### 5.3 Data Validation & Sanitization
- Sử dụng các kỹ thuật:
  - `mysqli_real_escape_string` để chống SQL Injection cho input dạng text.
  - Ép kiểu `(int)` cho id, giá trị số (
    ví dụ: `$id = (int)$_GET['id'];`).
  - `htmlspecialchars` khi hiển thị dữ liệu người dùng nhập lên UI để tránh XSS.

### 5.4 File Upload Security
- Ảnh sách được upload qua `$_FILES['img']`.
- Tên file được đổi dạng `time() . '_' . original_name` để tránh trùng lặp.
- Lưu ảnh tại `BOOK_IMAGE_SERVER_PATH` và hiển thị qua `BOOK_IMAGE_SITE_PATH`.

---

## 6. Theoretical Foundation

### 6.1 Web Application Architecture
- Dựa trên mô hình **Client–Server**:
  - Client: trình duyệt web của người dùng.
  - Server: ứng dụng PHP + MySQL xử lý request và trả về HTML.
- Hệ thống sử dụng kiến trúc **multi-page application (MPA)**:
  - Mỗi chức năng chính tương ứng một file PHP riêng.
  - Điều hướng qua URL và HTTP redirect.

### 6.2 Software Architecture Patterns
- **Layered Architecture (3-layer)**:
  - Presentation Layer: giao diện, hiển thị dữ liệu.
  - Business Logic Layer: quy tắc nghiệp vụ, xử lý request, kiểm tra điều kiện.
  - Data Access Layer: tương tác với CSDL.
- Đặc điểm:
  - Dễ hiểu, phù hợp với ứng dụng CRUD vừa và nhỏ.
  - Dễ mở rộng bằng cách thêm chức năng (file) mới.

### 6.3 Relational Database Theory
- Dự án áp dụng mô hình **Cơ sở dữ liệu quan hệ (RDBMS)**:
  - Dữ liệu tổ chức theo bảng (relations).
  - Mỗi bảng có **primary key** (id) để định danh bản ghi.
  - Sử dụng **foreign key logic** (order_detail.order_id tham chiếu orders.id, book_id tham chiếu books.id) để thể hiện quan hệ 1-n, n-n.
- Lợi ích:
  - Đảm bảo tính nhất quán và toàn vẹn dữ liệu.
  - Hỗ trợ truy vấn phức tạp (JOIN) cho báo cáo, thống kê.

### 6.4 Transaction & Consistency (Ở mức cơ bản)
- Khi cập nhật trạng thái đơn hàng (hủy/trả), hệ thống đồng thời cập nhật lại `qty` của sách.
- Đây là một ví dụ về đảm bảo tính **nhất quán nghiệp vụ (business consistency)** giữa đơn hàng và tồn kho.

### 6.5 Human–Computer Interaction (HCI) & UX
- Sử dụng Bootstrap/MDB giúp:
  - Giao diện responsive trên nhiều kích thước màn hình.
  - Component UI quen thuộc (navbar, table, form, button) → người dùng dễ sử dụng.
- Admin UI:
  - Sử dụng table và button hành động (Edit, Delete, View Details).
  - Dùng badge màu sắc cho trạng thái đơn hàng (Pending, Processing, Delivered, ...).
- Các hộp thoại confirm trước khi xóa đảm bảo **usability & safety**.

---

## 7. Design Principles Applied

- **Separation of Concerns**:
  - Tách phần cấu hình (`config/`), hàm dùng chung (`includes/`), giao diện người dùng (`pages/`), và phần admin (`Admin/`).
- **DRY (Donft Repeat Yourself)** ở mức cơ bản:
  - Dùng `topNav.php`, `header.php`, `footer.php` dùng chung nhiều trang.
  - Dùng các hàm chung trong `includes/function.php` để tránh lặp code.
- **Defensive Programming**:
  - Luôn kiểm tra biến GET/POST tồn tại trước khi dùng.
  - Kiểm tra session trước khi cho phép truy cập vào chức năng nhạy cảm.

---

## 8. Extensibility and Future Directions

- **Tách Business Logic rõ hơn**:
  - Có thể tiến tới mô hình MVC đầy đủ (Controller riêng, Model riêng cho mỗi entity: User, Book, Order...).
- **API Layer**:
  - Mở rộng thêm RESTful API (JSON) để hỗ trợ mobile app hoặc SPA.
- **Security nâng cao**:
  - Dùng prepared statements (PDO) thay cho query nối chuỗi.
  - Áp dụng CSRF token cho form quan trọng.
- **Scalability**:
  - Tối ưu SQL query, thêm index cho các cột tìm kiếm nhiều (email, book name, category_id,...).
  - Sử dụng caching layer (Redis/Memcached) nếu hệ thống mở rộng.

Tài liệu này nhằm mô tả tổng quan kiến trúc và nền tảng lý thuyết đứng sau hệ thống, hỗ trợ cho việc viết báo cáo đồ án hoặc tài liệu kỹ thuật cho dự án Bookrentail.
