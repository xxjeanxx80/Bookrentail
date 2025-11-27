<?php
/**
 * File cấu hình kết nối database và đường dẫn
 */

// Kiểm tra session đã start chưa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kết nối cơ sở dữ liệu
$databaseConnection = mysqli_connect("localhost", "root", "", "mini_project");
if ($databaseConnection->connect_error) {
    die("Connection failed: " . $databaseConnection->connect_error);
}

// Tạo alias $con để tương thích với code cũ (sẽ refactor dần)
$con = $databaseConnection;

// Đường dẫn thực tế trên máy (server path)
if (!defined('SERVER_PATH')) {
    define('SERVER_PATH', $_SERVER['DOCUMENT_ROOT'] . '/ins3064/Book-rental/');
}

// Đường dẫn truy cập website (site path)
if (!defined('SITE_PATH')) {
    define('SITE_PATH', 'http://localhost/ins3064/Book-rental/');
}

// Đường dẫn thư mục chứa ảnh sách
if (!defined('BOOK_IMAGE_SERVER_PATH')) {
    define('BOOK_IMAGE_SERVER_PATH', SERVER_PATH . 'assets/img/books/');
}
if (!defined('BOOK_IMAGE_SITE_PATH')) {
    define('BOOK_IMAGE_SITE_PATH', SITE_PATH . 'assets/img/books/');
}
?>
