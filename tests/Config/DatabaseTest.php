<?php

namespace Danish\IkspiTubanRegistration\Config;

use Danish\IkspiTubanRegistration\TestCase;
use Dotenv\Dotenv;
use Override;
use PDO;

class DatabaseTest extends TestCase
{
    #[Override]
    function setUp(): void
    {
        parent::setUp();
    }
    function testGetConnection()
    {
        $pdo = Database::getConnection();
        self::isInstanceOf(PDO::class);

        $stmt = $pdo->query('SELECT DATABASE()');
        $dbName = $stmt->fetchColumn();
        self::assertEquals('ikspi_tuban_dev', $dbName);
    }

    function testConnectionSingletone()
    {
        $pdo1 = Database::getConnection();
        $pdo2 = Database::getConnection();

        self::assertSame($pdo1, $pdo2);
    }
}
