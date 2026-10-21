<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodType;
use Danish\IkspiTubanRegistration\TestCase;
use DateTimeImmutable;
use Override;

use function PHPUnit\Framework\assertEquals;

class RegistrationPeriodRepositoryTest extends TestCase
{
    private RegistrationPeriodRepository $registrationPeriodRepository;
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->registrationPeriodRepository = new RegistrationPeriodRepository(Database::getConnection());
        $this->registrationPeriodRepository->deleteAll();
    }

    function testSaveSuccess()
    {
        $registration = new RegistrationPeriod();
        $registration->level = RegistrationPeriodLevel::LEVEL_1;
        $registration->date = new DateTimeImmutable('2026-10-10');
        $registration->time = "15.00 - End";
        $registration->location = 'Madiun';
        $registration->batch = 146;
        $registration->type = RegistrationPeriodType::SHORT_DISTANCE;
        $registration->class = 'Tennager';
        $registration->region = 'Jawa timur';
        $registration->isActive = true;
        $registration->information = 'tests';

        $this->registrationPeriodRepository->save($registration);
        $result = $this->registrationPeriodRepository->findByBatch($registration->batch);
        assertEquals($result->batch, $registration->batch);
        assertEquals($result->level->value, $registration->level->value);
    }

    function testFindByLevelAndBatch()
    {
        $registration = new RegistrationPeriod();
        $registration->level = RegistrationPeriodLevel::LEVEL_1;
        $registration->date = new DateTimeImmutable('2026-10-10');
        $registration->time = "15.00 - End";
        $registration->location = 'Madiun';
        $registration->batch = 146;
        $registration->type = RegistrationPeriodType::SHORT_DISTANCE;
        $registration->class = 'Tennager';
        $registration->region = 'Jawa timur';
        $registration->isActive = true;
        $registration->information = 'tests';

        $this->registrationPeriodRepository->save($registration);
        $result = $this->registrationPeriodRepository->findByLevelAndBatch($registration->level, $registration->batch);

        assertEquals($result->batch, $registration->batch);
        assertEquals($result->level->value, $registration->level->value);
    }

    public function testUpdateSuccess(): void
    {
        $registration = new RegistrationPeriod();
        $registration->level = RegistrationPeriodLevel::LEVEL_1;
        $registration->date = new DateTimeImmutable('2026-10-10');
        $registration->time = '15.00 - End';
        $registration->location = 'Madiun';
        $registration->batch = 146;
        $registration->type = RegistrationPeriodType::SHORT_DISTANCE;
        $registration->class = 'Tennager';
        $registration->region = 'Jawa timur';
        $registration->isActive = true;
        $registration->information = 'tests';

        $saved = $this->registrationPeriodRepository->save($registration);

        $new = new RegistrationPeriod();
        $new->level = RegistrationPeriodLevel::LEVEL_2;
        $new->date = new DateTimeImmutable('2027-10-10');
        $new->time = '05.00 - End';
        $new->location = 'Madiun';
        $new->batch = 148;
        $new->type = RegistrationPeriodType::SHORT_DISTANCE;
        $new->class = 'Tennager';
        $new->region = 'Jawa timur';
        $new->isActive = false;
        $new->information = 'updated information';

        $result = $this->registrationPeriodRepository
            ->updateById($saved->id, $new);

        $actual = $this->registrationPeriodRepository
            ->findByBatch($new->batch);

        self::assertNotNull($actual);
        self::assertEquals($new->batch, $actual->batch);
        self::assertSame($new->level, $actual->level);
        self::assertEquals($new->date, $actual->date);
        self::assertSame($new->time, $actual->time);
        self::assertSame($new->location, $actual->location);
        self::assertSame($new->type, $actual->type);
        self::assertSame($new->class, $actual->class);
        self::assertSame($new->region, $actual->region);
        self::assertSame($new->isActive, $actual->isActive);
        self::assertSame($new->information, $actual->information);
    }
}
