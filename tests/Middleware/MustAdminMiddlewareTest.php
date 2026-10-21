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

class MustAdminMiddlewareTest extends TestCase
{
    private MustAdminMiddleware $middleware;
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

        $this->middleware = new MustAdminMiddleware();
        unset($_COOKIE[SessionService::$cookieName]);
        View::$redirectPath = null;
    }

    function testUserAccessAdmin()
    {
        $seeder = new DatabaseSeeder();
        $seeder->runDummyUser();

        $user = $this->userRepository->findByEmail(
            'user@example.com'
        );

        $session = $this->sessionService->create($user->id);

        $_COOKIE[SessionService::$cookieName] = $session->id;

        $this->middleware->before();

        self::assertEquals('/', View::$redirectPath);
    }

    public function testAdminAccessAdmin(): void
    {
        $seeder = new DatabaseSeeder();
        $seeder->runDummyUser();

        $admin = $this->userRepository->findByEmail(
            'admin@example.com'
        );

        $session = $this->sessionService->create($admin->id);

        $_COOKIE[SessionService::$cookieName] = $session->id;

        $this->middleware->before();

        self::assertNull(View::$redirectPath);
    }
}
