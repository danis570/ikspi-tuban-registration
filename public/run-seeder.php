<?php

date_default_timezone_set('Asia/Jakarta');

require_once __DIR__ . '/../vendor/autoload.php';

use Danish\IkspiTubanRegistration\Config\DatabaseSeeder;
use Dotenv\Dotenv;
use Danish\IkspiTubanRegistration\Config\Database;

$startEnv = Dotenv::createImmutable(__DIR__ . '/../');
$startEnv->load();

Database::getConnection('prod');

$seeder = new DatabaseSeeder();
$seeder->runDummyUser();