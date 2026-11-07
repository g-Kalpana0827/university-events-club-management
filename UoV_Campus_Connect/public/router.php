<?php
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
if (str_contains($path, "\0")) { http_response_code(400); exit('Invalid path.'); }
$asset = realpath(__DIR__.$path);
if ($asset && str_starts_with($asset, __DIR__.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR)
    && is_file($asset) && in_array(strtolower(pathinfo($asset, PATHINFO_EXTENSION)), ['css','js','svg','png','jpg','jpeg','webp','ico'], true)) return false;
if ($path !== '/' && $path !== '/index.php') {
    http_response_code(404); exit('File not found.');
}
require __DIR__.'/index.php';
