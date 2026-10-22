<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\Registration\RegistrationSaveRequest;
use Danish\IkspiTubanRegistration\Repository\AdministrativeAreaRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationRepository;
use RuntimeException;
use Throwable;

class RegistrationService
{
    private RegistrationRepository $registrationRepository;
    private UploadRegistrationPhotoService $photoService;
    private AdministrativeAreaRepository $administrativeAreaRepository;

    public function __construct(
        RegistrationRepository $registrationRepository,
        UploadRegistrationPhotoService $photoService,
        AdministrativeAreaRepository $administrativeAreaRepository
    ) {
        $this->registrationRepository = $registrationRepository;
        $this->photoService = $photoService;
        $this->administrativeAreaRepository = $administrativeAreaRepository;
    }


    public function save(
        RegistrationSaveRequest $request
    ): Registration {
        if ($request->id === null) {
            $this->validate($request, true);

            return $this->create($request);
        }

        $current = $this->registrationRepository
            ->findById($request->id);

        if ($current === null) {
            throw new ValidationException(
                'Data pendaftaran tidak ditemukan.'
            );
        }

        $this->validate($request, false);

        return $this->update($request, $current);
    }


    public function create(
        RegistrationSaveRequest $request
    ): Registration {
        $savedPhotos = [];

        try {
            $smallPhoto = $this->photoService->save(
                $request->smallPhoto,
                'small'
            );

            $savedPhotos[] = $smallPhoto;

            $largePhoto = $this->photoService->save(
                $request->largePhoto,
                'large'
            );

            $savedPhotos[] = $largePhoto;

            $registration = $this->buildRegistration(
                $request,
                $smallPhoto,
                $largePhoto
            );

            return $this->registrationRepository->save(
                $registration
            );
        } catch (Throwable $exception) {
            $this->deletePhotos($savedPhotos);

            throw $exception;
        }
    }


    public function update(
        RegistrationSaveRequest $request,
        Registration $current
    ): Registration {
        $savedPhotos = [];

        try {
            // Gunakan foto lama jika tidak ada foto baru.
            $smallPhoto = $current->smallPhoto;
            $largePhoto = $current->largePhoto;

            if ($request->smallPhoto !== null) {
                $smallPhoto = $this->photoService->save(
                    $request->smallPhoto,
                    'small'
                );

                $savedPhotos[] = $smallPhoto;
            }

            if ($request->largePhoto !== null) {
                $largePhoto = $this->photoService->save(
                    $request->largePhoto,
                    'large'
                );

                $savedPhotos[] = $largePhoto;
            }

            $registration = $this->buildRegistration(
                $request,
                $smallPhoto,
                $largePhoto
            );

            $registration->id = $current->id;

            $updated = $this->registrationRepository->update(
                $registration
            );

            if (!$updated) {
                throw new RuntimeException(
                    'Tidak ada data yang diperbarui.'
                );
            }
        } catch (Throwable $exception) {
            // Jika gagal, hapus hanya foto baru yang sudah disimpan.
            $this->deletePhotos($savedPhotos);

            throw $exception;
        }

        // Hapus foto lama hanya jika benar-benar diganti.
        $oldPhotos = [];

        if ($request->smallPhoto !== null) {
            $oldPhotos[] = $current->smallPhoto;
        }

        if ($request->largePhoto !== null) {
            $oldPhotos[] = $current->largePhoto;
        }

        $this->deletePhotos($oldPhotos);

        return $registration;
    }


    public function delete(int $id, int $userId): void
    {
        $registration = $this->registrationRepository
            ->findById($id);

        if (
            $registration === null ||
            $registration->userId !== $userId
        ) {
            throw new ValidationException(
                'Data pendaftaran tidak ditemukan.'
            );
        }

        $this->deleteRegistration($registration);
    }

    public function deleteForAdmin(int $id): void
    {
        $registration = $this->registrationRepository
            ->findById($id);

        if ($registration === null) {
            throw new ValidationException(
                'Data pendaftaran tidak ditemukan.'
            );
        }

        $this->deleteRegistration($registration);
    }

    private function deleteRegistration(
        Registration $registration
    ): void {
        $deleted = $this->registrationRepository
            ->deleteById($registration->id);

        if (!$deleted) {
            throw new RuntimeException(
                'Gagal menghapus data pendaftaran.'
            );
        }

        $this->deletePhotos([
            $registration->smallPhoto,
            $registration->largePhoto,
        ]);
    }


    private function validate(
        RegistrationSaveRequest $request,
        bool $isCreate
    ): void {
        if (
            trim($request->fullName) === '' ||
            trim($request->birthPlace) === '' ||
            trim($request->birthDate) === '' ||
            trim($request->provinceCode) === '' ||
            trim($request->regencyCode) === '' ||
            trim($request->districtCode) === '' ||
            trim($request->villageCode) === '' ||
            trim($request->rt) === '' ||
            trim($request->rw) === '' ||
            trim($request->occupation) === '' ||
            trim($request->trainingLocation) === '' ||
            trim($request->fatherName) === '' ||
            trim($request->motherName) === '' ||
            trim($request->coachName) === '' ||
            trim($request->coachAddress) === ''
        ) {
            throw new ValidationException(
                'Semua kolom wajib diisi.'
            );
        }

        if (
            !$this->administrativeAreaRepository
                ->existsByCode($request->provinceCode)
        ) {
            throw new ValidationException(
                'Provinsi yang dipilih tidak valid.'
            );
        }

        if (
            !$this->administrativeAreaRepository
                ->existsByCode($request->regencyCode)
        ) {
            throw new ValidationException(
                'Kabupaten/kota yang dipilih tidak valid.'
            );
        }

        if (
            !$this->administrativeAreaRepository
                ->existsByCode($request->districtCode)
        ) {
            throw new ValidationException(
                'Kecamatan yang dipilih tidak valid.'
            );
        }

        if (
            !$this->administrativeAreaRepository
                ->existsByCode($request->villageCode)
        ) {
            throw new ValidationException(
                'Desa/kelurahan yang dipilih tidak valid.'
            );
        }

        if (
            $isCreate &&
            (
                !$request->smallPhoto instanceof UploadedFile ||
                !$request->largePhoto instanceof UploadedFile
            )
        ) {
            throw new ValidationException(
                'Foto kecil dan foto besar wajib diunggah.'
            );
        }
    }

    private function buildRegistration(
        RegistrationSaveRequest $request,
        string $smallPhoto,
        string $largePhoto
    ): Registration {
        $registration = new Registration();

        $registration->userId = $request->userId;
        $registration->registrationPeriodId =
            $request->registrationPeriodId;
        $registration->fullName = trim($request->fullName);
        $registration->birthPlace = trim($request->birthPlace);
        $registration->birthDate = $request->birthDate;

        $registration->provinceCode = $request->provinceCode;
        $registration->regencyCode = $request->regencyCode;
        $registration->districtCode = $request->districtCode;
        $registration->villageCode = $request->villageCode;

        $registration->rt = $request->rt;
        $registration->rw = $request->rw;
        $registration->occupation = $request->occupation;
        $registration->trainingLocation = $request->trainingLocation;

        $registration->fatherName = $request->fatherName;
        $registration->motherName = $request->motherName;
        $registration->coachName = $request->coachName;
        $registration->coachAddress = $request->coachAddress;

        $registration->smallPhoto = $smallPhoto;
        $registration->largePhoto = $largePhoto;

        return $registration;
    }

    private function deletePhotos(array $fileNames): void
    {
        $directory = realpath(
            __DIR__ . '/../../public/uploads/registrations/'
        );

        if ($directory === false) {
            return;
        }

        foreach (array_unique($fileNames) as $fileName) {
            if (
                !is_string($fileName) ||
                $fileName === '' ||
                basename($fileName) !== $fileName
            ) {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $fileName;

            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
