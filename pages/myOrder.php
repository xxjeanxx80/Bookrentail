<?php require(__DIR__ . '/../includes/header.php') ?>
<?php
if (!isset($_SESSION['USER_LOGIN'])) {
    header('Location: SignIn.php');
    exit;
}

if (isset($_GET['type']) && $_GET['type'] == 'cancel') {
    $id = (int)$_GET['id'];
    mysqli_query($con, "UPDATE orders SET order_status=4 WHERE id=$id");
    
    $qtyRes = mysqli_query($con, "SELECT books.qty, books.id FROM orders
                                  JOIN order_detail ON orders.id=order_detail.order_id
                                  JOIN books ON order_detail.book_id=books.id
                                  WHERE order_detail.order_id=$id");
    if ($qtyRow = mysqli_fetch_assoc($qtyRes)) {
        mysqli_query($con, "UPDATE books SET qty = qty + 1 WHERE id={$qtyRow['id']}");
    }
    header('Location: myOrder.php');
    exit;
}
?>
<script>
document.title = "My Orders | Book Rental";
</script>
<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <h1>My Orders
            <hr>
        </h1>
    </div>
    <table class="table table-responsive">
        <thead class="">
            <tr>
                <th> OrderID</th>
                <th>Order Date</th>
                <th>Book Name</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Address</th>
                <th>Payment Method</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $userId = (int)$_SESSION['USER_ID'];
            $res = mysqli_query($con, "SELECT orders.*, name, status_name FROM orders
                                       JOIN order_detail ON orders.id=order_detail.order_id
                                       JOIN books ON order_detail.book_id=books.id
                                       JOIN order_status ON orders.order_status=order_status.id
                                       WHERE user_id=$userId ORDER BY orders.id DESC");
            while ($row = mysqli_fetch_assoc($res)): 
                $canCancel = !in_array($row['status_name'], ['Cancelled', 'Returned']);
            ?>
            <tr>
                <td>#<?php echo $row['id'] ?></td>
                <td><?php echo $row['date'] ?></td>
                <td><?php echo $row['name'] ?></td>
                <td>₫<?php echo $row['total'] ?></td>
                <td><?php echo $row['duration'] ?> days</td>
                <td><?php echo $row['address'] ?><?php echo $row['address2'] ? ', ' . $row['address2'] : '' ?></td>
                <td><?php echo $row['payment_method'] ?></td>
                <td><?php echo $row['payment_status'] ?></td>
                <td><?php echo $row['status_name'] ?></td>
                <td>
                    <?php if ($canCancel): ?>
                        <a class="btn btn-danger btn-sm" href="?type=cancel&id=<?php echo $row['id'] ?>">Cancel</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<div id="scrollBtn">
    <button onclick="topFunction()" id="ScrollUpBtn" title="Go to top">
        <span> <i class="fas fa-chevron-up text-white"></i></span>
    </button>
    <script>
    let mybutton = document.getElementById("ScrollUpBtn");

    window.onscroll = function() {
        scrollFunction();
    };

    function scrollFunction() {
        if (
            document.body.scrollTop > 20 ||
            document.documentElement.scrollTop > 20
        ) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    function topFunction() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
    </script>
</div>

<!--------------------------------------------------DARK MODE BUTTON----------------------------------------------------------->
<div id="dark-btn">
    <button onclick="DarkMode()" id="dark-btn" title="Toggle Light/Dark Mode">
        <span><i class="fas fa-adjust fa-lg text-white"></i></span>
    </button>
    <script>
    //Dark Mode
    function DarkMode() {
        let element = document.body;
        element.classList.toggle("dark-mode");
    }
    </script>
</div>
</body>

</html>