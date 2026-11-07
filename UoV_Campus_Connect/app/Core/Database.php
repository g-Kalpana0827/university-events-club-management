<?php
namespace App\Core;
use PDO;
use PDOException;
use RuntimeException;

final class Database {
    private static ?PDO $instance = null;
    public static function connection(): PDO {
        if (self::$instance) return self::$instance;
        $cfg = config('db');
        if (!extension_loaded('pdo_mysql')) {
            throw new RuntimeException('The pdo_mysql PHP extension is required. Enable it in your XAMPP php.ini.');
        }
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $cfg['host'], $cfg['port'], $cfg['database']);
        self::$instance = new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        self::$instance->exec("SET time_zone = '+05:30'");
        return self::$instance;
    }
}
