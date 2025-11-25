<?php
session_start();

// Kết nối cơ sở dữ liệu PostgreSQL
$con = pg_connect("host=localhost dbname=mini_project user=postgres password=postgres");
if (!$con) {
    die("Connection failed: " . pg_last_error());
}

// ============================================================================
// CUSTOMER AUTHENTICATION AUTO-CHECK - CHẠY TỰ ĐỘNG TRÊN MỖI PAGE
// ============================================================================

// Giải thích: Mỗi khi customer page được load, connection.php sẽ tự động kiểm tra:
// 1. Nếu session đã tồn tại -> OK (đã đăng nhập)
// 2. Nếu session chưa tồn tại -> check cookie "Remember Me"
// 3. Nếu cookie hợp lệ -> restore session từ database token
// 4. Nếu cookie không hợp lệ -> xóa cookie

// Check cookie authentication for customer using database tokens
if (!isset($_SESSION['USER_LOGIN']) && isset($_COOKIE['user_auth'])) {
    $cookie_data = base64_decode($_COOKIE['user_auth']);
    $cookie_parts = explode('|', $cookie_data);
    if (count($cookie_parts) == 2) {
        $user_id = $cookie_parts[0];
        $token = $cookie_parts[1];
        
        // Verify token in database and check expiration
        $sql = "SELECT ut.*, u.name, u.email FROM user_tokens ut 
                JOIN users u ON ut.user_id = u.id 
                WHERE ut.user_id = '$user_id' AND ut.token = '$token' AND ut.expires_at > NOW()";
        $res = pg_query($con, $sql);
        if ($res && pg_num_rows($res) > 0) {
            $row = pg_fetch_assoc($res);
            // Restore session with valid token
            $_SESSION['USER_LOGIN'] = 'yes';
            $_SESSION['USER_ID'] = $row['user_id'];
            $_SESSION['USER_NAME'] = $row['name'];
        } else {
            // Invalid or expired token - delete cookie
            setcookie('user_auth', '', time() - 3600, "/");
        }
    }
}

// Đường dẫn thực tế trên máy (server path)
define('SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/Bookrentail/');

// Đường dẫn truy cập website (site path)
const SITE_PATH = 'http://localhost/Bookrentail/';

// Đường dẫn thư mục chứa ảnh sách
const BOOK_IMAGE_SERVER_PATH = SERVER_PATH . 'Img/books/'; 
const BOOK_IMAGE_SITE_PATH   = SITE_PATH   . 'Img/books/';
?>
