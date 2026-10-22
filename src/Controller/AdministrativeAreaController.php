<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Repository\AdministrativeAreaRepository;

class AdministrativeAreaController
{
    private AdministrativeAreaRepository $administrativeAreaRepository;
    public function __construct()
    {
        $this->administrativeAreaRepository = new AdministrativeAreaRepository(Database::getConnection());
    }

    public function provinces(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $areas = $this->administrativeAreaRepository->findProvinces();

        echo json_encode(
            array_map(
                fn($area) => [
                    'code' => $area->code,
                    'name' => $area->name,
                ],
                $areas
            ),
            JSON_UNESCAPED_UNICODE
        );
    }

    public function regencies(): void
    {
        $provinceCode = $_GET['province_code'] ?? '';

        $this->respond(
            $provinceCode,
            fn(string $code): array =>
                $this->administrativeAreaRepository->findRegencies($code)
        );
    }

    public function districts(): void
    {
        $regencyCode = $_GET['regency_code'] ?? '';

        $this->respond(
            $regencyCode,
            fn(string $code): array =>
                $this->administrativeAreaRepository->findDistricts($code)
        );
    }

    public function villages(): void
    {
        $districtCode = $_GET['district_code'] ?? '';

        $this->respond(
            $districtCode,
            fn(string $code): array =>
                $this->administrativeAreaRepository->findVillages($code)
        );
    }

    private function respond(
        string $parentCode,
        callable $finder
    ): void {
        header('Content-Type: application/json; charset=utf-8');

        if (trim($parentCode) === '') {
            http_response_code(400);

            echo json_encode([
                'message' => 'Kode wilayah wajib diisi.',
            ]);

            return;
        }

        $areas = $finder($parentCode);

        echo json_encode(
            array_map(
                fn($area) => [
                    'code' => $area->code,
                    'name' => $area->name,
                ],
                $areas
            ),
            JSON_UNESCAPED_UNICODE
        );
    }
}