<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\Profile;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\TestCase;
use Override;
use PDOException;

use function PHPUnit\Framework\assertNotNull;

class ProfileRepositoryTest extends TestCase
{
    private UserRepository $userRepository;
    private ProfileRepository $profileRepository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->profileRepository = new ProfileRepository(Database::getConnection());
        $this->profileRepository->deleteAll();
        $this->userRepository->deleteAll();
    }

    private function createUser(): User
    {
        $user = new User();
        $user->email = 'dan@gmail.com';
        $user->password = 't=rhs';
        $user->role = UserRole::USER;

        $r = $this->userRepository->save($user);
        return $r;
    }

    function testFindByUserIdSuccess()
    {
        $user = $this->createUser();

        $profile = new Profile();
        $profile->userId = $user->id;
        $profile->name = 'danish';
        $profile->avatar = 'danish.jpg';
        $r = $this->profileRepository->save($profile);

        $ress = $this->profileRepository->findByUserId($user->id);

        self::assertEquals($profile->name, $ress->name);
        self::assertEquals($profile->avatar, $ress->avatar);
    }

     function testFindByUserIdFailedUserFound()
    {
        $user = $this->createUser();

        $profile = new Profile();
        $profile->userId = $user->id;
        $profile->name = 'danish';
        $profile->avatar = 'danish.jpg';
        $r = $this->profileRepository->save($profile);

        $ress = $this->profileRepository->findByUserId(768628);

        self::assertNull($ress);
    }

    function testSaveProfileSuccess()
    {
        $user = $this->createUser();

        $profile = new Profile();
        $profile->userId = $user->id;
        $profile->name = 'danish';
        $profile->avatar = 'danish.jpg';
        $r = $this->profileRepository->save($profile);

        self::assertNotNull($r);
        self::assertEquals($profile->userId, $r->userId);
        self::assertEquals($profile->name, $r->name);
        self::assertEquals($profile->avatar, $r->avatar);
    }

    function testSaveFailedUserIdFound()
    {
        self::expectException(PDOException::class);
        $profile = new Profile();
        $profile->userId = 12412;
        $profile->name = 'danish';
        $profile->avatar = 'danish.jpg';
        $r = $this->profileRepository->save($profile);
    }

    function testSaveUpdateSuccess()
    {
        $user = $this->createUser();

        $profile = new Profile();
        $profile->userId = $user->id;
        $profile->name = 'danish';
        $profile->avatar = 'danish.jpg';
        $r = $this->profileRepository->save($profile);

        $profile = new Profile();
        $profile->userId = $user->id;
        $profile->name = 'jack';
        $profile->avatar = 'jack.jpg';
        $r = $this->profileRepository->update($profile);

        self::assertTrue($r);
    }

    function testSaveUpdateFailedFoundUserId()
    {
        $user = $this->createUser();

        $profile = new Profile();
        $profile->userId = $user->id;
        $profile->name = 'danish';
        $profile->avatar = 'danish.jpg';
        $r = $this->profileRepository->save($profile);

        $profile = new Profile();
        $profile->userId = 8868686;
        $profile->name = 'jack';
        $profile->avatar = 'jack.jpg';
        $r = $this->profileRepository->update($profile);

        self::assertFalse($r);
    }

}
