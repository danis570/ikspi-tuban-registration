<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Config\DatabaseSeeder;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class UserAuthControllerTests extends TestCase
{
    private UserAuthController $userAuthController;
    private SessionService $sessionService;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        putenv('APP_ENV=test');
        $this->userAuthController = new UserAuthController();
        $pdo = Database::getConnection();
        $this->sessionRepository = new SessionRepository($pdo);
        $this->userRepository = new UserRepository($pdo);

        $this->sessionService = new SessionService(
            $this->sessionRepository,
            $this->userRepository
        );
    }

    function testGetRegister()
    {
        $html = $this->userAuthController->register();
        self::assertStringContainsString('Login', $html);
        self::assertStringContainsString('form', $html);
        self::assertStringContainsString('submit', $html);
        self::assertStringContainsString('Register Page', $html);
    }

    function testPostRegister()
    {
        $_POST['email'] = 'danish@gmail.com';
        $_POST['password'] = '123';
        $html = $this->userAuthController->postRegister();
        self::assertStringContainsString('success redirect', $html);
    }

    function testPostRegisterBlankField()
    {
        $_POST['email'] = 'danish@gmail.com';
        $_POST['password'] = '';
        $html = $this->userAuthController->postRegister();
        self::assertStringContainsString('error', $html);
    }

    function testGetLogin()
    {
        $html = $this->userAuthController->login();
        self::assertStringContainsString('Login', $html);
        self::assertStringContainsString('form', $html);
        self::assertStringContainsString('submit', $html);
        self::assertStringContainsString('Login Page', $html);
    }

    function testPostLoginSuccessAdmin()
    {
        $seeder = new DatabaseSeeder();
        $seeder->runDummyUser();
        // !!! RUN THE SEEDER FIRST

        $_POST['email'] = 'admin@example.com';
        $_POST['password'] = 'admin';

        $html = $this->userAuthController->postLogin();
        self::assertStringContainsString('success redirect', $html);
    }

    function testPostLoginSuccessUser()
    {
        $seeder = new DatabaseSeeder();
        $seeder->runDummyUser();
        // !!! RUN THE SEEDER FIRST

        $_POST['email'] = 'user@example.com';
        $_POST['password'] = 'user';

        $html = $this->userAuthController->postLogin();
        self::assertStringContainsString('success redirect', $html);
    }

    function testPostLoginWrongPassword()
    {
        $seeder = new DatabaseSeeder();
        $seeder->runDummyUser();
        // !!! RUN THE SEEDER FIRST

        $_POST['email'] = 'user@example.com';
        $_POST['password'] = 'ur';

        $html = $this->userAuthController->postLogin();
        self::assertStringContainsString('Perhatian: Email atau password salah', $html);
    }

    function testLogout()
    {
        $seeder = new DatabaseSeeder();
        $seeder->runDummyUser();

        $user = $this->userRepository->findByEmail(
            'user@example.com'
        );
        $session = $this->sessionService->create($user->id);

        $_COOKIE[SessionService::$cookieName] = $session->id;

        $this->userAuthController->postLogout();
        $result = $this->sessionRepository->findById($session->id);

        self::assertFalse(isset($_COOKIE[SessionService::$cookieName]));
        self::assertNull($result);
    }

}