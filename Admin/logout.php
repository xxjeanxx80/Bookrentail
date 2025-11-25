<?php
require('connection.php');
session_start();

// Delete admin tokens from database when logging out
if (isset($_SESSION['ADMIN_ID'])) {
    $admin_id = $_SESSION['ADMIN_ID'];
    $deleteTokensSql = "DELETE FROM admin_tokens WHERE admin_id = '$admin_id'";
    pg_query($con, $deleteTokensSql);
}

// Clear session
unset($_SESSION['ADMIN_LOGIN']);
unset($_SESSION['ADMIN_ID']);
unset($_SESSION['ADMIN_email']);

// Clear cookie
setcookie('admin_auth', '', time() - 3600, "/");

// Redirect to login
header('location:login.php');
die();
?>
