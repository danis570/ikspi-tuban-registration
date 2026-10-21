<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodType;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\RegistrationPeriod\AdminSaveRegistrationPeriodRequest;
use Danish\IkspiTubanRegistration\Model\RegistrationPeriod\AdminSaveRegistrationPeriodResponse;
use Danish\IkspiTubanRegistration\Repository\RegistrationPeriodRepository;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class RegistrationPeriodServiceTest extends TestCase
{
    private RegistrationPeriodRepository $registrationPeriodRepository;

    private RegistrationPeriodService $registrationPeriodService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->registrationPeriodRepository =
            new RegistrationPeriodRepository(Database::getConnection());

        $this->registrationPeriodService =
            new RegistrationPeriodService(
                $this->registrationPeriodRepository
            );

        $this->registrationPeriodRepository->deleteAll();
    }

    private function createRequest(
        ?int $id = null,
        RegistrationPeriodLevel $level = RegistrationPeriodLevel::LEVEL_1,
        ?int $batch = 1,
        string $class = 'Reguler',
        bool $isActive = true,
    ): AdminSaveRegistrationPeriodRequest {
        $request = new AdminSaveRegistrationPeriodRequest();

        $request->id = $id;
        $request->level = $level->value;
        $request->date = '2026-11-01';
        $request->time = '08:00';
        $request->location = 'Tuban';
        $request->batch = $batch;
        $request->type = RegistrationPeriodType::SHORT_DISTANCE->value;
        $request->class = $class;
        $request->region = 'Palang';
        $request->isActive = $isActive;
        $request->information = null;

        return $request;
    }

    private function createPeriod(
        RegistrationPeriodLevel $level = RegistrationPeriodLevel::LEVEL_1,
        int $batch = 1,
        string $class = 'Reguler',
        bool $isActive = true,
    ): RegistrationPeriod {
        $period = new RegistrationPeriod();

        $period->level = $level;
        $period->date = new \DateTimeImmutable('2026-11-01');
        $period->time = '08:00';
        $period->location = 'Tuban';
        $period->batch = $batch;
        $period->type = RegistrationPeriodType::SHORT_DISTANCE;
        $period->class = $class;
        $period->region = 'Palang';
        $period->isActive = $isActive;
        $period->information = null;

        return $this->registrationPeriodRepository->save($period);
    }

    public function testSaveInCreate(): void
    {
        $request = $this->createRequest();

        $result = $this->registrationPeriodService->save($request);

        self::assertInstanceOf(
            AdminSaveRegistrationPeriodResponse::class,
            $result
        );

        self::assertNotNull($result->registrationPeriod);

        self::assertSame(
            RegistrationPeriodLevel::LEVEL_1,
            $result->registrationPeriod->level
        );

        self::assertSame(1, $result->registrationPeriod->batch);

        $saved = $this->registrationPeriodRepository->findById(
            $result->registrationPeriod->id
        );

        self::assertNotNull($saved);
        self::assertSame('Reguler', $saved->class);
        self::assertTrue($saved->isActive);
    }

    public function testSaveInCreateWithInactiveStatus(): void
    {
        $request = $this->createRequest(
            isActive: false
        );

        $result = $this->registrationPeriodService->save($request);

        self::assertInstanceOf(
            AdminSaveRegistrationPeriodResponse::class,
            $result
        );

        $saved = $this->registrationPeriodRepository->findById(
            $result->registrationPeriod->id
        );

        self::assertNotNull($saved);
        self::assertFalse($saved->isActive);
    }

    public function testSaveInCreateFailsWhenLevelAndBatchAlreadyExist(): void
    {
        $this->createPeriod(
            RegistrationPeriodLevel::LEVEL_1,
            1
        );

        $request = $this->createRequest(
            batch: 1
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Tingkat dan angkatan ini sudah ada'
        );

        $this->registrationPeriodService->save($request);
    }

    public function testSaveInUpdate(): void
    {
        $period = $this->createPeriod(
            RegistrationPeriodLevel::LEVEL_1,
            1,
            'Reguler'
        );

        $request = $this->createRequest(
            id: $period->id,
            class: 'Khusus'
        );

        $result = $this->registrationPeriodService->save($request);

        self::assertTrue($result);

        $updated = $this->registrationPeriodRepository->findById(
            $period->id
        );

        self::assertNotNull($updated);
        self::assertSame('Khusus', $updated->class);
        self::assertSame($period->id, $updated->id);
    }

    public function testSaveInUpdateWithInactiveStatus(): void
    {
        $period = $this->createPeriod();

        $request = $this->createRequest(
            id: $period->id,
            isActive: false
        );

        $result = $this->registrationPeriodService->save($request);

        self::assertTrue($result);

        $updated = $this->registrationPeriodRepository->findById(
            $period->id
        );

        self::assertNotNull($updated);
        self::assertFalse($updated->isActive);
    }

    public function testSaveInUpdateFailsWhenLevelAndBatchBelongToAnotherPeriod(): void
    {
        $periodA = $this->createPeriod(
            RegistrationPeriodLevel::LEVEL_1,
            1
        );

        $this->createPeriod(
            RegistrationPeriodLevel::LEVEL_2,
            2
        );

        $request = $this->createRequest(
            id: $periodA->id,
            level: RegistrationPeriodLevel::LEVEL_2,
            batch: 2
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Tingkat dan angkatan ini sudah ada'
        );

        $this->registrationPeriodService->save($request);
    }

    public function testSaveInUpdateFailsWhenIdDoesNotExist(): void
    {
        $request = $this->createRequest(
            id: 999999
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Periode registrasi belum ada'
        );

        $this->registrationPeriodService->save($request);
    }

    public function testSaveFailsWhenLocationIsEmpty(): void
    {
        $request = $this->createRequest();
        $request->location = '';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Semua kolom wajib di isi kecuali informasi'
        );

        $this->registrationPeriodService->save($request);
    }

    public function testSaveFailsWhenLevelIsEmpty(): void
    {
        $request = $this->createRequest();
        $request->level = '';

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Semua kolom wajib di isi kecuali informasi'
        );

        $this->registrationPeriodService->save($request);
    }

    public function testSaveFailsWhenBatchIsNull(): void
    {
        $request = $this->createRequest(
            batch: null
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Semua kolom wajib di isi kecuali informasi'
        );

        $this->registrationPeriodService->save($request);
    }

    public function testSaveFailsWhenBatchIsZero(): void
    {
        $request = $this->createRequest(
            batch: 0
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Semua kolom wajib di isi kecuali informasi'
        );

        $this->registrationPeriodService->save($request);
    }

    public function testSaveFailsWhenBatchIsNegative(): void
    {
        $request = $this->createRequest(
            batch: -1
        );

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Semua kolom wajib di isi kecuali informasi'
        );

        $this->registrationPeriodService->save($request);
    }

    public function testSaveFailsWhenIsActiveIsNull(): void
    {
        $request = $this->createRequest();
        $request->isActive = null;

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIs(
            'Perhatian: Semua kolom wajib di isi kecuali informasi'
        );

        $this->registrationPeriodService->save($request);
    }
}