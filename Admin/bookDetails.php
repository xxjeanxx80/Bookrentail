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

// Lấy book ID từ URL
$bookId = (int)$_GET['id'];
if ($bookId == 0) {
    header('Location: books.php');
    exit;
}

// Xử lý các action
if (isset($_GET['type']) && $_GET['type'] != ' ') {
    $type = trim($_GET['type']);
    
    if ($type == 'status') {
        $status = ($_GET['operation'] == 'active') ? 1 : 0;
        mysqli_query($con, "UPDATE books SET status=$status WHERE id=$bookId");
    } elseif ($type == 'best_seller') {
        $bestSeller = ($_GET['operation'] == 'active') ? 1 : 0;
        mysqli_query($con, "UPDATE books SET best_seller=$bestSeller WHERE id=$bookId");
    } elseif ($type == 'delete') {
        mysqli_query($con, "DELETE FROM books WHERE id=$bookId");
        header('Location: books.php');
        exit;
    }
    
    header("Location: bookDetails.php?id=$bookId");
    exit;
}

// Lấy thông tin sách
$sql = "SELECT books.*, categories.category 
        FROM books 
        LEFT JOIN categories ON books.category_id=categories.id 
        WHERE books.id=$bookId";
$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: books.php');
    exit;
}

$book = mysqli_fetch_assoc($result);

require('topNav.php');
?>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex justify-content-center flex-grow-1">
            <h1>Book Details - <?php echo htmlspecialchars($book['name']) ?>
                <hr>
            </h1>
        </div>
        <a href="books.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Books
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Book Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <img src="<?php echo BOOK_IMAGE_SITE_PATH . $book['img'] ?>" 
                                 class="book-detail-image mb-3" alt="Book cover">
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['ISBN']) ?></p>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category'] ?? 'N/A') ?></p>
                                    <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']) ?></p>
                                    <p><strong>Price:</strong> ₫<?php echo number_format($book['price']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Security Deposit:</strong> ₫<?php echo number_format($book['security']) ?></p>
                                    <p><strong>Rent Price:</strong> ₫<?php echo number_format($book['rent']) ?>/day</p>
                                    <p><strong>Quantity:</strong> <?php echo $book['qty'] ?></p>
                                    <p><strong>Status:</strong> 
                                        <?php if ($book['status'] == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($book['best_seller'] == 1): ?>
                                <div class="mt-2">
                                    <span class="badge bg-warning">
                                        <i class="fas fa-star"></i> Best Seller
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary" onclick="window.location.href='manageBooks.php?id=<?php echo $book['id'] ?>'">
                            <i class="fas fa-edit"></i> Edit Book
                        </button>
                        
                        <?php if ($book['best_seller'] == 1): ?>
                            <button class="btn btn-secondary" onclick="window.location.href='?type=best_seller&operation=deactive&id=<?php echo $book['id'] ?>'">
                                <i class="fas fa-star"></i> Remove Best Seller
                            </button>
                        <?php else: ?>
                            <button class="btn btn-warning" onclick="window.location.href='?type=best_seller&operation=active&id=<?php echo $book['id'] ?>'">
                                <i class="far fa-star"></i> Set as Best Seller
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($book['status'] == 1): ?>
                            <button class="btn btn-secondary" onclick="window.location.href='?type=status&operation=deactive&id=<?php echo $book['id'] ?>'">
                                <i class="fas fa-eye-slash"></i> Deactivate
                            </button>
                        <?php else: ?>
                            <button class="btn btn-success" onclick="window.location.href='?type=status&operation=active&id=<?php echo $book['id'] ?>'">
                                <i class="fas fa-eye"></i> Activate
                            </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-danger" onclick="if(confirm('Are you sure you want to delete this book? This action cannot be undone.')) window.location.href='?type=delete&id=<?php echo $book['id'] ?>'">
                            <i class="fas fa-trash"></i> Delete Book
                        </button>
                    </div>
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
