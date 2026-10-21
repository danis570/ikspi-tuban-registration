<?php

namespace Danish\IkspiTubanRegistration\Config;

use PDO;

class Database
{
    public static ?PDO $pdo = null;

    public static function getConnection(string $env = 'dev'): ?PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        require_once __DIR__ . '/../../config/database.php';
        $db = getDatabaseConfig();
        return self::$pdo =
            new PDO(
                $db['database'][$env]['dsn'],
                $db['database'][$env]['username'],
                $db['database'][$env]['password']
            );
    }
}
