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
    
    // Validation
    if (empty($category_id)) {
        $msg = "Please select a category";
    } elseif (empty($ISBN)) {
        $msg = "Please enter book ISBN";
    } elseif (empty($name)) {
        $msg = "Please enter book name";
    } elseif (empty($author)) {
        $msg = "Please enter book author";
    } elseif ($security <= 0) {
        $msg = "Security charges must be greater than 0";
    } elseif ($rent <= 0) {
        $msg = "Rent cost must be greater than 0";
    } elseif ($qty <= 0) {
        $msg = "Quantity must be greater than 0";
    } elseif (empty($short_desc)) {
        $msg = "Please enter short description";
    } elseif (empty($description)) {
        $msg = "Please enter description";
    } else {
        // Check if book name already exists (except current book)
        $nameEscaped = mysqli_real_escape_string($con, $name);
        $checkSql = mysqli_query($con, "SELECT id FROM books WHERE name='$nameEscaped'");
        if (mysqli_num_rows($checkSql) > 0) {
            $existing = mysqli_fetch_assoc($checkSql);
            if (!$id || $existing['id'] != $id) {
                $msg = "Book already exists";
            }
        }
    }
    
    if (empty($msg)) {
        if ($id > 0) {
            // Update existing book
            // Nếu có upload ảnh mới, sử dụng ảnh mới
            if (!empty($_FILES['img']['name'])) {
                $img = time() . '_' . $_FILES['img']['name'];
                if (move_uploaded_file($_FILES['img']['tmp_name'], BOOK_IMAGE_SERVER_PATH . $img)) {
                    // Xóa ảnh cũ nếu tồn tại
                    if (!empty($currentImg) && file_exists(BOOK_IMAGE_SERVER_PATH . $currentImg)) {
                        unlink(BOOK_IMAGE_SERVER_PATH . $currentImg);
                    }
                    $sql = "UPDATE books SET category_id=$category_id, ISBN='$ISBN', name='$name', author='$author',
                            security=$security, rent=$rent, qty=$qty, short_desc='$short_desc',
                            description='$description', img='$img' WHERE id=$id";
                } else {
                    $error = "Failed to upload image. Please try again.";
                }
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
                if (move_uploaded_file($_FILES['img']['tmp_name'], BOOK_IMAGE_SERVER_PATH . $img)) {
                    $sql = "INSERT INTO books(category_id, ISBN, name, author, vnd, security, rent, qty, short_desc, description, status, img)
                            VALUES ($category_id, '$ISBN', '$name', '$author', $security, $security, $rent, $qty, '$short_desc', '$description', 1, '$img')";
                } else {
                    $error = "Failed to upload image. Please try again.";
                }
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
                <div class="form-outline mb-4 ms-5">
                    <input type="text" name="ISBN" value="<?php echo $ISBN ?>" id="Book name" class="form-control"
                        required />
                    <label class="form-label" for="Book name">Enter book ISBN</label>
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
        <div class="form-outline mb-4 mx-5">
            <input type="text" name="name" value="<?php echo $name ?>" id="Book name" class="form-control" required />
            <label class="form-label" for="Book name">Enter book name</label>
        </div>

        <!-- Book Author -->
        <div class="form-outline mb-4 mx-5">
            <input type="text" name="author" value="<?php echo $author ?>" id="Book name" class="form-control"
                required />
            <label class="form-label" for="Book name">Enter book author name</label>
        </div>

        <!-- security -->
        <div class="form-outline mb-4 mx-5">
            <input type="number" name="security" value="<?php echo $security ?>" id="Book name" class="form-control"
                required />
            <label class="form-label" for="Book name">Enter book security charges</label>
        </div>

        <!-- rent -->
        <div class="form-outline mb-4 mx-5">
            <input type="number" name="rent" value="<?php echo $rent ?>" id="Book name" class="form-control" required />
            <label class="form-label" for="Book name">Enter book rent Cost</label>
        </div>

        <!-- qty -->
        <div class="form-outline mb-4 mx-5">
            <input type="number" name="qty" value="<?php echo $qty ?>" id="Book name" class="form-control" required />
            <label class="form-label" for="Book name">Enter book quantity</label>
        </div>
        <!-- img -->
        <div class="form-outline mb-4 mx-5">
            <label class="form-label ms-2 p-1" for="Book name">Enter book image</label>
            <input type="file" name="img" id="Book name" class="form-control" />
        </div>

        <!-- short_desc -->
        <div class="form-outline mb-4 mx-5">
            <textarea name="short_desc" id="Book name" class="form-control"
                required><?php echo $short_desc ?></textarea>
            <label class="form-label" for="Book name">Enter book short description</label>
        </div>

        <!-- description -->
        <div class="form-outline mb-4 mx-5">
            <textarea name="description" id="Book name" class="form-control"
                required><?php echo $description ?></textarea>
            <label class="form-label" for="Book name">Enter book description</label>
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