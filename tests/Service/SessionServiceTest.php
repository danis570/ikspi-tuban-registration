<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

use function PHPUnit\Framework\assertNotNull;

class SessionServiceTest extends TestCase
{
    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        $this->userRepository->deleteAll();
        $this->sessionRepository->deleteAll();
    }

    private function createUser(): User
    {
        $user = new User();
        $user->email = 'danish@gmail.com';
        $user->password = 'rahasia';
        $user->role = UserRole::ADMIN;

        return $user;
    }

    function testCreate()
    {
        $user = $this->createUser();
        $result = $this->userRepository->save($user);

        $session = $this->sessionService->create($result->id);
        self::assertEquals($result->id, $session->userId);
    }

    function testGetCurrentUser()
    {
        $user = $this->createUser();
        $result = $this->userRepository->save($user);
        $session = $this->sessionService->create($result->id);
        $_COOKIE[SessionService::$cookieName] = $session->id;

        $currentUser = $this->sessionService->getCurrentUser();
        self::assertNotNull($currentUser);
        self::assertEquals($result->id, $currentUser->id);
        self::assertEquals($result->email, $currentUser->email);
    }

    function testDestroy()
    {
        $user = $this->createUser();
        $result = $this->userRepository->save($user);
        $session = $this->sessionService->create($result->id);
        $_COOKIE[SessionService::$cookieName] = $session->id;

        $this->sessionService->destroy();

        $sessionNow = $this->sessionRepository->findById($session->id);

        self::assertNull($sessionNow);

        self::assertArrayNotHasKey(
            SessionService::$cookieName,
            $_COOKIE
        );
    }
}
