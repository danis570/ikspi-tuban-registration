<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Config\DatabaseSeeder;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class HomeControllerTest extends TestCase
{
    private HomeController $homeController;
    private SessionService $sessionService;
    private UserRepository $userRepository;


    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->homeController = new HomeController();
        $sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $this->userRepository);
    }

    function testGetHomePublic()
    {
        $html = $this->homeController->home();
        self::assertStringContainsString('User Management', $html);
        self::assertStringContainsString('body', $html);
        self::assertStringContainsString('footer', $html);
    }

    function testHomeUser()
    {
        $startdb = new DatabaseSeeder();
        $startdb->runDummyUser();

        $user = $this->userRepository->findByEmail('user@example.com');
        $sess = $this->sessionService->create($user->id);
        $_COOKIE[SessionService::$cookieName] = $sess->id;

        $session = $this->sessionService->getCurrentUser();
        $html = $this->homeController->home();

        self::assertStringContainsString('USER', $html);
    }

    function testHomeAdmin()
    {
        $startdb = new DatabaseSeeder();
        $startdb->runDummyUser();

        $user = $this->userRepository->findByEmail('admin@example.com');
        $sess = $this->sessionService->create($user->id);
        $_COOKIE[SessionService::$cookieName] = $sess->id;

        $session = $this->sessionService->getCurrentUser();
        $html = $this->homeController->home();

        self::assertStringContainsString('ADMIN', $html);
    }
}