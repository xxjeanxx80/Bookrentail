<?php
/**
 * File cấu hình kết nối database, đường dẫn và session cho môi trường Vercel
 */

declare(strict_types=1);

/**
 * Chuẩn hóa đường dẫn (forward slash) để dùng cho so sánh sau này
 */
function bookrentail_normalize_path(string $path): string
{
    return rtrim(str_replace('\\', '/', $path), '/') . '/';
}

/**
 * Parse connection string (postgres://user:pass@host:port/db) thành mảng
 */
function bookrentail_parse_pg_url(?string $url): ?array
{
    if (empty($url)) {
        return null;
    }

    $parts = parse_url($url);
    if ($parts === false || !isset($parts['scheme']) || $parts['scheme'] !== 'postgres') {
        return null;
    }

    parse_str($parts['query'] ?? '', $queryItems);

    return [
        'host' => $parts['host'] ?? '127.0.0.1',
        'port' => $parts['port'] ?? 5432,
        'user' => $parts['user'] ?? 'postgres',
        'pass' => $parts['pass'] ?? 'postgres',
        'name' => isset($parts['path']) ? ltrim($parts['path'], '/') : 'postgres',
        'sslmode' => $queryItems['sslmode'] ?? null,
    ];
}

// Ưu tiên biến môi trường dạng URL (Vercel Postgres/Neon/Supabase)
$url = getenv('VERCEL_POSTGRES_URL') ?: getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');
$url = $url === false ? null : $url;
$urlConfig = bookrentail_parse_pg_url($url);

$dbHost = $urlConfig['host'] ?? (getenv('DB_HOST') ?: '127.0.0.1');
$dbUser = $urlConfig['user'] ?? (getenv('DB_USER') ?: 'postgres');
$dbPass = $urlConfig['pass'] ?? (getenv('DB_PASS') ?: (getenv('DB_PASSWORD') ?: 'postgres'));
$dbName = $urlConfig['name'] ?? (getenv('DB_NAME') ?: 'mini_project');
$dbPort = (int)($urlConfig['port'] ?? (getenv('DB_PORT') ?: 5432));
$dbSslMode = $urlConfig['sslmode'] ?? getenv('DB_SSLMODE');

// Với môi trường Vercel/Neon cần sslmode=require (trừ khi override)
if (!$dbSslMode) {
    $isLocal = in_array($dbHost, ['127.0.0.1', 'localhost'], true);
    $dbSslMode = $isLocal ? 'disable' : 'require';
}

$connParts = [
    "host=$dbHost",
    "port=$dbPort",
    "dbname=$dbName",
    "user=$dbUser",
    "password=$dbPass",
    "sslmode=$dbSslMode",
];

// Debug: Check if pgsql extension is loaded
if (!function_exists('pg_connect')) {
    error_log('pg_connect function does not exist. pgsql extension likely not loaded.');
    throw new RuntimeException('pg_connect function not available. Check if pgsql extension is enabled in php.ini.');
}

$databaseConnection = @pg_connect(implode(' ', $connParts));
if (!$databaseConnection) {
    throw new RuntimeException('Connection failed: Unable to connect to PostgreSQL database with provided parameters');
}

// Tạo alias $con để tương thích với code cũ (sẽ refactor dần)
$con = $databaseConnection;

$shouldStartSession = !defined('BOOKRENTAIL_SKIP_SESSION') || BOOKRENTAIL_SKIP_SESSION !== true;

// Thiết lập session handler dùng Postgres để tương thích serverless
if ($shouldStartSession && session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/sessionHandler.php';
    $sessionHandler = new PgsqlSessionHandler($con);
    session_set_save_handler($sessionHandler, true);
    session_start();
}

// Xác định đường dẫn vật lý gốc của project
$projectRoot = bookrentail_normalize_path(realpath(__DIR__ . '/..') ?: __DIR__ . '/..');
if (!defined('SERVER_PATH')) {
    // Có thể override bằng biến môi trường nếu deploy trong thư mục khác
    $serverPath = getenv('SERVER_PATH') ?: $projectRoot;
    define('SERVER_PATH', bookrentail_normalize_path($serverPath));
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
    $sitePath = $appUrlFromEnv ?: $inferredUrl ?: 'http://localhost/';
    define('SITE_PATH', rtrim($sitePath, '/') . '/');
    define('SITE_PATH', rtrim($sitePath, '/') . '/');
}

// Đường dẫn thư mục chứa ảnh sách (đọc-only trên Vercel, upload xử lý ngoài)
if (!defined('BOOK_IMAGE_SERVER_PATH')) {
    define('BOOK_IMAGE_SERVER_PATH', SERVER_PATH . 'assets/img/books/');
}
if (!defined('BOOK_IMAGE_SITE_PATH')) {
    define('BOOK_IMAGE_SITE_PATH', SITE_PATH . 'assets/img/books/');
}
?>
