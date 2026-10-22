<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodType;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationPeriodRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class RegistrationControllerTest extends TestCase
{
    private RegistrationController $registrationController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    private RegistrationRepository $registrationRepository;
    private RegistrationPeriodRepository $periodRepository;
    private SessionService $sessionService;

    private array $filesToClean = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        putenv('APP_ENV=test');

        $_POST = [];
        $_FILES = [];
        $_COOKIE = [];

        $connection = Database::getConnection();

        $this->userRepository = new UserRepository($connection);
        $this->sessionRepository = new SessionRepository($connection);
        $this->registrationRepository =
            new RegistrationRepository($connection);
        $this->periodRepository =
            new RegistrationPeriodRepository($connection);

        $profileRepository = new ProfileRepository($connection);

        $this->sessionService = new SessionService(
            $this->sessionRepository,
            $this->userRepository
        );

        // Bersihkan data pengujian.
        $this->registrationRepository->deleteAll();
        $this->periodRepository->deleteAll();
        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();

        $this->registrationController = new RegistrationController();
        $this->filesToClean = [];
    }

    private function loginAsUser(): User
    {
        $user = new User();
        $user->email = 'registration_test@gmail.com';
        $user->password = 'secret';
        $user->role = UserRole::USER;

        $savedUser = $this->userRepository->save($user);

        $session = $this->sessionService->create($savedUser->id);

        $_COOKIE[SessionService::$cookieName] = $session->id;

        return $savedUser;
    }

    private function createPeriod(
        RegistrationPeriodLevel $level,
        bool $isActive = true
    ): RegistrationPeriod {
        $period = new RegistrationPeriod();
        $period->level = $level;
        $period->date = new \DateTimeImmutable('2026-10-21');
        $period->time = '08:00';
        $period->location = 'Tuban';
        $period->batch = 1;
        $period->type = RegistrationPeriodType::SHORT_DISTANCE;
        $period->class = 'Reguler';
        $period->region = 'Tuban';
        $period->isActive = $isActive;
        $period->information = 'Test pendaftaran';

        return $this->periodRepository->save($period);
    }

    public function testSaveLevel1RedirectsGuestToLogin(): void
    {
        $output = $this->registrationController->saveLevel1();

        self::assertSame('', $output);
    }

    public function testSaveLevel2RedirectsGuestToLogin(): void
    {
        $output = $this->registrationController->saveLevel2();

        self::assertSame('', $output);
    }

    public function testPostSaveLevel1RedirectsGuestToLogin(): void
    {
        $output = $this->registrationController->postSaveLevel1();

        self::assertSame('', $output);
    }

    public function testPostSaveLevel2RedirectsGuestToLogin(): void
    {
        $output = $this->registrationController->postSaveLevel2();

        self::assertSame('', $output);
    }

    public function testSaveLevel1ShowsFormWhenPeriodIsActive(): void
    {
        $this->loginAsUser();

        $this->createPeriod(RegistrationPeriodLevel::LEVEL_1);

        $output = $this->registrationController->saveLevel1();

        self::assertIsString($output);
        self::assertStringContainsString(
            'Pendaftaran IKSPI',
            $output
        );
    }

    public function testSaveLevel2ShowsFormWhenPeriodIsActive(): void
    {
        $this->loginAsUser();

        $this->createPeriod(RegistrationPeriodLevel::LEVEL_2);

        $output = $this->registrationController->saveLevel2();

        self::assertIsString($output);
        self::assertStringContainsString(
            'Pendaftaran IKSPI',
            $output
        );
    }

    #[Override]
    protected function tearDown(): void
    {
        foreach ($this->filesToClean as $filePath) {
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        foreach ($_FILES as $file) {
            $tmpFile = $file['tmp_name'] ?? null;

            if (is_string($tmpFile) && is_file($tmpFile)) {
                @unlink($tmpFile);
            }
        }

        parent::tearDown();
    }
}
