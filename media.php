<?php
declare(strict_types=1);

define('BOOKRENTAIL_SKIP_SESSION', true);
require __DIR__ . '/config/connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(404);
    exit('Not found');
}

$sql = 'SELECT file_name, mime_type, data FROM book_media WHERE id = $1';
$result = pg_query_params($con, $sql, [$id]);

if (!$result || pg_num_rows($result) === 0) {
    http_response_code(404);
    exit('Not found');
}

$row = pg_fetch_assoc($result);
$mimeType = $row['mime_type'] ?: 'application/octet-stream';
$fileName = $row['file_name'] ?: ('book-' . $id);
$binary = pg_unescape_bytea($row['data']);

header('Content-Type: ' . $mimeType);
header('Content-Disposition: inline; filename="' . basename($fileName) . '"');
header('Cache-Control: public, max-age=31536000, immutable');
header('Content-Length: ' . strlen($binary));

echo $binary;
exit;

