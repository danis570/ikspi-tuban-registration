<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodType;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\RegistrationPeriod\AdminSaveRegistrationPeriodRequest;
use Danish\IkspiTubanRegistration\Model\RegistrationPeriod\AdminSaveRegistrationPeriodResponse;
use Danish\IkspiTubanRegistration\Repository\RegistrationPeriodRepository;
use DateTimeImmutable;

class RegistrationPeriodService
{
    private RegistrationPeriodRepository $registrationPeriodRepository;
    public function __construct(RegistrationPeriodRepository $registrationPeriodRepository)
    {
        $this->registrationPeriodRepository = $registrationPeriodRepository;
    }

    function save(
        AdminSaveRegistrationPeriodRequest $request
    ): AdminSaveRegistrationPeriodResponse|bool {

        // Validasi input
        if (
            trim($request->level ?? '') === '' ||
            trim($request->date ?? '') === '' ||
            trim($request->type ?? '') === '' ||
            $request->isActive === null ||
            $request->batch <= 0 ||
            trim($request->class ?? '') === '' ||
            trim($request->region ?? '') === '' ||
            trim($request->time ?? '') === '' ||
            trim($request->location ?? '') === ''
        ) {
            throw new ValidationException(
                'Semua kolom wajib di isi kecuali informasi'
            );
        }

        $registrationPeriod = new RegistrationPeriod();
        $registrationPeriod->id = $request->id;
        $registrationPeriod->level = RegistrationPeriodLevel::from($request->level);
        $registrationPeriod->date = new DateTimeImmutable($request->date);
        $registrationPeriod->time = $request->time;
        $registrationPeriod->location = $request->location;
        $registrationPeriod->batch = $request->batch;
        $registrationPeriod->type = RegistrationPeriodType::from($request->type);
        $registrationPeriod->class = $request->class;
        $registrationPeriod->region = $request->region;
        $registrationPeriod->isActive = (bool) $request->isActive;
        $registrationPeriod->information = $request->information;

        $current = null;
        if ($request->id !== null) {
            $current = $this->registrationPeriodRepository
                ->findById($request->id);

            if ($current === null) {
                throw new ValidationException(
                    'Periode registrasi belum ada'
                );
            }
        }

        $existing = $this->registrationPeriodRepository
            ->findByLevelAndBatch(
                $registrationPeriod->level,
                $registrationPeriod->batch
            );

        if (
            $existing !== null &&
            $existing->id !== $request->id
        ) {
            throw new ValidationException(
                'Tingkat dan angkatan ini sudah ada'
            );
        }

        $activePeriod = $this->registrationPeriodRepository
            ->findActiveByLevel($registrationPeriod->level);

        if (
            $registrationPeriod->isActive &&
            $activePeriod !== null &&
            $activePeriod->id !== $request->id
        ) {
            throw new ValidationException(
                'Tingkat ini sudah aktif di data lain, harap nonaktifkan dulu salah satu'
            );
        }

        // Create
        if ($request->id === null) {
            $result = $this->registrationPeriodRepository
                ->save($registrationPeriod);

            $response = new AdminSaveRegistrationPeriodResponse();
            $response->registrationPeriod = $result;

            return $response;
        }

        if (
            $current->level === $registrationPeriod->level &&
            $current->date->format('Y-m-d') === $registrationPeriod->date->format('Y-m-d') &&
            $current->time === $registrationPeriod->time &&
            $current->location === $registrationPeriod->location &&
            $current->batch === $registrationPeriod->batch &&
            $current->type === $registrationPeriod->type &&
            $current->class === $registrationPeriod->class &&
            $current->region === $registrationPeriod->region &&
            $current->isActive === $registrationPeriod->isActive &&
            $current->information === $registrationPeriod->information
        ) {
            throw new ValidationException(
                'Tidak ada data yang diubah'
            );
        }

        // Update
        return $this->registrationPeriodRepository->updateById(
            $request->id,
            $registrationPeriod
        ) > 0;
    }

    function getAll(): array
    {
        return $this->registrationPeriodRepository->findAll();
    }

    public function findAllActive(): array
    {
        return $this->registrationPeriodRepository->findAllActive();
    }
}
