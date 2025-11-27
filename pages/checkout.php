<?php require(__DIR__ . '/../includes/header.php') ?>
<?php
if (!isset($_SESSION['USER_LOGIN'])) {
    header('Location: SignIn.php');
    exit;
}

$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$duration = isset($_GET['duration']) ? (int)$_GET['duration'] : 0;

if (!$bookId || !$duration) {
    header('Location: index.php');
    exit;
}

$booksList = getProduct($con, '', '', $bookId);
if (empty($booksList)) {
    header('Location: index.php');
    exit;
}

$bookData = $booksList[0];
$totalRent = $bookData['rent'] * $duration;
$totalPrice = $totalRent + $bookData['security'];


if (isset($_POST['submit'])) {
    $address = trim($_POST['address']);
    $address2 = trim($_POST['address2'] ?? '');
    $pin = (int)$_POST['pin'];
    $paymentMethod = trim($_POST['paymentMethod']);
    
    // Escape for SQL
    $address = mysqli_real_escape_string($con, $address);
    $address2 = mysqli_real_escape_string($con, $address2);
    $paymentMethod = mysqli_real_escape_string($con, $paymentMethod);
    
    $userId = (int)$_SESSION['USER_ID'];
    $paymentStatus = ($paymentMethod == 'COD') ? 'success' : 'pending';
    
    date_default_timezone_set('Asia/Kolkata');
    $date = date('Y-m-d H:i:s');
    
    // Insert order
    $sql = "INSERT INTO orders(user_id, address, address2, pin, payment_method, total, payment_status, order_status, date, duration)
            VALUES ($userId, '$address', '$address2', $pin, '$paymentMethod', $totalPrice, '$paymentStatus', 1, '$date', $duration)";
    mysqli_query($con, $sql);
    
    $orderId = mysqli_insert_id($con);
    
    // Insert order detail
    mysqli_query($con, "INSERT INTO order_detail(order_id, book_id, price, time) VALUES ($orderId, $bookId, $totalPrice, $duration)");
    
    // Update book quantity
    mysqli_query($con, "UPDATE books SET qty = qty - 1 WHERE id = $bookId");
    
    header("Location: thankYou.php?orderId=$orderId");
    exit;
}
?>
<script>
document.title = "Checkout | Book Rental";
</script>
<div class="container">
    <main>
        <div class="py-5 text-center">
            <h2>Checkout</h2>
        </div>
        <div class="row g-5">
            <div class="col-md-5 col-lg-4 order-md-last">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-primary">Your book</span>
                </h4>
                <ul class="list-group mb-3 dark-background">
                    <li class="list-group-item d-flex justify-content-center fw-bold lh-sm">
                        <div>
                            <h2 class="my-0 fs-5 fw-bold"><?php echo $bookData['name'] ?></h2>
                        </div>
                    </li>
                    <li class="list-group-item justify-content-start lh-sm">
                        <p><span class="fw-bold">Rent Price</span> = ₫<?php echo $bookData['rent'] ?> Per Day</p>
                        <p><span class="fw-bold">Duration</span> = <?php echo $duration ?> Days</p>
                        <p><span class="fw-bold">Total Rent</span> = ₫<?php echo $totalRent ?></p>
                        <p><span class="fw-bold">Security Deposit<span style="color: red"><sup>*</sup></span></span> = ₫<?php echo $bookData['security'] ?></p>
                        <p><span class="fw-bold">Total price</span> = ₫<?php echo $totalPrice ?></p>
                    </li>
                </ul>
                <ul class="list-group mb-3">
                    <li class="list-group-item d-flex justify-content-center fw-bold lh-sm">
                        <div>
                            <h2 class="my-0 fs-5 fw-bold"><span style="color: red"><sup>*</sup></span>Deposit Terms</h2>
                        </div>
                    </li>
                    <li class="list-group-item justify-content-start lh-sm">
                        <ol type="1">
                            <li>
                                You need to submit a photocopy and show Aadhar Card in original to the delivery person.
                            </li>
                            <li>
                                Security Deposit is refundable once we receive the book in the proper condition.
                            </li>
                        </ol>
                    </li>
                </ul>
            </div>
            <div class="col-md-7 col-lg-8">
                <form class="needs-validation" method="post" novalidate>
                    <h4 class="mb-3">Shipping address</h4>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="address" class="form-label">Address Line 1</label>
                            <input type="text" class="form-control" name="address" placeholder="12 Main St" required>
                            <div class="invalid-feedback">
                                Please enter your shipping address.
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="address2" class="form-label">Address Line 2 </label>
                            <input type="text" class="form-control" name="address2" placeholder="">
                        </div>

                        <div class="col-3">
                            <label for="pin" class="form-label">Pin Code</label>
                            <input type="number" maxlength="6" class="form-control" name="pin" placeholder="246401"
                                required>
                            <div class="invalid-feedback">
                                Pin code required.
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="my-3">
                        <div class="form-check">
                            <input id="cod" name="paymentMethod" type="radio" value="COD" class="form-check-input"
                                checked required>
                            <label class="form-check-label" for="cod">Cash on delivery(COD)</label>
                        </div>
                        <div class="form-check">
                            <input id="payU" name="paymentMethod" type="radio" value="payU" class="form-check-input"
                                required disabled>
                            <label class="form-check-label" for="payU">Online Payment</label>
                        </div>
                    </div>

                    <hr class="my-4 invisible">

                    <button class="w-100 btn btn-primary btn-lg" type="submit" name="submit">Place Your Order
                    </button>
                </form>
            </div>
        </div>
    </main>


</div>
<script>
(function() {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    let forms = document.querySelectorAll('.needs-validation');

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
})()
</script>
<?php require(__DIR__ . '/../includes/footer.php') ?>