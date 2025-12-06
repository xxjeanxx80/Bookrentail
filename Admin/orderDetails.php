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

// Lấy order ID từ URL
$orderId = (int)$_GET['id'];
if ($orderId == 0) {
    header('Location: orders.php');
    exit;
}

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['status_id'])) {
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
    header("Location: orderDetails.php?id=$orderId");
    exit;
}

// Lấy thông tin đơn hàng
$orderSql = "SELECT orders.*, name, status_name FROM orders
             JOIN order_detail ON orders.id=order_detail.order_id
             JOIN books ON order_detail.book_id=books.id
             JOIN order_status ON orders.order_status=order_status.id
             WHERE orders.id=$orderId";
$orderResult = mysqli_query($con, $orderSql);

if (!$orderResult || mysqli_num_rows($orderResult) == 0) {
    header('Location: orders.php');
    exit;
}

$order = mysqli_fetch_assoc($orderResult);
$canChange = !in_array($order['status_name'], ['Returned', 'Cancelled', 'Delivered']);

require('topNav.php');
?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex justify-content-center flex-grow-1">
            <h1>Order Details #<?php echo $order['id'] ?>
                <hr>
            </h1>
        </div>
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> #<?php echo $order['id'] ?></p>
                            <p><strong>Order Date:</strong> <?php echo $order['date'] ?></p>
                            <p><strong>Book Name:</strong> <?php echo htmlspecialchars($order['name']) ?></p>
                            <p><strong>Price:</strong> ₫<?php echo number_format($order['total']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Rent Duration:</strong> <?php echo $order['duration'] ?> days</p>
                            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']) ?></p>
                            <p><strong>Payment Status:</strong> 
                                <span class="badge bg-info"><?php echo htmlspecialchars($order['payment_status']) ?></span>
                            </p>
                            <p><strong>Current Status:</strong> 
                                <?php
                                $statusClass = [
                                    'Pending' => 'secondary',
                                    'Processing' => 'primary',
                                    'Shipped' => 'info',
                                    'Delivered' => 'success',
                                    'Cancelled' => 'danger',
                                    'Returned' => 'warning'
                                ];
                                $class = $statusClass[$order['status_name']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $class ?>"><?php echo htmlspecialchars($order['status_name']) ?></span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p><strong>Delivery Address:</strong></p>
                        <p class="ms-3"><?php echo htmlspecialchars($order['address']) ?><?php echo $order['address2'] ? ', ' . htmlspecialchars($order['address2']) : '' ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Order Status</h5>
                </div>
                <div class="card-body">
                    <?php if ($canChange): ?>
                    <form method="post">
                        <div class="mb-3">
                            <label for="status_id" class="form-label">New Status:</label>
                            <select class="form-select" id="status_id" name="status_id" required>
                                <option value="">Select New Status</option>
                                <?php
                                $statusSql = mysqli_query($con, "SELECT * FROM order_status ORDER BY status_name");
                                while ($statusRow = mysqli_fetch_assoc($statusSql)):
                                    if ($statusRow['id'] != $order['order_status']): // Don't show current status
                                ?>
                                <option value="<?php echo $statusRow['id'] ?>"><?php echo htmlspecialchars($statusRow['status_name']) ?></option>
                                <?php 
                                    endif;
                                endwhile; 
                                ?>
                            </select>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        This order is in final status and cannot be changed.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MDB -->
<script type="text/javascript" src="js/mdb.min.js"></script>
<!-- Custom scripts -->
<script type="text/javascript" src="js/admin.js"></script>
</body>
</html>
