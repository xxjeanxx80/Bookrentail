<?php
// Xử lý logic TRƯỚC KHI require topNav (để tránh lỗi headers already sent)
require_once(__DIR__ . '/../config/connection.php');
require_once(__DIR__ . '/../includes/function.php');

// Kiểm tra Remember Me token nếu chưa có session
if (!isset($_SESSION['ADMIN_LOGIN'])) {
    checkAdminRememberToken($con);
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['ADMIN_LOGIN']) || $_SESSION['ADMIN_LOGIN'] != 'yes') {
    header('Location: login.php');
    exit;
}

// Khởi tạo biến
$category_id = '';
$ISBN = '';
$name = '';
$author = '';
$security = '';
$rent = '';
$qty = '';
$img = '';
$description = '';
$short_desc = '';
$error = '';
$msg = '';

// Lấy ID từ GET (nếu đang edit)
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Biến để lưu ảnh cũ khi edit (cần lấy trước để dùng khi có lỗi hoặc không upload ảnh mới)
$currentImg = '';
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

// Xử lý submit form
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
                $sql = "INSERT INTO books(category_id, ISBN, name, author, security, rent, qty, short_desc, description, status, img, vnd)
                        VALUES ($category_id, '$ISBN', '$name', '$author', $security, $rent, $qty, '$short_desc', '$description', 1, '$img', 0)";
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
?>
<main>
    <div class="container pt-4">
        <h4 class="fs-2 text-center ">Manage Books</h4>
        <hr>
        <br>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-sm-8">

                <!-- ISBN -->
                <div class="mb-3">
                    <label for="ISBN" class="form-label">ISBN</label>
                    <input type="text" name="ISBN" value="<?php echo $ISBN ?>" id="ISBN" class="form-control"
                        required />
                </div>
            </div>
            <div class="col-sm">

                <!-- Categories selector-->
                <div>
                    <select class="form-select" name="category_id">
                        <option class="">Select Category</option>
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
        </div>

        <!-- Book Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Book Name</label>
            <input type="text" name="name" value="<?php echo $name ?>" id="name" class="form-control" required />
        </div>

        <!-- Book Author -->
        <div class="mb-3">
            <label for="author" class="form-label">Author</label>
            <input type="text" name="author" value="<?php echo $author ?>" id="author" class="form-control"
                required />
        </div>

        <!-- security -->
        <div class="mb-3">
            <label for="security" class="form-label">Security Charges</label>
            <input type="number" name="security" value="<?php echo $security ?>" id="security" class="form-control"
                required />
        </div>

        <!-- rent -->
        <div class="mb-3">
            <label for="rent" class="form-label">Rent Cost (per day)</label>
            <input type="number" name="rent" value="<?php echo $rent ?>" id="rent" class="form-control" required />
        </div>

        <!-- qty -->
        <div class="mb-3">
            <label for="qty" class="form-label">Quantity</label>
            <input type="number" name="qty" value="<?php echo $qty ?>" id="qty" class="form-control" required />
        </div>
        <!-- img -->
        <div class="mb-3">
            <label for="img" class="form-label">Book Image</label>
            <input type="file" name="img" id="img" class="form-control" <?php echo $id > 0 ? '' : 'required' ?> />
            <?php if (!empty($img) && file_exists(BOOK_IMAGE_SERVER_PATH . $img)): ?>
                <div class="mt-2">
                    <p class="small text-muted">Current image:</p>
                    <img src="<?php echo BOOK_IMAGE_SITE_PATH . $img ?>" alt="Current book cover" 
                         style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                </div>
            <?php endif; ?>
        </div>

        <!-- short_desc -->
        <div class="mb-3">
            <label for="short_desc" class="form-label">Short Description</label>
            <textarea name="short_desc" id="short_desc" class="form-control" rows="3"
                required><?php echo $short_desc ?></textarea>
        </div>

        <!-- description -->
        <div class="mb-3">
            <label for="description" class="form-label">Full Description</label>
            <textarea name="description" id="description" class="form-control" rows="5"
                required><?php echo $description ?></textarea>
        </div>
        <div class="mb-1 d-flex justify-content-center field_error">
            <?php echo $msg ?>
        </div>
        <div class="mb-1 d-flex justify-content-center">
            <?php echo $error ?>
        </div>
        <!-- Submit button -->
        <div class="text-center">
            <button type="submit" name="submit" class="btn btn-primary mx-5">Submit</button>
        </div>
    </form>
</main>
<!-- MDB -->
<script type="text/javascript" src="js/mdb.min.js"></script>
<!-- Custom scripts -->
<script type="text/javascript" src="js/admin.js"></script>
</body>

</html>