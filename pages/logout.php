<?php
require(__DIR__ . '/../config/connection.php');
require(__DIR__ . '/../includes/function.php');

session_start();

// Xóa token Remember Me nếu có
if (isset($_COOKIE['remember_token'])) {
    deleteRememberToken($con, $_COOKIE['remember_token']);
}

// Xóa session
unset($_SESSION['USER_LOGIN']);
unset($_SESSION['USER_ID']);
unset($_SESSION['USER_NAME']);
unset($_SESSION['BeforeCheckoutLogin']);

header('location:index.php');
die();
