<?php

namespace Danish\IkspiTubanRegistration\Middleware;

use Danish\PhpRouter\Middleware\Middleware;
use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Override;

class MustLoginMiddleware implements Middleware
{
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;
    private SessionService $sessionService;

    public function __construct()
    {
        $pdo = Database::getConnection();
        $this->userRepository = new UserRepository($pdo);
        $this->sessionRepository = new SessionRepository($pdo);

        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);
    }
    #[Override]
    public function before(): void
    {
        $session = $this->sessionService->getCurrentUser();
        if ($session === null) {
            View::redirect('/login');
        }
    }
}
