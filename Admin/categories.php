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
<!--Main layout-->
<main>
    <div class="container pt-4">
        <h4 class="fs-2 text-center ">Categories</h4>
        <hr>
        <br>
    </div>
    <h5 class="ms-5 fs-6"><a href="manageCategories.php">Add Categories</a></h5>
    <div class="">
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
    </div>
</main>
<script type="text/javascript" src="js/admin.js"></script>
</body>

</html>