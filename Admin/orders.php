<?php
require('topNav.php');
?>

<?php
// ============================================================================
// ORDERS MANAGEMENT - CLEAN CODE GIẢI THÍCH TẠI SAU
// ============================================================================

// Xử lý order status update từ form POST
// Giải thích: Admin có thể thay đổi trạng thái đơn hàng
// NGHIỆP VỤ: Cập nhật tiến trình đơn hàng và tự động khôi phục số lượng sách khi trả/hủy
if (isset($_POST['status_id'])) {
  $order_Id = $_POST['orderId'];
  $status_id = $_POST['status_id'];
  
  // Nếu order là returned (6) hoặc cancelled (4), khôi phục số lượng sách
  // LOGIC: Status 4=Cancelled, 6=Returned -> cần tăng lại qty sách
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

// Lấy tất cả đơn hàng với thông tin chi tiết
// Giải thích: JOIN 4 bảng để hiển thị đầy đủ thông tin cho admin
$res = pg_query($con, "select orders.*,name,status_name from orders
                                            JOIN order_detail ON orders.id=order_detail.order_id
                                            JOIN books ON order_detail.book_id=books.id
                                            JOIN order_status ON orders.order_status=order_status.id order by date desc ");

?>

<!--Main layout-->
<main>
    <div class="container pt-4">
        <h4 class="fs-2 text-center ">Orders</h4>
        <hr>
    </div>
    <div class="card-body">
        <table class="table orders-table">
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
        // Hiển thị danh sách đơn hàng
        // Giải thích: Loop qua từng đơn hàng và hiển thị thông tin chi tiết
        while ($row = pg_fetch_assoc($res)) { ?>
                <tr>
                    <td> <?php echo $row['id'] ?> </td>
                    <?php $orderId = $row['id'] ?>
                    <td> <?php echo $row['date'] ?> </td>
                    <td> <?php echo $row['name'] ?> </td>
                    <td> <?php echo $row['total'] ?> </td>
                    <td> <?php echo $row['duration'] ?> </td>
                    <td> <?php echo $row['address'] ?>, <?php echo $row['address2'] ?> </td>
                    <td> <?php echo $row['payment_method'] ?> </td>
                    <td> <?php echo $row['payment_status'] ?> </td>
                    <td> <?php echo $row['status_name'] ?> </td>
                    <td>
                        <?php
                        // Hiển thị form thay đổi trạng thái đơn hàng
                        // Giải thích: Chỉ cho phép thay đổi nếu chưa Returned/Cancelled
              $statusName = $row['status_name'];
              if ($statusName === 'Returned' || $statusName === 'Cancelled') {
              } else {
              ?>
                        <form method="post">
                            <input type="hidden" class="" value="<?php echo $orderId ?>" name="orderId">
                            <select class="form-select" name="status_id">
                                <option class="">Select Status</option>
                                <?php
                    // Lấy danh sách tất cả trạng thái đơn hàng
                    // Giải thích: Dynamic dropdown từ database order_status
                    $sql = pg_query($con, "select * from order_status order by status_name");
                    while ($row = pg_fetch_assoc($sql)) {
                      echo "<option value=" . $row['id'] . ">" . $row['status_name'] . "</option>";
                    }
                    ?>
                            </select>

                            <input type="submit" VALUE="Submit" class="btn btn btn-primary mt-2">
                        </form>
                        <?php
              } ?>
                    </td>
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
