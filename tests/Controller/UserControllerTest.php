<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Config\DatabaseSeeder;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class UserControllerTest extends TestCase
{
    private UserController $userController;
    private UserRepository $userRepository;


    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        putenv('APP_ENV=test');

        $this->userController = new UserController();
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->userRepository->deleteAll();
    }

    function testDeleteUserSuccess()
    {
        $db = new DatabaseSeeder();
        $db->runDummyUser();

        $user = $this->userRepository->findByEmail('user@example.com');
        $_POST['id'] = $user->id;
        $html = $this->userController->postDelete();

        self::assertStringContainsString('success redirect', $html);
    }

    function testDeleteUserFailedUSerFound()
    {
        $db = new DatabaseSeeder();
        $db->runDummyUser();

        $user = $this->userRepository->findByEmail('user@example.com');
        $_POST['id'] = 898989;
        $html = $this->userController->postDelete();

        self::assertStringContainsString('Delete error', $html);
    }

    function testGetAllUser()
    {
        $db = new DatabaseSeeder();
        $db->runDummyUser();
        $html = $this->userController->allUser();

        self::assertStringContainsString('admin@', $html);
        self::assertStringContainsString('user@', $html);
    }

    function testGetAllUserEmpty()
    {
        $html = $this->userController->allUser();

        self::assertStringContainsString('Data empty: Users', $html);
    }
}
