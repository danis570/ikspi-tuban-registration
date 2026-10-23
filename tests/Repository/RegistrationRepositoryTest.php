<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Domain\RegistrationDressSize;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodType;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\TestCase;
use DateTimeImmutable;
use Override;

class RegistrationRepositoryTest extends TestCase
{
    private RegistrationRepository $registrationRepository;
    private RegistrationPeriodRepository $periodRepository;
    private UserRepository $userRepository;

    private int $userId;
    private int $periodId;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $connection = Database::getConnection();

        $this->registrationRepository =
            new RegistrationRepository($connection);

        $this->periodRepository =
            new RegistrationPeriodRepository($connection);

        $this->userRepository =
            new UserRepository($connection);

        // Hapus data anak terlebih dahulu karena foreign key.
        $this->registrationRepository->deleteAll();
        $this->periodRepository->deleteAll();
        $this->userRepository->deleteAll();

        $this->createUser();
        $this->createPeriod();
    }

    private function createUser(): void
    {
        $user = new User();
        $user->email = 'registration-test@gmail.com';
        $user->password = 'test-password';
        $user->role = UserRole::USER;

        $user = $this->userRepository->save($user);

        $this->userId = (int) $user->id;
    }

    private function createPeriod(): void
    {
        $period = new RegistrationPeriod();
        $period->level = RegistrationPeriodLevel::LEVEL_1;
        $period->date = new DateTimeImmutable('2026-11-01');
        $period->time = '08:00';
        $period->location = 'Tuban';
        $period->batch = 1;
        $period->type = RegistrationPeriodType::SHORT_DISTANCE;
        $period->class = 'Reguler';
        $period->region = 'Palang';
        $period->isActive = true;
        $period->information = null;

        $period = $this->periodRepository->save($period);

        $this->periodId = (int) $period->id;
    }

    private function createRegistration(): Registration
    {
        $registration = new Registration();

        $registration->userId = $this->userId;
        $registration->registrationPeriodId = $this->periodId;

        $registration->fullName = 'Ahmad Danish';
        $registration->birthPlace = 'Tuban';
        $registration->birthDate = '2005-01-15';

        $registration->provinceCode = '35';
        $registration->regencyCode = '35.23';
        $registration->districtCode = '35.23.18';
        $registration->villageCode = '35.23.18.2019';

        $registration->rt = '001';
        $registration->rw = '002';

        $registration->occupation = 'Mahasiswa';
        $registration->trainingLocation = 'Palang';

        $registration->fatherName = 'Bapak Danish';
        $registration->motherName = 'Ibu Danish';

        $registration->coachName = 'Pelatih Danish';
        $registration->coachAddress = 'Palang, Tuban';

        $registration->smallPhoto = null;
        $registration->largePhoto = null;

        $registration->dressSize = RegistrationDressSize::EXTRA_LARGE;

        return $registration;
    }

    public function testSaveSuccess(): void
    {
        $registration = $this->createRegistration();

        $result = $this->registrationRepository->save($registration);

        self::assertNotNull($result->id);
        self::assertGreaterThan(0, $result->id);

        $saved = $this->registrationRepository->findById($result->id);

        self::assertNotNull($saved);
        self::assertSame('Ahmad Danish', $saved->fullName);
        self::assertSame($this->userId, $saved->userId);
        self::assertSame($this->periodId, $saved->registrationPeriodId);
        self::assertSame('35.23.18.2019', $saved->villageCode);
    }

    public function testUpdateSuccess(): void
    {
        $registration = $this->registrationRepository->save(
            $this->createRegistration()
        );

        $registration->fullName = 'Ahmad Danish Updated';
        $registration->occupation = 'Backend Developer';

        $result = $this->registrationRepository->update($registration);

        self::assertTrue($result);

        $updated = $this->registrationRepository->findById(
            $registration->id
        );

        self::assertNotNull($updated);
        self::assertSame('Ahmad Danish Updated', $updated->fullName);
        self::assertSame('Backend Developer', $updated->occupation);
    }

    public function testUpdateWithoutIdReturnsFalse(): void
    {
        $registration = $this->createRegistration();

        self::assertNull($registration->id);
        self::assertFalse(
            $this->registrationRepository->update($registration)
        );
    }

    public function testFindByIdSuccess(): void
    {
        $registration = $this->registrationRepository->save(
            $this->createRegistration()
        );

        $result = $this->registrationRepository->findById(
            $registration->id
        );

        self::assertNotNull($result);
        self::assertSame($registration->id, $result->id);
        self::assertSame('Ahmad Danish', $result->fullName);
        self::assertSame('Tuban', $result->birthPlace);
        self::assertSame('35', $result->provinceCode);
        self::assertSame('35.23', $result->regencyCode);
        self::assertSame('35.23.18', $result->districtCode);
        self::assertSame('35.23.18.2019', $result->villageCode);
        self::assertNull($result->smallPhoto);
        self::assertNull($result->largePhoto);
    }

    public function testFindByIdNotFoundReturnsNull(): void
    {
        $result = $this->registrationRepository->findById(999999999);

        self::assertNull($result);
    }

    public function testDeleteAllSuccess(): void
    {
        $registration = $this->registrationRepository->save(
            $this->createRegistration()
        );

        self::assertNotNull(
            $this->registrationRepository->findById($registration->id)
        );

        $this->registrationRepository->deleteAll();

        self::assertNull(
            $this->registrationRepository->findById($registration->id)
        );
    }
}
