<?php
$settings = [
    'name' => 'Vanni Campus Connect',
    'timezone' => 'Asia/Colombo',
    'db' => [
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_NAME') ?: 'uov_event_club',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') === false ? '' : getenv('DB_PASS'),
    ],
];
$localFile = __DIR__.'/local.php';
if (is_file($localFile)) {
    $overrides = require $localFile;
    if (!is_array($overrides)) throw new RuntimeException('config/local.php must return a settings array.');
    $settings = array_replace_recursive($settings, $overrides);
}
return $settings;
