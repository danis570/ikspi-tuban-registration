<?php

namespace Danish\IkspiTubanRegistration\Repository;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\AdministrativeArea;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class AdministrativeAreaRepositoryTest extends TestCase
{
    private AdministrativeAreaRepository $repository;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new AdministrativeAreaRepository(
            Database::getConnection()
        );
    }

    public function testFindProvincesSuccess(): void
    {
        $provinces = $this->repository->findProvinces();

        self::assertNotEmpty($provinces);

        $province = $this->findAreaByCode($provinces, '35');

        self::assertSame('Jawa Timur', $province->name);

        foreach ($provinces as $province) {
            self::assertInstanceOf(AdministrativeArea::class, $province);
            self::assertNotSame('', $province->code);
            self::assertNotSame('', $province->name);
            self::assertStringNotContainsString('.', $province->code);
        }
    }

    public function testFindRegenciesSuccess(): void
    {
        $regencies = $this->repository->findRegencies('35');

        self::assertNotEmpty($regencies);

        $regency = $this->findAreaByCode($regencies, '35.23');

        self::assertSame('Tuban', $regency->name);

        foreach ($regencies as $regency) {
            self::assertInstanceOf(AdministrativeArea::class, $regency);

            self::assertMatchesRegularExpression(
                '/^[0-9]{2}\.[0-9]{2}$/',
                $regency->code
            );

            self::assertStringStartsWith('35.', $regency->code);
        }
    }

    public function testFindRegenciesWithUnknownProvinceReturnsEmptyArray(): void
    {
        $regencies = $this->repository->findRegencies('99');

        self::assertSame([], $regencies);
    }

    public function testFindDistrictsSuccess(): void
    {
        $districts = $this->repository->findDistricts('35.23');

        self::assertNotEmpty($districts);

        $district = $this->findAreaByCode($districts, '35.23.18');

        self::assertSame('Palang', $district->name);

        foreach ($districts as $district) {
            self::assertInstanceOf(AdministrativeArea::class, $district);

            self::assertMatchesRegularExpression(
                '/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}$/',
                $district->code
            );

            self::assertStringStartsWith('35.23.', $district->code);
        }
    }

    public function testFindDistrictsWithUnknownRegencyReturnsEmptyArray(): void
    {
        $districts = $this->repository->findDistricts('99.99');

        self::assertSame([], $districts);
    }

    public function testFindVillagesSuccess(): void
    {
        $villages = $this->repository->findVillages('35.23.18');

        self::assertNotEmpty($villages);

        $village = $this->findAreaByCode($villages, '35.23.18.2019');

        self::assertSame('Ketambul', $village->name);

        foreach ($villages as $village) {
            self::assertInstanceOf(AdministrativeArea::class, $village);

            self::assertMatchesRegularExpression(
                '/^[0-9]{2}\.[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/',
                $village->code
            );

            self::assertStringStartsWith('35.23.18.', $village->code);
        }
    }

    public function testFindVillagesWithUnknownDistrictReturnsEmptyArray(): void
    {
        $villages = $this->repository->findVillages('99.99.99');

        self::assertSame([], $villages);
    }

    /**
     * @param AdministrativeArea[] $areas
     */
    private function findAreaByCode(
        array $areas,
        string $code
    ): AdministrativeArea {
        foreach ($areas as $area) {
            if ($area->code === $code) {
                return $area;
            }
        }

        self::fail("Administrative area with code {$code} was not found.");
    }
}
