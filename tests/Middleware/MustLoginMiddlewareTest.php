<?php

namespace Danish\IkspiTubanRegistration;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Config\DatabaseSeeder;
use Danish\IkspiTubanRegistration\Middleware\MustLoginMiddleware;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Override;

class MustLoginMiddlewareTest extends TestCase
{
    private MustLoginMiddleware $middleware;
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

        $this->middleware = new MustLoginMiddleware();
        unset($_COOKIE[SessionService::$cookieName]);
        View::$redirectPath = null;
    }

    public function testNotLogin(): void
    {
        $this->middleware->before();

        self::assertEquals('/login', View::$redirectPath);
    }

    public function testAfterLogin(): void
    {
        $seeder = new DatabaseSeeder();
        $seeder->runDummyUser();

        $user = $this->userRepository->findByEmail(
            'user@example.com'
        );

        $session = $this->sessionService->create($user->id);

        $_COOKIE[SessionService::$cookieName] = $session->id;

        $this->middleware->before();

        self::assertNull(View::$redirectPath);
    }
}
