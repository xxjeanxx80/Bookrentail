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

// Xử lý action (delete user)
if (isset($_GET['type']) && $_GET['type'] != ' ') {
    $type = trim($_GET['type']);
    
    if ($type == 'delete') {
        $id = (int)$_GET['id'];
        $deleteSql = "DELETE FROM users WHERE id=$id";
        mysqli_query($con, $deleteSql);
        // Redirect để tránh resubmit form
        header('Location: users.php');
        exit;
    }
}

// Sau khi xử lý xong tất cả logic, mới require topNav để hiển thị HTML
require('topNav.php');

$sql = "select * from users order by id desc";
$res = mysqli_query($con, $sql);
?>
<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <h1>Users
            <hr>
        </h1>
    </div>
    <table class="table table-responsive">
        <thead class="">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Joined Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td>#<?php echo $row['id'] ?></td>
                <td><?php echo htmlspecialchars($row['name']) ?></td>
                <td><?php echo htmlspecialchars($row['email']) ?></td>
                <td><?php echo htmlspecialchars($row['mobile']) ?></td>
                <td><?php echo htmlspecialchars($row['doj']) ?></td>
                <td>
                    <button class="btn btn-danger btn-sm" onclick="if(confirm('Are you sure you want to delete this user?')) window.location.href='?type=delete&id=<?php echo $row['id'] ?>'">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<!-- MDB -->
<script type="text/javascript" src="js/mdb.min.js"></script>
<!-- Custom scripts -->
<script type="text/javascript" src="js/admin.js"></script>
</body>

</html>