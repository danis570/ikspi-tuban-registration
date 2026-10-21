<?php

namespace Danish\IkspiTubanRegistration\Middleware;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Config\DatabaseSeeder;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class MustNotLoginMiddlewareTest extends TestCase
{
    private MustNotLoginMiddleware $middleware;
    private SessionService $sessionService;
    private UserRepository $userRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        putenv('APP_ENV=test');

        $pdo = Database::getConnection();

        $sessionRepository = new SessionRepository($pdo);
        $this->userRepository = new UserRepository($pdo);

        $this->sessionService = new SessionService(
            $sessionRepository,
            $this->userRepository
        );

        $this->middleware = new MustNotLoginMiddleware();
        unset($_COOKIE[SessionService::$cookieName]);
        View::$redirectPath = null;
    }

    function testAfterLogin()
    {
        $startDb = new DatabaseSeeder();
        $startDb->runDummyUser();

        $user = $this->userRepository->findByEmail('user@example.com');
        $session = $this->sessionService->create($user->id);

        $_COOKIE[SessionService::$cookieName] = $session->id;

        $this->middleware->before();
        self::assertEquals('/', View::$redirectPath);
    }

    function testIsNotLogin()
    {
        $startDb = new DatabaseSeeder();
        $startDb->runDummyUser();

        $user = $this->userRepository->findByEmail('user@example.com');

        $this->middleware->before();

        self::assertNull(View::$redirectPath);
    }
}
