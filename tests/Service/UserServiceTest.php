<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\Exception\DataNotFoundException;
use Danish\IkspiTubanRegistration\Exception\DeleteFailedException;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->userService = new UserService($this->userRepository);
        $this->userRepository->deleteAll();
    }

    function testGetAll()
    {
        $user = new User();
        $user->email = 'danish@gmail.com';
        $user->password = '123';
        $user->role = UserRole::USER;

        $this->userRepository->save($user);

        $user = new User();
        $user->email = 'danis@gmail.com';
        $user->password = '123';
        $user->role = UserRole::USER;

        $this->userRepository->save($user);

        $users = $this->userService->getAll();

        self::assertCount(2, $users);
    }

    function testDeleteByIdSuccess()
    {
        $user = new User();
        $user->email = 'danish@gmail.com';
        $user->password = '123';
        $user->role = UserRole::USER;

        $this->userRepository->save($user);
        $r = $this->userRepository->findByEmail($user->email);
        $status = $this->userService->deleteById($r->id);

        self::assertTrue($status);
    }

    function testDeleteByIdFailedUSerNotFound()
    {
        self::expectException(DeleteFailedException::class);
        self::expectExceptionMessageIs('Delete error: User');
        $status = $this->userService->deleteById(905465);
    }

    function testEmptyGetAll()
    {
        self::expectException(DataNotFoundException::class);
        self::expectExceptionMessageIs('Data empty: Users');

        $users = $this->userService->getAll();

        self::assertCount(2, $users);
    }
}
