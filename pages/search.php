<?php require(__DIR__ . '/../includes/header.php') ?>
<?php
$search = isset($_GET['search']) ? $_GET['search'] : '';
$getBook = searchBooks($con, $search);
?>
<script>
document.title = "Search Results for '<?php echo htmlspecialchars($search); ?>' | Book Rental";
</script>
<main class="px-4 row py-3 container-fluid">
    <div class="d-flex justify-content-center flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 border-bottom">
        <h1 class="h2">Search Results for "<?php echo htmlspecialchars($search); ?>"</h1>
    </div>
    <?php
  if (count($getBook) > 0) {
  ?>
    <div class="row gy-3 text-center ">
        <?php
      foreach ($getBook as $list) {
      ?>
        <div class="col-6 col-sm-4 col-md-3 col-lg-2">
            <div class="card border-dark  shadow-sm product">
                <img id="card-img" alt="Book Image" src="<?php echo BOOK_IMAGE_SITE_PATH . $list['img'] ?>"
                    class="card-img-top rounded" height="250rem" />
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
                <p class="card-text text-break"><strong>Author</strong> - <?php echo $list['author'] ?></p>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } else {
    if (!empty($search)) {
        echo '<div class="alert alert-info text-center">No books found for: "' . htmlspecialchars($search) . '"</div>';
    } else {
        echo '<div class="alert alert-warning text-center">Please enter a search term</div>';
    }
  } ?>
</main>
<?php require(__DIR__ . '/../includes/footer.php') ?>