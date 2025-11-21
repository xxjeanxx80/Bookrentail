<?php
declare(strict_types=1);

// Central router cho Vercel (file nằm trong thư mục api/ để Vercel nhận diện function)

/**
 * Chuẩn hóa slug: bỏ ký tự dư thừa và chuyển về lowercase
 */
function bookrentail_sanitize_page(string $page): string
{
    $clean = trim($page, " \t\n\r\0\x0B/");
    return str_replace(['\\', '..'], '', $clean); // chống path traversal đơn giản
}

/**
 * Kiểm tra str_starts_with cho PHP < 8
 */
function bookrentail_starts_with(string $haystack, string $needle): bool
{
    return strncmp($haystack, $needle, strlen($needle)) === 0;
}

/**
 * Tìm file đích tương ứng với page hiện tại
 */
function bookrentail_resolve_route(string $page, array $routes, string $baseDir): ?string
{
    $slug = strtolower($page);
    $normalizedBase = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

    if ($slug === '' || $slug === 'index.php') {
        $slug = 'home';
    }

    if (isset($routes[$slug])) {
        return $routes[$slug];
    }

    // Nếu người dùng yêu cầu đuôi .php (vd: book.php) thì bỏ đuôi để thử map
    if (str_ends_with($slug, '.php')) {
        $trimmed = substr($slug, 0, -4);
        if (isset($routes[$trimmed])) {
            return $routes[$trimmed];
        }
    }

    // Cho phép truy cập trực tiếp file: pages/book.php, Admin/*.php ...
    $relative = $page;
    if (!str_ends_with($relative, '.php')) {
        $relative .= '.php';
    }
    $relative = ltrim($relative, '/');
    $candidate = $baseDir . '/' . $relative;
    $real = realpath($candidate);

    if ($real && bookrentail_starts_with($real, $normalizedBase) && is_file($real)) {
        return $real;
    }

    return null;
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool
    {
        $len = strlen($needle);
        if ($len === 0) {
            return true;
        }
        return substr($haystack, -$len) === $needle;
    }
}

$rawPage = $_GET['page'] ?? 'home';
$page = bookrentail_sanitize_page($rawPage);

$baseDir = realpath(dirname(__DIR__));
if ($baseDir === false) {
    http_response_code(500);
    echo 'Base directory not found';
    exit;
}

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
    'admin'                  => $baseDir . '/Admin/login.php',
    'admin-login'            => $baseDir . '/Admin/login.php',
    'admin-books'            => $baseDir . '/Admin/books.php',
    'admin-manage-books'     => $baseDir . '/Admin/manageBooks.php',
    'admin-categories'       => $baseDir . '/Admin/categories.php',
    'admin-manage-categories'=> $baseDir . '/Admin/manageCategories.php',
    'admin-users'            => $baseDir . '/Admin/users.php',
    'admin-orders'           => $baseDir . '/Admin/orders.php',
    'admin-logout'           => $baseDir . '/Admin/logout.php',
];

$target = bookrentail_resolve_route($page, $routes, $baseDir);

if (!$target) {
    http_response_code(404);
    echo '404 - Page not found';
    exit;
}

require $target;
