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
  $searchKeyword = trim($searchKeyword);
  $query = "SELECT * FROM books WHERE status = 1 
          AND (name LIKE '%$searchKeyword%' OR author LIKE '%$searchKeyword%')";
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