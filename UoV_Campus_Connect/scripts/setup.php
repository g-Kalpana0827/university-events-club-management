<?php
/** Repeatable CLI setup; existing users/passwords are never reseeded. */
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require dirname(__DIR__).'/vendor_autoload.php';
try {
    if (PHP_VERSION_ID < 80100) throw new RuntimeException('PHP 8.1 or newer is required.');
    if (!extension_loaded('pdo_mysql')) throw new RuntimeException('Enable pdo_mysql in php.ini.');
    $cfg=config('db');
    if (!preg_match('/^[a-zA-Z0-9_]+$/',$cfg['database'])) throw new RuntimeException('DB_NAME must contain only letters, numbers or underscores.');
    $dsn=sprintf('mysql:host=%s;port=%s;charset=utf8mb4',$cfg['host'],$cfg['port']);
    $pdo=new PDO($dsn,$cfg['username'],$cfg['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    // An account allowed to use an existing DB need not have CREATE DATABASE permission.
    try { $pdo->exec('USE `'.$cfg['database'].'`'); }
    catch (PDOException $error) {
        if (($error->errorInfo[1]??null)!==1049) throw $error;
        $pdo->exec('CREATE DATABASE `'.$cfg['database'].'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $pdo->exec('USE `'.$cfg['database'].'`');
    }
    App\Core\Schema::apply($pdo);
    echo "Database setup/upgrade completed. No existing users were replaced.\n";
    if (in_array('--demo',$argv,true) && scalar('SELECT COUNT(*) FROM users')===0) {
        $password=bin2hex(random_bytes(8)).'Aa7!';
        $argv=[$argv[0],$password];
        require __DIR__.'/seed.php';
        echo "\nDemo password for the accounts listed above: ".$password."\n";
        echo "Keep this password. Change it through Profile after signing in.\n";
    } elseif (scalar('SELECT COUNT(*) FROM users')>0) {
        echo "Existing users detected. Keep using your current login credentials.\n";
    } else {
        echo "To add sample accounts to this empty database: php scripts/setup.php --demo\n";
    }
} catch (Throwable $error) {
    fwrite(STDERR,"Setup failed: ".$error->getMessage()."\nCheck MySQL is running and config/app.php (or config/local.php) is correct.\n");
    exit(1);
}
