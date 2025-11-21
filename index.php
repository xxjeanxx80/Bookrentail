<?php
declare(strict_types=1);

// Central router for Vercel
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = $_GET['page'] ?? 'home';
$page = trim($page, " \t\n\r\0\x0B/"); // basic sanitization

$baseDir = __DIR__;
$routes = [
    // Public pages
    'home'               => $baseDir . '/pages/index.php',
    'about'              => $baseDir . '/pages/aboutUs.php',
    'book'               => $baseDir . '/pages/book.php',
    'category'           => $baseDir . '/pages/bookCategory.php',
    'checkout'           => $baseDir . '/pages/checkout.php',
    'orders'             => $baseDir . '/pages/myOrder.php',
    'profile'            => $baseDir . '/pages/profile.php',
    'register'           => $baseDir . '/pages/register.php',
    'login'              => $baseDir . '/pages/SignIn.php',
    'logout'             => $baseDir . '/pages/logout.php',
    'search'             => $baseDir . '/pages/search.php',
    'terms'              => $baseDir . '/pages/termsAndCondition.php',
    'thanks'             => $baseDir . '/pages/thankYou.php',

    // Admin aliases
    'admin'              => $baseDir . '/Admin/login.php',
    'admin-login'        => $baseDir . '/Admin/login.php',
    'admin-books'        => $baseDir . '/Admin/books.php',
    'admin-manage-books' => $baseDir . '/Admin/manageBooks.php',
    'admin-categories'   => $baseDir . '/Admin/categories.php',
    'admin-manage-categories' => $baseDir . '/Admin/manageCategories.php',
    'admin-users'        => $baseDir . '/Admin/users.php',
    'admin-orders'       => $baseDir . '/Admin/orders.php',
    'admin-logout'       => $baseDir . '/Admin/logout.php',
];

$target = $routes[$page] ?? null;

if (!$target || !file_exists($target)) {
    http_response_code(404);
    echo '404 - Page not found';
    exit;
}

require $target;
