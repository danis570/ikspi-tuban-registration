<?php

namespace Danish\IkspiTubanRegistration;

use Dotenv\Dotenv;
use Override;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

abstract class TestCase extends FrameworkTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $env = Dotenv::createImmutable(__DIR__ . '/../');
        $env->load();
    }
}