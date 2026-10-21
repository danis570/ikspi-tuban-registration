<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Dotenv\Dotenv;
use PDOException;
use Danish\IkspiTubanRegistration\TestCase;
use TypeError;

class UserRepositoryTest extends TestCase
{
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->userRepository->deleteAll();
    }

    private function createUser(): User
    {
        $user = new User();
        $user->email = 'danish@gmail.com';
        $user->password = 'rahasia';
        $user->role = UserRole::ADMIN;

        return $user;
    }

    function testDeleteByIdSuccess()
    {
        $user = $this->createUser();
        $this->userRepository->save($user);

        $result = $this->userRepository->findByEmail($user->email);
        $dell = $this->userRepository->deleteById($result->id);
        $ress = $this->userRepository->findByEmail($user->email);

        self::assertTrue($dell);
        self::assertNull($ress);

    }

    function testDeleteByIdFoundUser()
    {
        $dell = $this->userRepository->deleteById(123123);

        self::assertFalse($dell);
    }

    function testFindAll()
    {
        $user = $this->createUser();
        $this->userRepository->save($user);
        $users = $this->userRepository->findAll();

        self::assertCount(1, $users);
    }

    function testFindByEmail()
    {
        $user = $this->createUser();
        $result = $this->userRepository->save($user);
        $response = $this->userRepository->findByEmail($result->email);

        self::assertEquals($user->email, $response->email);
        self::assertEquals($user->password, $response->password);
        self::assertEquals($user->id, $response->id);
        self::assertEquals($user->role, $response->role);
    }


    function testsFindById()
    {
        $user = $this->createUser();
        $result = $this->userRepository->save($user);
        $response = $this->userRepository->findById($result->id);

        self::assertEquals($user->email, $response->email);
        self::assertEquals($user->password, $response->password);
        self::assertEquals($user->id, $response->id);
        self::assertEquals($user->role, $response->role);
    }

    function testUpdatePasswordById()
    {
        $user = $this->createUser();
        $resposne = $this->userRepository->save($user);

        $newPassword = 'tests';
        $result = $this->userRepository->updatePasswordById($resposne->id, $newPassword);

        $user = $this->userRepository->findByEmail($user->email);
        self::assertEquals($user->password, $newPassword);
    }

    function testUpdateEmailById()
    {
        $user = $this->createUser();
        $resposne = $this->userRepository->save($user);

        $newEmail = 'ahmad@gmail.com';
        $this->userRepository->updateEmailById($resposne->id, $newEmail);

        self::assertEquals($resposne->email, $user->email);
    }

    function testSaveSuccess()
    {
        $user = $this->createUser();

        $respose = $this->userRepository->save($user);
        $expec = $this->userRepository->findById($respose->id);

        self::assertEquals($user->email, $respose->email);
        self::assertEquals($user->password, $respose->password);
        self::assertEquals($user->role->value, $respose->role->value);
        self::assertEquals($respose->id, $expec->id);
    }

    function testSaveFailedDuplicateEmail()
    {
        $user = $this->createUser();

        self::expectException(PDOException::class);
        self::expectExceptionMessageIs("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'danish@gmail.com' for key 'email'");

        $respose = $this->userRepository->save($user);
        $respose = $this->userRepository->save($user);
    }

    function testSaveFailedErrorValueRole()
    {
        $user = $this->createUser();

        self::expectException(TypeError::class);
        $user->role = 'not match';
    }

}