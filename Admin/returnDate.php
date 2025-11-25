<?php
require('topNav.php');

// ============================================================================
// RETURN DATE MANAGEMENT - CLEAN CODE GIẢI THÍCH TẠI SAU
// ============================================================================

// Xử lý order status update từ form POST
// Giải thích: Admin có thể thay đổi trạng thái đơn hàng
// NGHIỆP VỤ: Cập nhật tiến trình đơn hàng và tự động khôi phục số lượng sách khi trả/hủy
if (isset($_POST['status_id'])) {
    $order_Id = $_POST['orderId'];
    $status_id = $_POST['status_id'];
    if ($status_id === 6 || $status_id === 4) {
        // Lấy thông tin sách từ đơn hàng để khôi phục số lượng
        // Giải thích: JOIN qua 3 bảng để lấy book id và current quantity
        $qtyRes = pg_query($con, "SELECT books.qty,books.id FROM orders
                                            JOIN order_detail ON orders.id=order_detail.order_id
                                            JOIN books ON order_detail.book_id=books.id
                                            where order_detail.order_id='$order_Id'");
        $qtyRow = pg_fetch_assoc($qtyRes);
        $newQty = $qtyRow['qty'] + 1;
        $bookId = $qtyRow['id'];
        // Cập nhật số lượng sách (khôi phục 1 quyển)
        pg_query($con, "UPDATE books SET qty = '$newQty' WHERE id='$bookId';");
    }

    // Cập nhật trạng thái đơn hàng
    pg_query($con, "update orders set order_status='$status_id' where id='$order_Id'");
}

// Lấy danh sách đơn hàng đã hủy/trả
// Giải thích: SELECT orders với status LIKE '%Cancelled%' để hiển thị trong returnDate page
$sql = "select orders.*,name,status_name from orders
                                        JOIN order_detail ON orders.id=order_detail.order_id
                                        JOIN books ON order_detail.book_id=books.id
                                        JOIN order_status ON orders.order_status=order_status.id 
                                        WHERE order_status.status_name LIKE '%Cancelled%'
                                        order by date desc";
$res = pg_query($con, $sql);

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
                    <th>Return Date</th>
                    <th>Book Name</th>
                    <th>Book Price</th>
                    <th>Rent Duration</th>
                    <th>Address</th>
                    <th>Order Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
        // Hiển thị danh sách đơn hàng đã hủy/trả
        // Giải thích: Loop qua từng đơn hàng và hiển thị thông tin
        while ($row = pg_fetch_assoc($res)) { ?>
                <tr>
                    <td> <?php echo $row['id'] ?> </td>
                    <td> <?php echo $row['date'] ?> </td>
                    <td>haha</td>
                    <td> <?php echo $row['name'] ?> </td>
                    <td> <?php echo $row['total'] ?> </td>
                    <td> <?php echo $row['duration'] ?> </td>
                    <td> <?php echo $row['address'] ?>, <?php echo $row['address2'] ?> </td>
                    <td> <?php echo $row['status_name'] ?> </td>
                </tr>
                <?php } ?>
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
