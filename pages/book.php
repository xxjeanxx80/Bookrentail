<?php require(__DIR__ . '/../includes/header.php') ?>
<?php
$bookId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$getProduct = getProduct($con, '', '', $bookId);

if (empty($getProduct)) {
    header('Location: index.php');
    exit;
}

$book = $getProduct[0];

if (isset($_GET['submit'])) {
    $duration = (int)$_GET['duration'];
    $id = (int)$_GET['bookId'];
    if ($duration >= 10 && $duration <= 200) {
        $_SESSION['BeforeCheckoutLogin'] = "checkout.php?id=$id&duration=$duration";
        header("Location: checkout.php?id=$id&duration=$duration");
        exit;
    }
}
?>
<script>
document.title = "<?php echo $book['name'] ?> | Book Rental";
</script>
<div class="container-fluid py-5">
    <div class="row mb-3">
        <div class="col-6 col-sm-6 col-md-3 mt-3">
            <img class="card card-img-top border-dark rounded"
                src="<?php echo BOOK_IMAGE_SITE_PATH . $book['img'] ?>" height="396rem" width="260rem">
        </div>
        <div class="col-12 col-md-9">
            <h2 id="bookName" class="text-uppercase font-weight-bold"><?php echo $book['name'] ?></h2>
            <hr>
            <h6>ISBN:- <span class="fw-normal"><?php echo $book['ISBN'] ?></span></h6>
            <h6>Author:- <span class="fw-normal"><?php echo $book['author'] ?></span></h6>
            <p>
                <span class="fs-4 fw-bolder">₫<?php echo $book['rent'] ?></span>
                <span class="fs-5"><strong>(Per Day)</strong></span>
            </p>

            <?php if ($book['qty'] == 0): ?>
                <p class="fs-4 text-danger">Currently out of stock</p>
            <?php else: ?>
                <button id="toggle" class="btn-primary btn" onclick="showDiv()">Rent</button>
            <?php endif; ?>

            <script>
            function showDiv() {
                document.getElementById("after-rent").style.display = "block";
                document.getElementById("toggle").style.display = "none";
            }
            </script>
            <div id="after-rent" class="mb-4" style="display:none">
                <form method="get">
                    <input type="hidden" name="bookId" value="<?php echo $book['id'] ?>">
                    <h4 class="mb-3">Enter the duration of renting (in days)</h4>
                    <div class="col-2 d-flex">
                        <input type="number" class="form-control" name="duration" min="10" max="200" placeholder="Days" required>
                        <input type="submit" name="submit" value="Rent" class="btn-primary btn ms-3">
                    </div>
                </form>
            </div>

            <h6 class="fw-bold fs-5 my-3">Short Description</h6>
            <p id="shortDescription" class="mt-3 text-justify"><?php echo $book['short_desc'] ?></p>
        </div>
    </div>
    <div class="accordion" id="accordion">
        <div class="accordion-item">
            <h2 class="accordion-header" id="headingOne">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    <h6 class="fs-5 ms-4">Description</h6>
                </button>
            </h2>
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                data-bs-parent="#accordion">
                <div class="accordion-body">
                    <p id="description" class="mb-3 text-justify"><?php echo $book['description'] ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require(__DIR__ . '/../includes/footer.php') ?>