<?php
/**
 * In mảng để debug (không dừng script)
 */
function pr($arr)
{
  echo '<pre>';
  print_r($arr);
  echo '</pre>';
}

/**
 * In mảng và dừng script (để debug)
 */
function prx($arr)
{
  echo '<pre>';
  print_r($arr);
  echo '</pre>';
  die();
}


/**
 * Lấy danh sách sách từ database
 * @param mysqli $databaseConnection - Kết nối database
 * @param int|string $limitCount - Số lượng sách (mặc định: không giới hạn)
 * @param int|string $categoryId - ID danh mục (mặc định: tất cả)
 * @param int|string $bookId - ID sách cụ thể (mặc định: tất cả)
 * @param string $orderByClause - Sắp xếp (mặc định: không sắp xếp)
 * @return array - Mảng chứa thông tin sách
 */
function getProduct($con, $limitCount = '', $categoryId = '', $bookId = '', $orderByClause = '')
{
  $query = "SELECT * FROM books WHERE status = 1";
  
  // Nếu có bookId cụ thể, chỉ lấy sách đó (bỏ qua categoryId)
  if (!empty($bookId)) {
    $bookId = (int)$bookId;
    $query .= " AND id = $bookId";
  } elseif ($categoryId !== '' && $categoryId !== null && $categoryId !== 0) {
    $categoryId = (int)$categoryId;
    $query .= " AND category_id = $categoryId";
  }
  
  if (!empty($orderByClause)) {
    $query .= " ORDER BY $orderByClause";
  }
  
  if (!empty($limitCount)) {
    $limitCount = (int)$limitCount;
    $query .= " LIMIT $limitCount";
  }
  
  $queryResult = mysqli_query($con, $query);
  if (!$queryResult) {
    return [];
  }
  
  $booksList = [];
  while ($bookRecord = mysqli_fetch_assoc($queryResult)) {
    $booksList[] = $bookRecord;
  }
  return $booksList;
}

/**
 * Lấy sách bán chạy (best seller)
 * @param mysqli $databaseConnection - Kết nối database
 * @param int $limitCount - Số lượng sách cần lấy (mặc định: 8)
 * @return array - Mảng chứa thông tin sách bán chạy
 */
function getBook($con, $limitCount = 8)
{
  $limitCount = (int)$limitCount;
  $query = "SELECT * FROM books WHERE best_seller = 1 AND status = 1 LIMIT $limitCount";
  $queryResult = mysqli_query($con, $query);
  
  if (!$queryResult) {
    return [];
  }
  
  $bestSellerBooks = [];
  while ($bookRecord = mysqli_fetch_assoc($queryResult)) {
    $bestSellerBooks[] = $bookRecord;
  }
  return $bestSellerBooks;
}

/**
 * Tìm kiếm sách theo tên hoặc tác giả
 * @param mysqli $databaseConnection - Kết nối database
 * @param string $searchKeyword - Từ khóa tìm kiếm
 * @return array - Mảng chứa thông tin sách tìm được
 */
function searchBooks($con, $searchKeyword)
{
  // Xóa khoảng trắng và làm sạch từ khóa tìm kiếm
  $searchKeyword = trim($searchKeyword);
  $searchKeyword = mysqli_real_escape_string($con, $searchKeyword);
  
  // Nếu từ khóa rỗng, trả về mảng rỗng
  if (empty($searchKeyword)) {
    return [];
  }
  
  // Tìm kiếm theo ký tự trong tên sách hoặc tác giả
  $query = "SELECT * FROM books WHERE status = 1 
          AND (name LIKE '%$searchKeyword%' OR author LIKE '%$searchKeyword%')
          ORDER BY name ASC";
  
  $queryResult = mysqli_query($con, $query);
  
  if (!$queryResult) {
    return [];
  }
  
  $searchResults = [];
  while ($bookRecord = mysqli_fetch_assoc($queryResult)) {
    $searchResults[] = $bookRecord;
  }
  return $searchResults;
}

/**
 * Kiểm tra user đã feedback cho cuốn sách trong đơn hàng chưa
 * @param mysqli $con - Kết nối database
 * @param int $orderId - ID đơn hàng
 * @param int $bookId - ID sách
 * @param int $userId - ID user
 * @return bool - True nếu đã feedback, False nếu chưa
 */
function hasUserFeedback($con, $orderId, $bookId, $userId) {
  // Check if feedback table exists
  $tableCheck = mysqli_query($con, "SHOW TABLES LIKE 'feedback'");
  if (mysqli_num_rows($tableCheck) == 0) {
    return false; // Table doesn't exist, no feedback possible
  }
  
  $orderId = (int)$orderId;
  $bookId = (int)$bookId;
  $userId = (int)$userId;
  
  $query = "SELECT id FROM feedback WHERE order_id = $orderId AND book_id = $bookId AND user_id = $userId LIMIT 1";
  $result = mysqli_query($con, $query);
  
  return (mysqli_num_rows($result) > 0);
}

/**
 * Lưu feedback của user
 * @param mysqli $con - Kết nối database
 * @param int $orderId - ID đơn hàng
 * @param int $bookId - ID sách
 * @param int $userId - ID user
 * @param int $rating - Đánh giá (1-5)
 * @param string $comment - Bình luận
 * @return bool - True nếu thành công, False nếu thất bại
 */
function saveFeedback($con, $orderId, $bookId, $userId, $rating, $comment) {
  // Auto-create feedback table if it doesn't exist
  $createTableSQL = "CREATE TABLE IF NOT EXISTS `feedback` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `book_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `rating` tinyint(1) NOT NULL COMMENT '1-5 stars',
    `comment` text DEFAULT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_feedback` (`order_id`, `book_id`, `user_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
  mysqli_query($con, $createTableSQL);
  
  $orderId = (int)$orderId;
  $bookId = (int)$bookId;
  $userId = (int)$userId;
  $rating = (int)$rating;
  $comment = mysqli_real_escape_string($con, trim($comment));
  
  // Validate rating
  if ($rating < 1 || $rating > 5) {
    return false;
  }
  
  $query = "INSERT INTO feedback (order_id, book_id, user_id, rating, comment) 
            VALUES ($orderId, $bookId, $userId, $rating, '$comment')";
  
  return mysqli_query($con, $query);
}

/**
 * Lấy tất cả feedback cho admin
 * @param mysqli $con - Kết nối database
 * @return array - Mảng chứa tất cả feedback
 */
function getAllFeedbacks($con) {
  // Check if feedback table exists
  $tableCheck = mysqli_query($con, "SHOW TABLES LIKE 'feedback'");
  if (mysqli_num_rows($tableCheck) == 0) {
    return []; // Table doesn't exist, return empty array
  }
  
  $query = "SELECT f.*, b.name as book_name, b.author, u.name as user_name, u.email, o.id as order_id
            FROM feedback f
            JOIN books b ON f.book_id = b.id
            JOIN users u ON f.user_id = u.id
            JOIN orders o ON f.order_id = o.id
            ORDER BY f.created_at DESC";
  
  $result = mysqli_query($con, $query);
  $feedbacks = [];
  
  while ($row = mysqli_fetch_assoc($result)) {
    $feedbacks[] = $row;
  }
  
  return $feedbacks;
}

/**
 * Lấy feedback của một cuốn sách
 * @param mysqli $con - Kết nối database
 * @param int $bookId - ID sách
 * @return array - Mảng chứa feedback và thông tin thống kê
 */
function getBookFeedbacks($con, $bookId) {
  // Check if feedback table exists
  $tableCheck = mysqli_query($con, "SHOW TABLES LIKE 'feedback'");
  if (mysqli_num_rows($tableCheck) == 0) {
    return [
      'feedbacks' => [],
      'avg_rating' => 0,
      'total_feedbacks' => 0
    ]; // Table doesn't exist, return empty data
  }
  
  $bookId = (int)$bookId;
  
  // Lấy tất cả feedback của sách
  $query = "SELECT f.*, u.name as user_name
            FROM feedback f
            JOIN users u ON f.user_id = u.id
            WHERE f.book_id = $bookId
            ORDER BY f.created_at DESC";
  
  $result = mysqli_query($con, $query);
  $feedbacks = [];
  
  while ($row = mysqli_fetch_assoc($result)) {
    $feedbacks[] = $row;
  }
  
  // Tính trung bình rating
  $avgQuery = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_feedbacks
               FROM feedback WHERE book_id = $bookId";
  $avgResult = mysqli_query($con, $avgQuery);
  $stats = mysqli_fetch_assoc($avgResult);
  
  return [
    'feedbacks' => $feedbacks,
    'avg_rating' => round($stats['avg_rating'], 1),
    'total_feedbacks' => $stats['total_feedbacks']
  ];
}

/**
 * Tạo token ngẫu nhiên cho Remember Me
 * @return string - Token 32 ký tự
 */
function generateToken()
{
  return bin2hex(random_bytes(32)); // 64 ký tự hex
}

/**
 * Lưu token vào cookie và database khi user chọn Remember Me
 * @param mysqli $con - Kết nối database
 * @param int $userId - ID của user
 * @return string|false - Token nếu thành công, false nếu thất bại
 */
function saveRememberToken($con, $userId)
{
  // Tạo token mới
  $token = generateToken();
  
  // Token hết hạn sau 30 ngày
  $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
  $createdAt = date('Y-m-d H:i:s');
  
  // Lưu vào database
  $userId = (int)$userId;
  $sql = "INSERT INTO user_tokens (user_id, token, expires_at, created_at) 
          VALUES ($userId, '$token', '$expiresAt', '$createdAt')";
  
  if (mysqli_query($con, $sql)) {
    // Lưu vào cookie (30 ngày)
    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    return $token;
  }
  
  return false;
}

/**
 * Xóa token khỏi cookie và database
 * @param mysqli $con - Kết nối database
 * @param string $token - Token cần xóa
 */
function deleteRememberToken($con, $token)
{
  // Xóa khỏi database
  mysqli_query($con, "DELETE FROM user_tokens WHERE token='$token'");
  
  // Xóa cookie
  setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

/**
 * Kiểm tra token từ cookie và tự động đăng nhập
 * @param mysqli $con - Kết nối database
 * @return bool - true nếu đăng nhập thành công, false nếu không
 */
function checkRememberToken($con)
{
  // Chỉ check nếu chưa có session
  if (isset($_SESSION['USER_LOGIN'])) {
    return false;
  }
  
  // Kiểm tra cookie
  if (!isset($_COOKIE['remember_token']) || empty($_COOKIE['remember_token'])) {
    return false;
  }
  
  $token = $_COOKIE['remember_token'];
  
  // Tìm token trong database
  $sql = "SELECT ut.user_id, u.name, u.email 
          FROM user_tokens ut
          JOIN users u ON ut.user_id = u.id
          WHERE ut.token='$token' AND ut.expires_at > NOW()";
  $res = mysqli_query($con, $sql);
  
  if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    
    // Set session
    $_SESSION['USER_LOGIN'] = 'yes';
    $_SESSION['USER_ID'] = $row['user_id'];
    $_SESSION['USER_NAME'] = $row['name'];
    
    return true;
  } else {
    // Token không hợp lệ hoặc hết hạn, xóa cookie
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    return false;
  }
}

/**
 * Xóa tất cả token của user (khi đổi password hoặc logout tất cả thiết bị)
 * @param mysqli $con - Kết nối database
 * @param int $userId - ID của user
 */
function deleteAllUserTokens($con, $userId)
{
  $userId = (int)$userId;
  mysqli_query($con, "DELETE FROM user_tokens WHERE user_id=$userId");
}

/**
 * Lưu token vào cookie và database khi admin chọn Remember Me
 * @param mysqli $con - Kết nối database
 * @param int $adminId - ID của admin
 * @return string|false - Token nếu thành công, false nếu thất bại
 */
function saveAdminRememberToken($con, $adminId)
{
  // Tạo token mới
  $token = generateToken();
  
  // Token hết hạn sau 30 ngày
  $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
  $createdAt = date('Y-m-d H:i:s');
  
  // Lưu vào database
  $adminId = (int)$adminId;
  $sql = "INSERT INTO admin_tokens (admin_id, token, expires_at, created_at) 
          VALUES ($adminId, '$token', '$expiresAt', '$createdAt')";
  
  if (mysqli_query($con, $sql)) {
    // Lưu vào cookie (30 ngày) - dùng tên cookie khác với user
    setcookie('admin_remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    return $token;
  }
  
  return false;
}

/**
 * Xóa token admin khỏi cookie và database
 * @param mysqli $con - Kết nối database
 * @param string $token - Token cần xóa
 */
function deleteAdminRememberToken($con, $token)
{
  // Xóa khỏi database
  mysqli_query($con, "DELETE FROM admin_tokens WHERE token='$token'");
  
  // Xóa cookie
  setcookie('admin_remember_token', '', time() - 3600, '/', '', false, true);
}

/**
 * Kiểm tra token admin từ cookie và tự động đăng nhập
 * @param mysqli $con - Kết nối database
 * @return bool - true nếu đăng nhập thành công, false nếu không
 */
function checkAdminRememberToken($con)
{
  // Chỉ check nếu chưa có session
  if (isset($_SESSION['ADMIN_LOGIN'])) {
    return false;
  }
  
  // Kiểm tra cookie
  if (!isset($_COOKIE['admin_remember_token']) || empty($_COOKIE['admin_remember_token'])) {
    return false;
  }
  
  $token = $_COOKIE['admin_remember_token'];
  
  // Tìm token trong database
  $sql = "SELECT at.admin_id, a.email 
          FROM admin_tokens at
          JOIN admin a ON at.admin_id = a.id
          WHERE at.token='$token' AND at.expires_at > NOW()";
  $res = mysqli_query($con, $sql);
  
  if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    
    // Set session
    $_SESSION['ADMIN_LOGIN'] = 'yes';
    $_SESSION['ADMIN_email'] = $row['email'];
    
    return true;
  } else {
    // Token không hợp lệ hoặc hết hạn, xóa cookie
    setcookie('admin_remember_token', '', time() - 3600, '/', '', false, true);
    return false;
  }
}

/**
 * Xóa tất cả token của admin (khi đổi password hoặc logout tất cả thiết bị)
 * @param mysqli $con - Kết nối database
 * @param int $adminId - ID của admin
 */
function deleteAllAdminTokens($con, $adminId)
{
  $adminId = (int)$adminId;
  mysqli_query($con, "DELETE FROM admin_tokens WHERE admin_id=$adminId");
}

/**
 * Kiểm tra xem đơn hàng có thể được duyệt không (dựa trên số lượng sách còn lại)
 * @param mysqli $con - Kết nối database
 * @param int $orderId - ID của đơn hàng
 * @return array - Mảng chứa thông tin kết quả: ['can_approve' => bool, 'message' => string, 'qty' => int]
 */
function canApproveOrder($con, $orderId)
{
    $orderId = (int)$orderId;
    
    // Lấy thông tin sách và số lượng trong đơn hàng
    $query = "SELECT b.id, b.name, b.qty, od.quantity 
              FROM orders o
              JOIN order_detail od ON o.id = od.order_id
              JOIN books b ON od.book_id = b.id
              WHERE o.id = $orderId";
    
    $result = mysqli_query($con, $query);
    
    if (!$result || mysqli_num_rows($result) == 0) {
        return [
            'can_approve' => false,
            'message' => 'Đơn hàng không tồn tại',
            'qty' => 0
        ];
    }
    
    $book = mysqli_fetch_assoc($result);
    $availableQty = $book['qty'];
    
    if ($availableQty <= 0) {
        return [
            'can_approve' => false,
            'message' => 'Hết sách, không thể xác nhận đơn',
            'qty' => $availableQty
        ];
    }
    
    return [
        'can_approve' => true,
        'message' => 'Sách còn sẵn có, có thể xác nhận đơn',
        'qty' => $availableQty
    ];
}