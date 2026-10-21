<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\Session;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class SessionReositoryTest extends TestCase
{
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->userRepository->deleteAll();
        $this->sessionRepository->deleteAll();
    }

    private function createUser(): User
    {
        $user = new User();
        $user->email = 'danish@gmail.com';
        $user->password = 'rahasia';
        $user->role = UserRole::ADMIN;

        $result = $this->userRepository->save($user);
        return $result;
    }

    function testSave()
    {
        $user = $this->createUser();

        $session = new Session();
        $session->id = bin2hex(random_bytes(32));
        $session->userId = $user->id;
        $result = $this->sessionRepository->save($session);

        self::assertEquals($session->id, $result->id);
        self::assertEquals($session->userId, $result->userId);
    }

    function testFindByUserId()
    {
        $user = $this->createUser();

        $session = new Session();
        $session->id = bin2hex(random_bytes(32));
        $session->userId = $user->id;
        $this->sessionRepository->save($session);

        $result = $this->sessionRepository->findByUserId($session->userId);

        self::assertEquals($session->id, $result->id);
        self::assertEquals($session->userId, $result->userId);
    }

    function testDeleteByUserId()
    {
        $user = $this->createUser();

        $session = new Session();
        $session->id = bin2hex(random_bytes(32));
        $session->userId = $user->id;
        $this->sessionRepository->save($session);

        $this->sessionRepository->deleteByUserId($session->userId);

        $result = $this->sessionRepository->findByUserId($session->userId);
        print_r($result);
        self::assertNull($result);
    }

     function testDeleteById()
    {
        $user = $this->createUser();

        $session = new Session();
        $session->id = bin2hex(random_bytes(32));
        $session->userId = $user->id;
        $this->sessionRepository->save($session);

        $this->sessionRepository->deleteById($session->id);

        $result = $this->sessionRepository->findByUserId($session->userId);
        print_r($result);
        self::assertNull($result);
    }
}