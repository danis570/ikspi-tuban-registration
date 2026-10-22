<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Domain\AdministrativeArea;
use PDO;

class AdministrativeAreaRepository
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function findDistrictByName(string $name): ?AdministrativeArea
    {
        $sql = "
        SELECT code, name
        FROM administrative_areas
        WHERE code REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}$'
          AND LOWER(name) = LOWER(:name)
        LIMIT 1
    ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'name' => trim($name),
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToEntity($row) : null;
    }
    public function findDistrictHierarchy(string $districtCode): ?array
    {
        $sql = "
        SELECT
            province.code AS province_code,
            regency.code AS regency_code,
            district.code AS district_code
        FROM administrative_areas AS district
        JOIN administrative_areas AS regency
            ON district.code LIKE CONCAT(regency.code, '.%')
           AND regency.code REGEXP '^[0-9]{2}\\.[0-9]{2}$'
        JOIN administrative_areas AS province
            ON regency.code LIKE CONCAT(province.code, '.%')
           AND province.code NOT LIKE '%.%'
        WHERE district.code = :district_code
          AND district.code REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}$'
        LIMIT 1
    ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'district_code' => $districtCode,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function findByCode(string $code): ?AdministrativeArea
    {
        $sql = "
        SELECT code, name
        FROM administrative_areas
        WHERE code = :code
        LIMIT 1
    ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'code' => $code,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToEntity($row) : null;
    }

    public function findDistrictMentionedInEmail(
        string $email
    ): ?AdministrativeArea {
        $sql = "
        SELECT code, name
        FROM administrative_areas
        WHERE code REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}$'
          AND LOCATE(
              LOWER(name),
              LOWER(SUBSTRING_INDEX(:email, '@', 1))
          ) > 0
        ORDER BY LENGTH(name) DESC
        LIMIT 1
    ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'email' => $email,
        ]);

        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToEntity($row) : null;
    }

    public function existsByCode(string $code): bool
    {
        $sql = "
        SELECT 1
        FROM administrative_areas
        WHERE code = :code
        LIMIT 1
    ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'code' => $code,
        ]);

        return $statement->fetchColumn() !== false;
    }

    public function findProvinces(): array
    {
        $sql = "
        SELECT code, name
        FROM administrative_areas
        WHERE code NOT LIKE '%.%'
        ORDER BY name
        ";

        $statement = $this->connection->query($sql);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $areas = [];

        foreach ($rows as $row) {
            $areas[] = $this->mapToEntity($row);
        }

        return $areas;
    }


    public function findRegencies(string $provinceCode): array
    {
        $sql = "
        SELECT code, name
        FROM administrative_areas
        WHERE code REGEXP '^[0-9]{2}\\.[0-9]{2}$'
          AND code LIKE :parent_code
        ORDER BY name
        ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'parent_code' => $provinceCode . '.%'
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $this->mapToEntities($rows);
    }

    public function findDistricts(string $regencyCode): array
    {
        $sql = "
        SELECT code, name
        FROM administrative_areas
        WHERE code REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}$'
          AND code LIKE :parent_code
        ORDER BY name
    ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'parent_code' => $regencyCode . '.%'
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $this->mapToEntities($rows);
    }

    public function findVillages(string $districtCode): array
    {
        $sql = "
        SELECT code, name
        FROM administrative_areas
        WHERE code REGEXP '^[0-9]{2}\\.[0-9]{2}\\.[0-9]{2}\\.[0-9]{4}$'
          AND code LIKE :parent_code
        ORDER BY name
    ";

        $statement = $this->connection->prepare($sql);
        $statement->execute([
            'parent_code' => $districtCode . '.%'
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $this->mapToEntities($rows);
    }

    // HELPERS

    private function mapToEntity(array $row): AdministrativeArea
    {
        $area = new AdministrativeArea();
        $area->code = $row['code'];
        $area->name = $row['name'];

        return $area;
    }

    private function mapToEntities(array $rows): array
    {
        $areas = [];

        foreach ($rows as $row) {
            $areas[] = $this->mapToEntity($row);
        }

        return $areas;
    }

}