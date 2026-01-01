<?php require(__DIR__ . '/../includes/header.php') ?>
<?php
if (!isset($_SESSION['USER_LOGIN'])) {
    header('Location: SignIn.php');
    exit;
}

// Handle feedback submission
if (isset($_POST['submit_feedback'])) {
    $orderId = (int)$_POST['order_id'];
    $bookId = (int)$_POST['book_id'];
    $userId = (int)$_SESSION['USER_ID'];
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    
    if (saveFeedback($con, $orderId, $bookId, $userId, $rating, $comment)) {
        $feedback_msg = '<div class="alert alert-success">✅ Thank you for your feedback!</div>';
    } else {
        $feedback_msg = '<div class="alert alert-danger">❌ Error submitting feedback. Please try again.</div>';
    }
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
    
    <?php if (isset($feedback_msg)) echo $feedback_msg; ?>
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $userId = (int)$_SESSION['USER_ID'];
            $res = mysqli_query($con, "SELECT orders.*, order_detail.book_id, books.name, books.author, status_name FROM orders
                                       JOIN order_detail ON orders.id=order_detail.order_id
                                       JOIN books ON order_detail.book_id=books.id
                                       JOIN order_status ON orders.order_status=order_status.id
                                       WHERE user_id=$userId ORDER BY orders.id DESC");
            while ($row = mysqli_fetch_assoc($res)): 
                $canCancel = !in_array($row['status_name'], ['Cancelled', 'Returned', 'Delivered']);
                $canFeedback = ($row['status_name'] == 'Delivered');
                $hasFeedback = hasUserFeedback($con, $row['id'], $row['book_id'], $userId);
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
                    <?php if ($canFeedback && !$hasFeedback): ?>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#feedbackModal<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>">
                            Rate Book
                        </button>
                    <?php elseif ($hasFeedback): ?>
                        <span class="badge bg-success">✓ Rated</span>
                    <?php endif; ?>
                </td>
            </tr>
            
            <!-- Feedback Modal -->
            <?php if ($canFeedback && !$hasFeedback): ?>
            <div class="modal fade" id="feedbackModal<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Rate Your Experience</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="post">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Book:</strong> <?php echo htmlspecialchars($row['name']) ?></label>
                                    <br><small class="text-muted">Author: <?php echo htmlspecialchars($row['author']) ?></small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Your Rating *</label>
                                    <div class="star-rating">
                                        <input type="radio" id="star5_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>" name="rating" value="5" required>
                                        <label for="star5_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>"></label>
                                        
                                        <input type="radio" id="star4_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>" name="rating" value="4">
                                        <label for="star4_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>"></label>
                                        
                                        <input type="radio" id="star3_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>" name="rating" value="3">
                                        <label for="star3_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>"></label>
                                        
                                        <input type="radio" id="star2_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>" name="rating" value="2">
                                        <label for="star2_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>"></label>
                                        
                                        <input type="radio" id="star1_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>" name="rating" value="1">
                                        <label for="star1_<?php echo $row['id'] ?>_<?php echo $row['book_id'] ?>"></label>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="comment" class="form-label">Your Review (Optional)</label>
                                    <textarea class="form-control" id="comment" name="comment" rows="3" 
                                              placeholder="Share your experience with this book..."></textarea>
                                </div>
                                
                                <input type="hidden" name="order_id" value="<?php echo $row['id'] ?>">
                                <input type="hidden" name="book_id" value="<?php echo $row['book_id'] ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>
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

</body>

</html>