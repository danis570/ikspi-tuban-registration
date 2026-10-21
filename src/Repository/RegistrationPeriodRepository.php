<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodType;
use DateTimeImmutable;
use PDO;

class RegistrationPeriodRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(RegistrationPeriod $registrationPeriod): RegistrationPeriod
    {
        $stmt = $this->pdo->prepare("INSERT INTO registration_periods (
        level, date, time, location, batch, type, class, region, is_active, information)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $registrationPeriod->level->value,
            $registrationPeriod->date->format('Y-m-d'),
            $registrationPeriod->time,
            $registrationPeriod->location,
            $registrationPeriod->batch,
            $registrationPeriod->type->value,
            $registrationPeriod->class,
            $registrationPeriod->region,
            $registrationPeriod->isActive,
            $registrationPeriod->information
        ]);

        $registrationPeriod->id = $this->pdo->lastInsertId();
        return $registrationPeriod;
    }

    public function updateById(int $id, RegistrationPeriod $registrationPeriod): int
    {
        $stmt = $this->pdo->prepare("
            UPDATE registration_periods
            SET level = ?,
                date = ?,
                time = ?,
                location = ?,
                batch = ?,
                type = ?,
                class = ?,
                region = ?,
                is_active = ?,
                information = ?
            WHERE id = ?
        ");
        $result = $stmt->execute([
            $registrationPeriod->level->value,
            $registrationPeriod->date->format('Y-m-d'),
            $registrationPeriod->time,
            $registrationPeriod->location,
            $registrationPeriod->batch,
            $registrationPeriod->type->value,
            $registrationPeriod->class,
            $registrationPeriod->region,
            $registrationPeriod->isActive,
            $registrationPeriod->information,
            $id
        ]);

        if ($result) {
            return $stmt->rowCount();
        }

        return $result;
    }

    public function findByBatch(int $batch): ?RegistrationPeriod
    {
        $stmt = $this->pdo->prepare("SELECT * FROM registration_periods WHERE batch = ?");
        $stmt->execute([$batch]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row == false) {
            return null;
        }

        return $this->mapToRegistrationPeriod($row);

    }

    public function findByLevelAndBatch(RegistrationPeriodLevel $level, int $batch): ?RegistrationPeriod
    {
        $stmt = $this->pdo->prepare("SELECT * FROM registration_periods
        WHERE level = ? AND batch = ?");
        $stmt->execute([$level->value, $batch]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return $this->mapToRegistrationPeriod($row);
    }

    public function findById(int $id): ?RegistrationPeriod
    {
        $stmt = $this->pdo->prepare("SELECT * FROM registration_periods
        WHERE id = ?");
        $stmt->execute([$id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return $this->mapToRegistrationPeriod($row);
    }

    public function findAll(): ?array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM registration_periods"
        );

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $datas = [];

        foreach ($rows as $row) {
            $datas[] = $this->mapToRegistrationPeriod($row);
        }

        return $datas;
    }

    public function deleteById(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM registration_periods
        WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function findActiveByLevel(
        RegistrationPeriodLevel $level
    ): ?RegistrationPeriod {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM registration_periods
        WHERE level = ? AND is_active = 1
        LIMIT 1"
        );

        $stmt->execute([$level->value]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->mapToRegistrationPeriod($row);
    }

    public function findAllActive(): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM registration_periods
        WHERE is_active = 1
        ORDER BY date ASC"
        );

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $periods = [];

        foreach ($rows as $row) {
            $periods[] = $this->mapToRegistrationPeriod($row);
        }

        return $periods;
    }

    // HELPER

    private function mapToRegistrationPeriod(array $row): RegistrationPeriod
    {
        $registrationPeriod = new RegistrationPeriod();
        $registrationPeriod->id = $row['id'];
        $registrationPeriod->level = RegistrationPeriodLevel::from($row['level']);
        $registrationPeriod->date = new DateTimeImmutable($row['date']);
        $registrationPeriod->time = $row['time'];
        $registrationPeriod->location = $row['location'];
        $registrationPeriod->batch = $row['batch'];
        $registrationPeriod->type = RegistrationPeriodType::from($row['type']);
        $registrationPeriod->class = $row['class'];
        $registrationPeriod->region = $row['region'];
        $registrationPeriod->isActive = $row['is_active'];
        $registrationPeriod->information = $row['information'];

        return $registrationPeriod;
    }
    // FOR UNIT TEST;

    public function deleteAll(): void
    {
        $this->pdo->exec("DELETE FROM registration_periods");
    }
}
