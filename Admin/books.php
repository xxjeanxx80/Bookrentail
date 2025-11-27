<?php
// Xử lý các action TRƯỚC KHI require topNav (để tránh lỗi headers already sent)
// Cần require connection và function trước để có $con và các hàm hỗ trợ
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

// Xử lý các action
if (isset($_GET['type']) && $_GET['type'] != ' ') {
    $type = trim($_GET['type']);
    $id = (int)$_GET['id'];
    
    if ($type == 'status') {
        $status = ($_GET['operation'] == 'active') ? 1 : 0;
        mysqli_query($con, "UPDATE books SET status=$status WHERE id=$id");
    } elseif ($type == 'best_seller') {
        $bestSeller = ($_GET['operation'] == 'active') ? 1 : 0;
        mysqli_query($con, "UPDATE books SET best_seller=$bestSeller WHERE id=$id");
    } elseif ($type == 'delete') {
        mysqli_query($con, "DELETE FROM books WHERE id=$id");
    }
    
    header('Location: books.php');
    exit;
}

// Lấy danh sách sách
$sql = "SELECT books.*, categories.category 
        FROM books 
        LEFT JOIN categories ON books.category_id=categories.id 
        ORDER BY books.name ASC";
$res = mysqli_query($con, $sql);

// Bây giờ mới require topNav (đã có connection và function rồi, nên require_once sẽ skip)
require('topNav.php');
?>
<!--Main layout-->
<main>
    <div class="container pt-4">
        <h4 class="fs-2 text-center ">Books</h4>
        <hr>
        <br>
    </div>
    <h5 class="btn btn-white ms-5 px-2 py-1 fs-6 "><a class="link-dark" href="manageBooks.php">Add Book</a></h5>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ISBN</th>
                    <th>Category</th>
                    <th>img</th>
                    <th>Name</th>
                    <th>Author</th>
                    <th>Security</th>
                    <th>Rent</th>
                    <th>Price</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['ISBN']) ?></td>
                    <td><?php echo htmlspecialchars($row['category'] ?? 'N/A') ?></td>
                    <td>
                        <img src="<?php echo BOOK_IMAGE_SITE_PATH . $row['img'] ?>" 
                             class="card-img" height="60px" width="80px" alt="Book cover">
                    </td>
                    <td><?php echo htmlspecialchars($row['name']) ?></td>
                    <td><?php echo htmlspecialchars($row['author']) ?></td>
                    <td>₫<?php echo number_format($row['security']) ?></td>
                    <td>₫<?php echo number_format($row['rent']) ?>/day</td>
                    <td>₫<?php echo number_format($row['price']) ?></td>
                    <td><?php echo $row['qty'] ?></td>
                    <td>
                        <?php if ($row['best_seller'] == 1): ?>
                            <a href="?type=best_seller&operation=deactive&id=<?php echo $row['id'] ?>">Most Viewed</a>
                        <?php else: ?>
                            <a href="?type=best_seller&operation=active&id=<?php echo $row['id'] ?>">Normal</a>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="manageBooks.php?id=<?php echo $row['id'] ?>">Edit</a> | 
                        <a href="?type=delete&id=<?php echo $row['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
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