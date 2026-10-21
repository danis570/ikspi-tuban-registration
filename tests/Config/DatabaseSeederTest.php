<?php

namespace Danish\IkspiTubanRegistration\Config;

use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class DatabaseSeederTest extends TestCase
{
    private DatabaseSeeder $databaseSeeder;
    private UserRepository $userRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_ENV=test');

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->databaseSeeder = new DatabaseSeeder(Database::getConnection());

        $this->userRepository->deleteAll();
    }

    function testFirstStartSeeder()
    {
        $this->databaseSeeder->runDummyUser();

        $adminResult = $this->userRepository->findByEmail('admin@example.com');
        $userResult = $this->userRepository->findByEmail('user@example.com');

        self::assertEquals($adminResult->email, 'admin@example.com');
        self::assertTrue(password_verify('admin', $adminResult->password));
        self::assertEquals($adminResult->role->value, 'admin');

        self::assertEquals($userResult->email, 'user@example.com');
        self::assertTrue(password_verify('user', $userResult->password));
        self::assertEquals($userResult->role->value, 'user');
    }

    function testFirstAfterStartSeeder()
    {
        $this->databaseSeeder->runDummyUser();
        $this->databaseSeeder->runDummyUser();
        $this->databaseSeeder->runDummyUser();
        $this->databaseSeeder->runDummyUser();
        $this->databaseSeeder->runDummyUser();

        $users = $this->userRepository->findAll();

        self::assertCount(2, $users);
    }
}