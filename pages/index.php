<?php require(__DIR__ . '/../includes/header.php') ?>

<div id="myCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="0" class="active" aria-current="true"
            aria-label="Slide 1"></button>
        <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
        <button type="button" data-bs-target="#myCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="../assets/img/carousel/carousel1.png" alt="" class="img-fluid" />
            <div class="container">
                <div class="carousel-caption text-start"></div>
            </div>
        </div>
        <div class="carousel-item">
            <img src="../assets/img/carousel/carousel2.jpeg" alt="" class="img-fluid" />

            <div class="container">
                <div class="carousel-caption text-end"></div>
            </div>
        </div>
        <div class="carousel-item">
            <img src="../assets/img/carousel/carousel3.jpg" alt="" class="img-fluid" />

            <div class="carousel-caption text-start carousel-justify mt-5">
                <br /><br /><br />
                <p> Dear Readers,</p>
                <br />
                <p>
                    Due to a high volume of orders, deliveries may take longer than usual. We are working hard to process your requests as quickly as possible. Thank you for your patience and continued support.
                </p>
                <p> IS Library Team - Book Rental </p>
            </div>
        </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden"> Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden"> Next</span>
    </button>
</div>

<!--------------------------------------------NEW ARRIVALS CONTAINER------------------------------------------------------->
<div class="container mb-5 mt-5">
    <h2 class="fs-2 fw-bold text-center"> New Arrivals</h2>
    <hr />
    <div class="row gy-3 text-center ">
        <?php
        $orderBy = 'id desc';
        $getProduct = getProduct($con, 4, '', '', $orderBy);
        foreach ($getProduct as $list) {
            $img = BOOK_IMAGE_SITE_PATH . $list['img'];
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class=" card border-dark mt-3 shadow-sm product">
                <img id="card-img" alt="Book Image" src="<?php echo $img ?>" class="card-img-top rounded"
                    height="396rem" width="260rem" />
                <div class="overlay">
                    <a href="book.php?id=<?php echo $list['id'] ?>" class="btn-lg text-decoration-none rent-btn">
                        Info</a>
                </div>
            </div>
            <div id="bookCardName">
                <a href="book.php?id=<?php echo $list['id'] ?>"
                    class="card-text text-uppercase text-break fw-bold text-decoration-none">
                    <?php echo $list['name'] ?>
                </a>
                <p class="card-text">Price- ₫<?php echo $list['rent'] ?> Per day</p>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!--------------------------------------------MOST VIEWED CONTAINER-------------------------------------------------------->
<div class="container mb-5 mt-5">
    <h2 class="fs-2 fw-bold text-center">Most Viewed</h2>
    <hr />
    <div class="row gy-3 text-center ">
        <?php
        $getBook = getBook($con);
        foreach ($getBook as $list) {
            $img = BOOK_IMAGE_SITE_PATH . $list['img'];
        ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-dark mt-3 shadow-sm product">
                <img id="card-img" alt="Book Image" src="<?php echo $img ?>" class="card-img-top rounded"
                    height="396rem" width="260rem" />
                <div class="overlay">
                    <a href="book.php?id=<?php echo $list['id'] ?>" class="btn-lg text-decoration-none rent-btn">
                        Info</a>
                </div>
            </div>
            <div id="bookCardName">
                <a href="book.php?id=<?php echo $list['id'] ?>"
                    class="card-text text-uppercase text-break fw-bold text-decoration-none">
                    <?php echo $list['name'] ?>
                </a>
                <p class="card-text">Price- ₫<?php echo $list['rent'] ?> Per day</p>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!--------------------------------------------CUSTOMER FEEDBACKS CONTAINER-------------------------------------------------->
<div class="container mb-5 mt-5">
    <h2 class="fs-2 fw-bold text-center">Customer Feedbacks</h2>
    <hr />
    <div class="row">
        <?php
        $feedbacks = getAllFeedbacks($con);
        if (!empty($feedbacks)):
            // Hiển thị tối đa 6 feedback mới nhất
            $displayFeedbacks = array_slice($feedbacks, 0, 6);
            foreach ($displayFeedbacks as $feedback):
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0"><?php echo htmlspecialchars($feedback['book_name']); ?></h6>
                        <small class="text-muted"><?php echo date('M j, Y', strtotime($feedback['created_at'])); ?></small>
                    </div>
                    
                    <!-- Rating Stars -->
                    <div class="mb-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                        <small class="text-muted">(<?php echo $feedback['rating']; ?>/5)</small>
                    </div>
                    
                    <!-- Comment -->
                    <p class="card-text">
                        <?php echo !empty($feedback['comment']) ? htmlspecialchars(substr($feedback['comment'], 0, 150)) . (strlen($feedback['comment']) > 150 ? '...' : '') : 'No comment provided'; ?>
                    </p>
                    
                    <!-- Customer Info -->
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($feedback['user_name']); ?>
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-book"></i> <?php echo htmlspecialchars($feedback['author']); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            endforeach;
        else:
        ?>
        <div class="col-12">
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i>
                No customer feedbacks available yet. Be the first to share your experience!
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (!empty($feedbacks) && count($feedbacks) > 6): ?>
    <div class="text-center mt-3">
        <a href="feedbacks.php" class="btn btn-outline-primary">
            View All Feedbacks <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require(__DIR__ . '/../includes/footer.php') ?>