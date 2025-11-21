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
<!--Main layout-->
<main>
    <div class="container pt-4">
        <h4 class="fs-2 text-center ">Orders</h4>
        <hr>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th> OrderID</th>
                    <th>Order Date</th>
                    <th>Book Name</th>
                    <th>Book Price</th>
                    <th>Rent Duration</th>
                    <th>Address</th>
                    <th>Payment Method</th>
                    <th>Payment Status</th>
                    <th>Order Status</th>
                    <th>Change Order Status</th>
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
            $canChange = !in_array($row['status_name'], ['Returned', 'Cancelled']);
        ?>
                <tr>
                    <td><?php echo $row['id'] ?></td>
                    <td><?php echo $row['date'] ?></td>
                    <td><?php echo $row['name'] ?></td>
                    <td>₫<?php echo $row['total'] ?></td>
                    <td><?php echo $row['duration'] ?> days</td>
                    <td><?php echo $row['address'] ?><?php echo $row['address2'] ? ', ' . $row['address2'] : '' ?></td>
                    <td><?php echo $row['payment_method'] ?></td>
                    <td><?php echo $row['payment_status'] ?></td>
                    <td><?php echo $row['status_name'] ?></td>
                    <td>
                        <?php if ($canChange): ?>
                        <form method="post">
                            <input type="hidden" name="orderId" value="<?php echo $row['id'] ?>">
                            <select class="form-select" name="status_id">
                                <option value="">Select Status</option>
                                <?php
                                $statusSql = mysqli_query($con, "SELECT * FROM order_status ORDER BY status_name");
                                while ($statusRow = mysqli_fetch_assoc($statusSql)):
                                ?>
                                <option value="<?php echo $statusRow['id'] ?>"><?php echo $statusRow['status_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                            <input type="submit" value="Submit" class="btn btn-primary mt-2">
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>
<!-- MDB -->
<script type="text/javascript" src="js/mdb.min.js"></script>
<!-- Custom scripts -->
<script type="text/javascript" src="js/admin.js"></script>
</body>

</html>