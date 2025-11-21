<?php
/**
 * File cấu hình kết nối database và đường dẫn
 */

// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Đọc thông số DB từ biến môi trường (phù hợp khi deploy)
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_NAME') ?: 'mini_project';
$dbPort = getenv('DB_PORT') ?: 3306;

// Kết nối cơ sở dữ liệu
$databaseConnection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
if (!$databaseConnection || $databaseConnection->connect_error) {
    die('Connection failed: ' . ($databaseConnection->connect_error ?? mysqli_connect_error()));
}

// Tạo alias $con để tương thích với code cũ (sẽ refactor dần)
$con = $databaseConnection;

// Xác định đường dẫn vật lý gốc của project
$projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/..')) . '/';
if (!defined('SERVER_PATH')) {
    // Có thể override bằng biến môi trường nếu deploy trong thư mục khác
    $serverPath = getenv('SERVER_PATH') ?: $projectRoot;
    define('SERVER_PATH', rtrim($serverPath, '/') . '/');
}

// Đường dẫn truy cập website (site path)
if (!defined('SITE_PATH')) {
    $appUrlFromEnv = getenv('SITE_PATH') ?: getenv('APP_URL');
    $inferredUrl = '';

    // Tự suy ra subfolder nếu đang chạy dưới docroot/ins3064/Book-rental
    $docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : '';
    $normalizedDocRoot = $docRoot ? rtrim(str_replace('\\', '/', $docRoot), '/') : '';
    $projectNormalized = rtrim($projectRoot, '/');
    $relativeToDocRoot = $normalizedDocRoot ?
        trim(str_replace($normalizedDocRoot, '', $projectNormalized), '/')
        : '';

    if (!empty($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $subPath = $relativeToDocRoot ? ('/' . $relativeToDocRoot) : '';
        $inferredUrl = $scheme . $_SERVER['HTTP_HOST'] . $subPath . '/';
    }

    $sitePath = $appUrlFromEnv ?: $inferredUrl ?: 'http://localhost/';
    define('SITE_PATH', rtrim($sitePath, '/') . '/');
}

// Đường dẫn thư mục chứa ảnh sách
if (!defined('BOOK_IMAGE_SERVER_PATH')) {
    define('BOOK_IMAGE_SERVER_PATH', SERVER_PATH . 'assets/img/books/');
}
if (!defined('BOOK_IMAGE_SITE_PATH')) {
    define('BOOK_IMAGE_SITE_PATH', SITE_PATH . 'assets/img/books/');
}
?>
