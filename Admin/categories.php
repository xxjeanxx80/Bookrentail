<?php
// Xử lý action TRƯỚC KHI require topNav (để tránh lỗi headers already sent)
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

// Xử lý action
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

require('topNav.php');

$sql = "select * from categories order by category asc";
$res = mysqli_query($con, $sql);
?>
<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <h1>Categories
            <hr>
        </h1>
    </div>
    <h5 class="btn btn-white ms-5 px-2 py-1 fs-6 "><a class="link-dark" href="manageCategories.php">Add Category</a></h5>
    <table class="table table-responsive">
        <thead class="">
            <tr>
                <th>Category Name</th>
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
                        <span class="badge bg-success">Active</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="window.location.href='manageCategories.php?id=<?php echo $row['id'] ?>'">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="if(confirm('Are you sure you want to delete this category?')) window.location.href='?type=delete&id=<?php echo $row['id'] ?>'">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<script type="text/javascript" src="js/admin.js"></script>
</body>

</html>