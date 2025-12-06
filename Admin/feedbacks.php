<<<<<<< Updated upstream
<?php require(__DIR__ . '/topNav.php') ?>
<?php
require_once(__DIR__ . '/../includes/function.php');

// Handle delete feedback
if (isset($_GET['type']) && $_GET['type'] == 'delete') {
    $id = (int)$_GET['id'];
    mysqli_query($con, "DELETE FROM feedback WHERE id=$id");
    header('Location: feedbacks.php');
    exit;
}

// Get all feedbacks
$feedbacks = getAllFeedbacks($con);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <h1>Customer Feedbacks
            <hr>
        </h1>
    </div>
    <table class="table table-responsive">
        <thead class="">
            <tr>
                <th>Order ID</th>
                <th>Book Name</th>
                <th>Customer</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($feedbacks)): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td>#<?php echo $feedback['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($feedback['book_name']); ?></td>
                    <td><?php echo htmlspecialchars($feedback['user_name']); ?></td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                        (<?php echo $feedback['rating']; ?>/5)
                    </td>
                    <td><?php echo !empty($feedback['comment']) ? htmlspecialchars($feedback['comment']) : 'No comment'; ?></td>
                    <td><?php echo $feedback['created_at']; ?></td>
                    <td>
                        <a class="btn btn-danger btn-sm" href="?type=delete&id=<?php echo $feedback['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this feedback?')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No feedbacks found</td>
                </tr>
            <?php endif; ?>
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
=======
<?php require(__DIR__ . '/topNav.php') ?>
<?php
require_once(__DIR__ . '/../includes/function.php');

// Handle delete feedback
if (isset($_GET['type']) && $_GET['type'] == 'delete') {
    $id = (int)$_GET['id'];
    mysqli_query($con, "DELETE FROM feedback WHERE id=$id");
    header('Location: feedbacks.php');
    exit;
}

// Get all feedbacks
$feedbacks = getAllFeedbacks($con);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-center">
        <h1>Customer Feedbacks
            <hr>
        </h1>
    </div>
    <table class="table table-responsive">
        <thead class="">
            <tr>
                <th>Order ID</th>
                <th>Book Name</th>
                <th>Customer</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($feedbacks)): ?>
                <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td>#<?php echo $feedback['order_id']; ?></td>
                    <td><?php echo htmlspecialchars($feedback['book_name']); ?></td>
                    <td><?php echo htmlspecialchars($feedback['user_name']); ?></td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                        (<?php echo $feedback['rating']; ?>/5)
                    </td>
                    <td><?php echo !empty($feedback['comment']) ? htmlspecialchars($feedback['comment']) : 'No comment'; ?></td>
                    <td><?php echo $feedback['created_at']; ?></td>
                    <td>
                        <a class="btn btn-danger btn-sm" href="?type=delete&id=<?php echo $feedback['id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this feedback?')">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">No feedbacks found</td>
                </tr>
            <?php endif; ?>
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
>>>>>>> Stashed changes
