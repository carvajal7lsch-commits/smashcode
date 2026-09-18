<?php
// Enrutador para: php -S 127.0.0.1:8097 tools/local-router.php
header('X-Smashcode-Local: true');
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (preg_match('~^/(?:database|config|includes|app|vendor|tools|tests)(?:/|$)|(?:^|/)\.(?!well-known(?:/|$))~i', $path)) {
    http_response_code(403);
    exit('Acceso denegado.');
}
$root = realpath(dirname(__DIR__));
$file = realpath($root . $path);
if ($file && str_starts_with($file, $root . DIRECTORY_SEPARATOR) && is_file($file)
    && str_starts_with($path, '/assets/')) {
    return false;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
require $root . '/index.php';
