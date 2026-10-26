<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Domain\RegistrationDressSize;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use PDO;

class RegistrationRepository
{
    public function __construct(
        private PDO $connection
    ) {
    }


    public function findAllWithUserEmail(
        ?string $level = null,
        ?int $batch = null,
        ?string $search = null
    ): array {
        $sql = "
        SELECT
            registrations.*,
            users.email AS user_email,
            registration_periods.level AS period_level,
            registration_periods.batch AS period_batch
        FROM registrations
        INNER JOIN users
            ON registrations.user_id = users.id
        INNER JOIN registration_periods
            ON registrations.registration_period_id = registration_periods.id
        WHERE 1 = 1
    ";

        $params = [];

        if ($level !== null && $level !== '') {
            $sql .= " AND registration_periods.level = ?";
            $params[] = $level;
        }

        if ($batch !== null) {
            $sql .= " AND registration_periods.batch = ?";
            $params[] = $batch;
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND (
            registrations.full_name LIKE ?
            OR users.email LIKE ?
        )";

            $keyword = '%' . $search . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        $sql .= " ORDER BY registrations.id DESC";

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $datas = [];

        foreach ($rows as $row) {
            $datas[] = [
                'registration' => $this->mapToEntity($row),
                'period' => (function () use ($row): RegistrationPeriod{
                    $period = new RegistrationPeriod();
                    $period->level = RegistrationPeriodLevel::from(
                        $row['period_level']
                    );
                    $period->batch = (int) $row['period_batch'];

                    return $period;
                })(),
                'email' => $row['user_email'],
            ];
        }

        return $datas;
    }


    public function save(Registration $registration): Registration
    {
        $sql = "
            INSERT INTO registrations (
                user_id,
                registration_period_id,
                full_name,
                birth_place,
                birth_date,
                province_code,
                regency_code,
                district_code,
                village_code,
                rt,
                rw,
                specific_address,
                occupation,
                training_location,
                father_name,
                mother_name,
                coach_name,
                coach_address,
                small_photo,
                large_photo,
                dress_size
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            $registration->userId,
            $registration->registrationPeriodId,
            $registration->fullName,
            $registration->birthPlace,
            $registration->birthDate,
            $registration->provinceCode,
            $registration->regencyCode,
            $registration->districtCode,
            $registration->villageCode,
            $registration->rt,
            $registration->rw,
            $registration->specificAddress,
            $registration->occupation,
            $registration->trainingLocation,
            $registration->fatherName,
            $registration->motherName,
            $registration->coachName,
            $registration->coachAddress,
            $registration->smallPhoto,
            $registration->largePhoto,
            $registration->dressSize->value
        ]);

        $registration->id = (int) $this->connection->lastInsertId();

        return $registration;
    }

    public function update(Registration $registration): bool
    {
        if ($registration->id === null) {
            return false;
        }

        $sql = "
            UPDATE registrations SET
                user_id = ?,
                registration_period_id = ?,
                full_name = ?,
                birth_place = ?,
                birth_date = ?,
                province_code = ?,
                regency_code = ?,
                district_code = ?,
                village_code = ?,
                rt = ?,
                rw = ?,
                specific_address = ?,
                occupation = ?,
                training_location = ?,
                father_name = ?,
                mother_name = ?,
                coach_name = ?,
                coach_address = ?,
                small_photo = ?,
                large_photo = ?,
                dress_size = ?
            WHERE id = ?
        ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            $registration->userId,
            $registration->registrationPeriodId,
            $registration->fullName,
            $registration->birthPlace,
            $registration->birthDate,
            $registration->provinceCode,
            $registration->regencyCode,
            $registration->districtCode,
            $registration->villageCode,
            $registration->rt,
            $registration->rw,
            $registration->specificAddress,
            $registration->occupation,
            $registration->trainingLocation,
            $registration->fatherName,
            $registration->motherName,
            $registration->coachName,
            $registration->coachAddress,
            $registration->smallPhoto,
            $registration->largePhoto,
            $registration->dressSize->value,
            $registration->id
        ]);

        return $statement->rowCount() > 0;
    }

    public function findById(int $id): ?Registration
    {
        $statement = $this->connection->prepare("
            SELECT *
            FROM registrations
            WHERE id = ?
        ");

        $statement->execute([$id]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->mapToEntity($row);
    }


    public function findByUserId(
        int $userId,
        ?string $level = null,
        ?int $batch = null
    ): array {
        $sql = "
        SELECT
            registrations.*,
            registration_periods.level AS period_level,
            registration_periods.batch AS period_batch
        FROM registrations
        JOIN registration_periods
            ON registrations.registration_period_id = registration_periods.id
        WHERE registrations.user_id = ?
    ";

        $params = [$userId];

        if ($level !== null && $level !== '') {
            $sql .= " AND registration_periods.level = ?";
            $params[] = $level;
        }

        if ($batch !== null) {
            $sql .= " AND registration_periods.batch = ?";
            $params[] = $batch;
        }

        $sql .= " ORDER BY registrations.id DESC";

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $datas = [];

        foreach ($rows as $row) {
            $registration = $this->mapToEntity($row);

            $period = new RegistrationPeriod();
            $period->level = RegistrationPeriodLevel::from(
                $row['period_level']
            );
            $period->batch = $row['period_batch'];

            $datas[] = [
                'registration' => $registration,
                'period' => $period,
            ];
        }

        return $datas;
    }


    public function deleteById(int $id): bool
    {
        $statement = $this->connection->prepare("
        DELETE FROM registrations
        WHERE id = ?
    ");

        $statement->execute([$id]);

        return $statement->rowCount() > 0;
    }

    public function deleteAll(): void
    {
        $this->connection->exec("DELETE FROM registrations");
    }

    // HELPERS

    private function mapToEntity(array $row): Registration
    {
        $registration = new Registration();

        $registration->id = (int) $row['id'];
        $registration->userId = (int) $row['user_id'];
        $registration->registrationPeriodId =
            (int) $row['registration_period_id'];

        $registration->fullName = $row['full_name'];
        $registration->birthPlace = $row['birth_place'];
        $registration->birthDate = $row['birth_date'];

        $registration->provinceCode = $row['province_code'];
        $registration->regencyCode = $row['regency_code'];
        $registration->districtCode = $row['district_code'];
        $registration->villageCode = $row['village_code'];

        $registration->rt = $row['rt'];
        $registration->rw = $row['rw'];
        $registration->specificAddress = $row['specific_address'];

        $registration->occupation = $row['occupation'];
        $registration->trainingLocation = $row['training_location'];

        $registration->fatherName = $row['father_name'];
        $registration->motherName = $row['mother_name'];

        $registration->coachName = $row['coach_name'];
        $registration->coachAddress = $row['coach_address'];

        $registration->smallPhoto = $row['small_photo'];
        $registration->largePhoto = $row['large_photo'];

        $registration->dressSize = RegistrationDressSize::from($row['dress_size']);

        return $registration;
    }
}
