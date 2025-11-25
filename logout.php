<?php
require('connection.php');
session_start();

// ============================================================================
// CUSTOMER LOGOUT - CLEAN CODE GIẢI THÍCH TẠI SAU
// ============================================================================

// Delete customer tokens from database when logging out
// Giải thích: Xóa tất cả Remember Me tokens của user
// NGHIỆP VỤ: Đảm bảo logout hoàn toàn, không thể auto-login lại
if (isset($_SESSION['USER_ID'])) {
    $user_id = $_SESSION['USER_ID'];
    $deleteTokensSql = "DELETE FROM user_tokens WHERE user_id = '$user_id'";
    pg_query($con, $deleteTokensSql);
}

// Clear session variables
// Giải thích: Xóa tất cả session data của customer
unset($_SESSION['USER_LOGIN']);
unset($_SESSION['USER_ID']);
unset($_SESSION['USER_NAME']);

// Clear remember me cookie
// Giải thích: Xóa cookie trên browser
setcookie('user_auth', '', time() - 3600, "/");

// Destroy session completely
session_destroy();

// Redirect to home page
header('location:index.php');
die();
?>
