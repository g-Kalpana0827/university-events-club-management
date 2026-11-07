<?php
spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) return;
    $file = __DIR__.'/app/'.str_replace('\\','/',substr($class,4)).'.php';
    if (is_file($file)) require $file;
});
require __DIR__.'/app/Core/Helpers.php';
date_default_timezone_set(config('timezone', 'Asia/Colombo'));
if (PHP_SAPI !== 'cli' && session_status() !== PHP_SESSION_ACTIVE) {
    session_name('vanni_connect');
    session_set_cookie_params(['lifetime'=>0,'path'=>(base_path()?:'/'),'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off','httponly'=>true,'samesite'=>'Lax']);
    ini_set('session.use_strict_mode','1');
    if (!session_start()) {
        http_response_code(500);
        exit('PHP sessions are unavailable. Check session.save_path and its write permissions in php.ini.');
    }
}
