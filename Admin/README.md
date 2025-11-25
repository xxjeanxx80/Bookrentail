# 📚 Admin Panel - Student Project Coding Standards

## 🎯 Mục Tiêu
Dự án này được thiết kế cho học sinh thực hành, code phải:
- **Dễ hiểu** cho người mới học PHP/MySQL
- **Logic hoạt động đúng** 
- **Gọn gàng, sạch sẽ**
- **Bảo mật cơ bản**

## 📝 Luật Code

### 1. Tên Biến & Function
```php
// ✅ Tốt: Rõ ràng, tiếng Anh đơn giản
$bookName = "Gulliver's Travels";
$userEmail = $_SESSION['ADMIN_EMAIL'];
function validateBookData($data) { ... }

// ❌ Tránh: Tên khó hiểu
$bn = "Gulliver's Travels";
$ue = $_SESSION['ADMIN_EMAIL'];
function vd($d) { ... }
```

### 2. Comment Style
```php
// ✅ Comment ngắn gọn, giải thích TẠI SAO
// Check if book already exists to prevent duplicates
$sql = "SELECT * FROM books WHERE name='$bookName'";

// ✅ Comment cho function
/**
 * Add new book to database
 * @param array $bookData Book information
 * @return bool Success status
 */
function addBook($bookData) { ... }
```

### 3. Database Operations
```php
// ✅ Luôn dùng getSafeValue cho security
$category_id = getSafeValue($con, $_POST['category_id']);
$book_name = getSafeValue($con, $_POST['book_name']);

// ✅ Query đơn giản, dễ đọc
$sql = "INSERT INTO books (name, author, isbn) 
        VALUES ('$book_name', '$author', '$isbn')";

// ❌ Tránh query phức tạp trong 1 dòng
$sql = "INSERT INTO books (name,author,category_id,security,rent,qty,short_desc,description,status,img) VALUES ('$name','$author','$category_id','$security','$rent','$qty','$short_desc','$description','1','$img')";
```

### 4. Error Handling
```php
// ✅ Error message đơn giản, dễ hiểu
if (!$result) {
    $error = "Không thể thêm sách. Vui lòng thử lại.";
    return false;
}

// ✅ Validation message rõ ràng
if (empty($book_name)) {
    $msg = "Tên sách không được để trống";
}
```

### 5. File Structure
```
Admin/
├── connection.php     # Database connection
├── function.php       # Utility functions  
├── topNav.php         # Navigation header
├── books.php          # Book list display
├── manageBooks.php    # Add/Edit book form
├── orders.php         # Order management
├── categories.php     # Category management
├── users.php          # User management
├── css/admin.css      # Styles
└── README.md          # This file
```

### 6. Function Length
```php
// ✅ Tốt: Function ngắn, làm 1 việc
function validateBookForm() {
    if (empty($_POST['name'])) return false;
    if (empty($_POST['author'])) return false;
    if (empty($_POST['isbn'])) return false;
    return true;
}

function insertBook($data) {
    $sql = "INSERT INTO books ...";
    return pg_query($con, $sql);
}

// ❌ Tránh: Function quá dài, làm nhiều việc
function processBookForm() {
    // 100 lines of validation + insert + redirect + email + logging
}
```

### 7. Security Rules
```php
// ✅ Luôn sanitize input
$name = getSafeValue($con, $_POST['name']);

// ✅ Check admin login
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    header('location: login.php');
    exit();
}

// ✅ Validate file uploads
if ($_FILES['img']['error'] !== UPLOAD_ERR_OK) {
    $error = "File upload failed";
}
```

### 8. HTML/PHP Mix
```php
// ✅ Tách PHP logic và HTML display
<?php
// Logic ở trên
$books = getAllBooks();
$error = getMessage();
?>
<!-- HTML ở dưới, chỉ echo variables -->
<?php if ($error): ?>
    <div class="alert"><?php echo $error; ?></div>
<?php endif; ?>

<table>
    <?php foreach ($books as $book): ?>
        <tr>
            <td><?php echo $book['name']; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
```

## 🔍 Code Review Checklist

### Functionality Check
- [ ] Database queries execute without errors
- [ ] Forms submit and validate correctly  
- [ ] File uploads work properly
- [ ] Session management functions
- [ ] Navigation links work

### Code Quality Check  
- [ ] Variable names are descriptive
- [ ] Functions are short and focused
- [ ] Comments explain WHY not WHAT
- [ ] Error messages are user-friendly
- [ ] No hardcoded values (use constants)

### Security Check
- [ ] All inputs use getSafeValue()
- [ ] Admin login is checked
- [ ] File uploads are validated
- [ ] SQL injection protection

## 🚀 Quick Start Guide

1. **Read code** from top to bottom
2. **Test each function** manually
3. **Simplify complex logic** 
4. **Add comments** where needed
5. **Update README** with changes

## 📞 Support

- Code phải dễ đọc cho người mới học
- Logic phải đơn giản nhưng đúng
- Luôn test trước khi commit
- Hỏi khi không hiểu - không ngại!

*"Good code is like a good joke - it doesn't need explanation!"* 🎯
