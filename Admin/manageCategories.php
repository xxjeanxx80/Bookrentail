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

?>
<main>
    <div class="container pt-4">
        <h4 class="fs-2 text-center ">Manage Category</h4>
        <hr>
        <br>
    </div>

    <form method="post">
        <div class="form-outline mb-4 mx-5">
            <input type="text" name="category" value="<?php echo $categories ?>" id="category" class="form-control"
                required />
            <label class="form-label" for="category">Enter category name</label>
        </div>
        <div class="mb-1 d-flex justify-content-center field_error">
            <?php echo $msg ?>
        </div>
        <div class="mb-1 d-flex justify-content-center">
            <?php echo $res ?>
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