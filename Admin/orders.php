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

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['status_id'])) {
    $orderId = (int)$_POST['orderId'];
    $statusId = (int)$_POST['status_id'];
    
    // Nếu đơn hàng bị hủy hoặc trả lại, tăng lại số lượng sách
    if (in_array($statusId, [4, 6])) {
        $qtyRes = mysqli_query($con, "SELECT books.id FROM orders
                                       JOIN order_detail ON orders.id=order_detail.order_id
                                       JOIN books ON order_detail.book_id=books.id
                                       WHERE order_detail.order_id=$orderId");
        if ($qtyRow = mysqli_fetch_assoc($qtyRes)) {
            mysqli_query($con, "UPDATE books SET qty = qty + 1 WHERE id={$qtyRow['id']}");
        }
    }
    
    mysqli_query($con, "UPDATE orders SET order_status=$statusId WHERE id=$orderId");
    header('Location: orders.php');
    exit;
}

require('topNav.php');
?>
<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <h1>Orders
            <hr>
        </h1>
    </div>
    <table class="table table-responsive">
        <thead class="">
            <tr>
                <th>Order ID</th>
                <th>Order Date</th>
                <th>Book Name</th>
                <th>Price</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $res = mysqli_query($con, "SELECT orders.*, name, status_name FROM orders
                                        JOIN order_detail ON orders.id=order_detail.order_id
                                        JOIN books ON order_detail.book_id=books.id
                                        JOIN order_status ON orders.order_status=order_status.id
                                        ORDER BY date DESC");
            while ($row = mysqli_fetch_assoc($res)):
            ?>
            <tr>
                <td>#<?php echo $row['id'] ?></td>
                <td><?php echo $row['date'] ?></td>
                <td><?php echo htmlspecialchars($row['name']) ?></td>
                <td>₫<?php echo number_format($row['total']) ?></td>
                <td>
                    <?php
                    $statusClass = [
                        'Pending' => 'secondary',
                        'Processing' => 'primary',
                        'Shipped' => 'info',
                        'Delivered' => 'success',
                        'Cancelled' => 'danger',
                        'Returned' => 'warning'
                    ];
                    $class = $statusClass[$row['status_name']] ?? 'secondary';
                    ?>
                    <span class="badge bg-<?php echo $class ?>"><?php echo htmlspecialchars($row['status_name']) ?></span>
                </td>
                <td>
                    <a class="btn btn-info btn-sm" href="orderDetails.php?id=<?php echo $row['id'] ?>">
                        View Details
                    </a>
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