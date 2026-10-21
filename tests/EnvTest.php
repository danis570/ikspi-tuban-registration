<?php

namespace Danish\IkspiTubanRegistration;

use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class EnvTest extends TestCase
{
    function testConfigDatabase()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        require_once __DIR__ . '/../config/database.php';
        $db = getDatabaseConfig();

        self::assertIsArray($db);
    }
}