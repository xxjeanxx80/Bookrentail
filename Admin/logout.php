<?php
require(__DIR__ . '/../config/connection.php');
require(__DIR__ . '/../includes/function.php');

session_start();

// Xóa token Remember Me nếu có
if (isset($_COOKIE['admin_remember_token'])) {
    deleteAdminRememberToken($con, $_COOKIE['admin_remember_token']);
}

// Xóa session (thống nhất sử dụng ADMIN_email - chữ thường)
unset($_SESSION['ADMIN_LOGIN']);
unset($_SESSION['ADMIN_email']);

header('location:login.php');
die();
