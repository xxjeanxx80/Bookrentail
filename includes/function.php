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

if (!function_exists('str_starts_with')) {
  function str_starts_with($haystack, $needle)
  {
    $needle = (string)$needle;
    if ($needle === '') {
      return true;
    }
    return strncmp($haystack, $needle, strlen($needle)) === 0;
  }
}

if (!function_exists('str_contains')) {
  function str_contains($haystack, $needle)
  {
    return $needle === '' || strpos($haystack, $needle) !== false;
  }
}

/**
 * Kiểm tra thư mục assets/img/books có ghi được không
 */
function bookrentail_can_write_local_images(): bool
{
  static $isWritable = null;

  if ($isWritable === null) {
    $targetDir = BOOK_IMAGE_SERVER_PATH;
    if (!is_dir($targetDir)) {
      @mkdir($targetDir, 0775, true);
    }
    $isWritable = is_dir($targetDir) && is_writable($targetDir);
  }

  return $isWritable;
}

/**
 * Xác định chuỗi img có phải reference tới bảng book_media hay không (media:{id})
 */
function bookrentail_is_media_reference(?string $value): bool
{
  if (empty($value)) {
    return false;
  }

  return str_starts_with($value, 'media:');
}

/**
 * Lấy URL hiển thị ảnh sách (hỗ trợ local file, URL tuyệt đối hoặc media:id)
 */
function bookrentail_get_book_image_url(?string $value): string
{
  $fallback = SITE_PATH . 'assets/img/icon.png';

  if (empty($value)) {
    return $fallback;
  }

  if (filter_var($value, FILTER_VALIDATE_URL)) {
    return $value;
  }

  if (bookrentail_is_media_reference($value)) {
    $id = (int)substr($value, 6);
    if ($id > 0) {
      return SITE_PATH . 'media.php?id=' . $id;
    }
    return $fallback;
  }

  return BOOK_IMAGE_SITE_PATH . ltrim($value, '/');
}

/**
 * Xóa ảnh sách (cả file hệ thống lẫn record media)
 */
function bookrentail_delete_book_image($con, ?string $value): void
{
  if (empty($value)) {
    return;
  }

  if (bookrentail_is_media_reference($value)) {
    $id = (int)substr($value, 6);
    if ($id > 0) {
      bookrentail_ensure_book_media_table($con);
      pg_query_params($con, 'DELETE FROM book_media WHERE id = $1', [$id]);
    }
    return;
  }

  if (bookrentail_can_write_local_images()) {
    $path = BOOK_IMAGE_SERVER_PATH . ltrim($value, '/');
    if (is_file($path)) {
      @unlink($path);
    }
  }
}

function bookrentail_ensure_book_media_table($con): void
{
  static $initialized = false;

  if ($initialized) {
    return;
  }

  $ddl = <<<SQL
CREATE TABLE IF NOT EXISTS book_media (
    id SERIAL PRIMARY KEY,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    data BYTEA NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
)
SQL;

  @pg_query($con, $ddl);
  @pg_query($con, 'CREATE INDEX IF NOT EXISTS idx_book_media_created_at ON book_media (created_at)');

  $initialized = true;
}

/**
 * Lưu ảnh upload. Ưu tiên lưu file local nếu ghi được, ngược lại lưu vào bảng book_media
 *
 * @return array{0:?string,1:?string} [đường dẫn/ID ảnh, thông báo lỗi]
 */
function bookrentail_store_book_image($con, array $file, ?string $existingValue = null): array
{
  if (empty($file['name'])) {
    return [$existingValue, null];
  }

  $errorCode = $file['error'] ?? UPLOAD_ERR_OK;
  if ($errorCode !== UPLOAD_ERR_OK) {
    return [null, 'Upload failed. Please try again.'];
  }

  $originalName = basename($file['name']);
  $safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
  $generatedName = time() . '_' . $safeName;

  if (bookrentail_can_write_local_images()) {
    $targetPath = BOOK_IMAGE_SERVER_PATH . $generatedName;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
      if ($existingValue && $existingValue !== $generatedName) {
        bookrentail_delete_book_image($con, $existingValue);
      }
      return [$generatedName, null];
    }
  }

  $content = @file_get_contents($file['tmp_name']);
  if ($content === false) {
    return [null, 'Could not read uploaded file.'];
  }

  bookrentail_ensure_book_media_table($con);

  $mimeType = $file['type'] ?? (function_exists('mime_content_type') ? mime_content_type($file['tmp_name']) : 'application/octet-stream');
  $insert = pg_query_params(
    $con,
    'INSERT INTO book_media (file_name, mime_type, data) VALUES ($1, $2, $3) RETURNING id',
    [$safeName, $mimeType, $content]
  );

  if ($insert && ($row = pg_fetch_assoc($insert))) {
    $newValue = 'media:' . $row['id'];
    if ($existingValue && $existingValue !== $newValue) {
      bookrentail_delete_book_image($con, $existingValue);
    }
    return [$newValue, null];
  }

  return [null, 'Failed to store uploaded image.'];
}


/**
 * Lấy danh sách sách từ database
 * @param resource $databaseConnection - Kết nối database
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
  
  $queryResult = pg_query($con, $query);
  if (!$queryResult) {
    return [];
  }

  $booksList = [];
  while ($bookRecord = pg_fetch_assoc($queryResult)) {
    $booksList[] = $bookRecord;
  }
  return $booksList;
}

/**
 * Lấy sách bán chạy (best seller)
 * @param resource $databaseConnection - Kết nối database
 * @param int $limitCount - Số lượng sách cần lấy (mặc định: 8)
 * @return array - Mảng chứa thông tin sách bán chạy
 */
function getBook($con, $limitCount = 8)
{
  $limitCount = (int)$limitCount;
  $query = "SELECT * FROM books WHERE best_seller = 1 AND status = 1 LIMIT $limitCount";
  $queryResult = pg_query($con, $query);

  if (!$queryResult) {
    return [];
  }

  $bestSellerBooks = [];
  while ($bookRecord = pg_fetch_assoc($queryResult)) {
    $bestSellerBooks[] = $bookRecord;
  }
  return $bestSellerBooks;
}

/**
 * Tìm kiếm sách theo tên hoặc tác giả
 * @param resource $databaseConnection - Kết nối database
 * @param string $searchKeyword - Từ khóa tìm kiếm
 * @return array - Mảng chứa thông tin sách tìm được
 */
function searchBooks($con, $searchKeyword)
{
  $searchKeyword = trim($searchKeyword);
  $query = "SELECT * FROM books WHERE status = 1
          AND (name LIKE '%$searchKeyword%' OR author LIKE '%$searchKeyword%')";
  $queryResult = pg_query($con, $query);

  if (!$queryResult) {
    return [];
  }

  $searchResults = [];
  while ($bookRecord = pg_fetch_assoc($queryResult)) {
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
 * @param resource $con - Kết nối database
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

  if (pg_query($con, $sql)) {
    // Lưu vào cookie (30 ngày)
    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    return $token;
  }

  return false;
}

/**
 * Xóa token khỏi cookie và database
 * @param resource $con - Kết nối database
 * @param string $token - Token cần xóa
 */
function deleteRememberToken($con, $token)
{
  // Xóa khỏi database
  pg_query($con, "DELETE FROM user_tokens WHERE token='$token'");

  // Xóa cookie
  setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

/**
 * Kiểm tra token từ cookie và tự động đăng nhập
 * @param resource $con - Kết nối database
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
  $res = pg_query($con, $sql);

  if ($res && pg_num_rows($res) > 0) {
    $row = pg_fetch_assoc($res);

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
 * @param resource $con - Kết nối database
 * @param int $userId - ID của user
 */
function deleteAllUserTokens($con, $userId)
{
  $userId = (int)$userId;
  pg_query($con, "DELETE FROM user_tokens WHERE user_id=$userId");
}

/**
 * Lưu token vào cookie và database khi admin chọn Remember Me
 * @param resource $con - Kết nối database
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

  if (pg_query($con, $sql)) {
    // Lưu vào cookie (30 ngày) - dùng tên cookie khác với user
    setcookie('admin_remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
    return $token;
  }

  return false;
}

/**
 * Xóa token admin khỏi cookie và database
 * @param resource $con - Kết nối database
 * @param string $token - Token cần xóa
 */
function deleteAdminRememberToken($con, $token)
{
  // Xóa khỏi database
  pg_query($con, "DELETE FROM admin_tokens WHERE token='$token'");

  // Xóa cookie
  setcookie('admin_remember_token', '', time() - 3600, '/', '', false, true);
}

/**
 * Kiểm tra token admin từ cookie và tự động đăng nhập
 * @param resource $con - Kết nối database
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
  $res = pg_query($con, $sql);

  if ($res && pg_num_rows($res) > 0) {
    $row = pg_fetch_assoc($res);

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
 * @param resource $con - Kết nối database
 * @param int $adminId - ID của admin
 */
function deleteAllAdminTokens($con, $adminId)
{
  $adminId = (int)$adminId;
  pg_query($con, "DELETE FROM admin_tokens WHERE admin_id=$adminId");
}