<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriod;
use Danish\IkspiTubanRegistration\Domain\RegistrationPeriodLevel;
use Danish\IkspiTubanRegistration\Exception\UploadFileException;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\Registration\RegistrationSaveRequest;
use Danish\IkspiTubanRegistration\Repository\AdministrativeAreaRepository;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationPeriodRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\RegistrationService;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Danish\IkspiTubanRegistration\Service\UploadRegistrationPhotoService;
use RuntimeException;

class AdminRegistrationController extends BaseController
{
    private RegistrationRepository $registrationRepository;
    private AdministrativeAreaRepository $administrativeAreaRepository;
    private RegistrationPeriodRepository $registrationPeriodRepository;
    private RegistrationService $registrationService;

    private SessionService $sessionService;

    public function __construct()
    {
        $connection = Database::getConnection();

        $this->registrationRepository =
            new RegistrationRepository($connection);
        $this->administrativeAreaRepository =
            new AdministrativeAreaRepository($connection);
        $this->registrationPeriodRepository =
            new RegistrationPeriodRepository($connection);
        $photoService = new UploadRegistrationPhotoService();
        $this->registrationService =
            new RegistrationService(
                $this->registrationRepository,
                $photoService,
                $this->administrativeAreaRepository
            );

        $sessionRepository = new SessionRepository($connection);
        $userRepository = new UserRepository($connection);
        $this->sessionService =
            new SessionService($sessionRepository, $userRepository);
        $profileRepository = new ProfileRepository($connection);
        parent::__construct(
            $userRepository,
            $profileRepository,
            $sessionRepository
        );
    }


    public function index(): string
    {
        $level = trim($_GET['level'] ?? '');
        $batchInput = trim($_GET['batch'] ?? '');
        $search = trim($_GET['search'] ?? '');

        if (!in_array($level, ['', 'I', 'II'], true)) {
            $level = '';
        }

        $batch = null;

        if ($batchInput !== '') {
            $validatedBatch = filter_var(
                $batchInput,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($validatedBatch !== false) {
                $batch = $validatedBatch;
            }
        }

        $registrations = $this->registrationRepository
            ->findAllWithUserEmail(
                $level !== '' ? $level : null,
                $batch,
                $search !== '' ? $search : null
            );

        return View::render(
            'Admin',
            'Registration/registration-all',
            [
                'title' => 'Data Registrasi',
                'registrations' => $registrations,
                'current' => 'registrations',
                'filterLevel' => $level,
                'filterBatch' => $batchInput,
                'filterSearch' => $search,
            ]
        );
    }

    public function edit(): string
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === false || $id === null || $id < 1) {
            View::redirect('/admin/registration/all');
            return '';
        }

        $registration = $this->registrationRepository->findById($id);

        if ($registration === null) {
            View::flashMessage('Data pendaftaran tidak ditemukan.');
            View::redirect('/admin/registration/all');
            return '';
        }

        $period = $this->registrationPeriodRepository
            ->findById($registration->registrationPeriodId);

        if ($period === null) {
            View::flashMessage('Periode pendaftaran tidak ditemukan.');
            View::redirect('/admin/registration/all');
            return '';
        }

        $initialAddress = $this->administrativeAreaRepository
            ->findDistrictHierarchy($registration->districtCode);

        if ($initialAddress !== null) {
            $initialAddress['village_code'] = $registration->villageCode;
        }

        $view = $period->level === RegistrationPeriodLevel::LEVEL_1
            ? 'Registration/save-registration-level-1'
            : 'Registration/save-registration-level-2';

        return View::render('Admin', $view, [
            'title' => 'Edit Data Registrasi',
            'current' => 'registrations',
            'registrationPeriod' => $period,
            'registration' => $registration,
            'initialAddress' => $initialAddress,
            'formAction' => '/admin/registration/edit',
        ]);
    }

    public function postEdit(): string
    {
        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($id === false || $id === null || $id < 1) {
            View::flashMessage('ID pendaftaran tidak valid.');
            View::redirect('/admin/registration/all');

            return getenv('APP_ENV') === 'test'
                ? 'success redirect'
                : '';
        }

        $registration = $this->registrationRepository
            ->findById($id);

        if ($registration === null) {
            View::flashMessage('Data pendaftaran tidak ditemukan.');
            View::redirect('/admin/registration/all');

            return getenv('APP_ENV') === 'test'
                ? 'success redirect'
                : '';
        }

        $period = $this->registrationPeriodRepository
            ->findById($registration->registrationPeriodId);

        if ($period === null) {
            View::flashMessage('Periode pendaftaran tidak ditemukan.');
            View::redirect('/admin/registration/all');

            return getenv('APP_ENV') === 'test'
                ? 'success redirect'
                : '';
        }

        $view = $period->level === RegistrationPeriodLevel::LEVEL_1
            ? 'Registration/save-registration-level-1'
            : 'Registration/save-registration-level-2';

        try {
            $request = $this->createAdminRequest($registration);

            $this->registrationService->save($request);

            View::flashMessage(
                'Data pendaftaran berhasil diperbarui.'
            );

            View::redirect('/admin/registration/all');

            return getenv('APP_ENV') === 'test'
                ? 'success redirect'
                : '';
        } catch (
            ValidationException |
            UploadFileException |
            RuntimeException $exception
        ) {
            // Gunakan alamat dari input agar pilihan wilayah tetap terlihat.
            $districtCode = trim(
                $_POST['district_code'] ?? $registration->districtCode
            );

            $villageCode = trim(
                $_POST['village_code'] ?? $registration->villageCode
            );

            $initialAddress = null;

            if ($districtCode !== '') {
                $initialAddress = $this->administrativeAreaRepository
                    ->findDistrictHierarchy($districtCode);
            }

            if ($initialAddress !== null) {
                $initialAddress['village_code'] = $villageCode;
            }

            return View::render('Admin', $view, [
                'title' => 'Edit Data Registrasi',
                'current' => 'registrations',
                'registrationPeriod' => $period,
                'registration' => $registration,
                'initialAddress' => $initialAddress,
                'formAction' => '/admin/registration/edit',
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function postDelete(): string
    {
        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($id === false || $id === null || $id < 1) {
            View::flashMessage('ID pendaftaran tidak valid.');
            View::redirect('/admin/registration/all');

            return getenv('APP_ENV') === 'test'
                ? 'success redirect'
                : '';
        }

        try {
            $this->registrationService->deleteForAdmin($id);

            View::flashMessage(
                'Data pendaftaran berhasil dihapus.'
            );
        } catch (ValidationException | RuntimeException $exception) {
            View::flashMessage($exception->getMessage());
        }

        View::redirect('/admin/registration/all');

        return getenv('APP_ENV') === 'test'
            ? 'success redirect'
            : '';
    }

    public function postDeleteSelected(): string
    {
        $ids = $_POST['ids'] ?? null;

        if (!is_array($ids) || $ids === []) {
            View::flashMessage(
                'Pilih minimal satu data registrasi.'
            );

            View::redirect('/admin/registration/all');

            return getenv('APP_ENV') === 'test'
                ? 'success redirect'
                : '';
        }

        // Validasi seluruh ID sebelum melakukan penghapusan.
        $validatedIds = [];

        foreach ($ids as $id) {
            if (!is_string($id) && !is_int($id)) {
                View::flashMessage(
                    'Daftar ID pendaftaran tidak valid.'
                );

                View::redirect('/admin/registration/all');

                return getenv('APP_ENV') === 'test'
                    ? 'success redirect'
                    : '';
            }

            $validatedId = filter_var(
                $id,
                FILTER_VALIDATE_INT
            );

            if (
                $validatedId === false ||
                $validatedId < 1
            ) {
                View::flashMessage(
                    'Daftar ID pendaftaran tidak valid.'
                );

                View::redirect('/admin/registration/all');

                return getenv('APP_ENV') === 'test'
                    ? 'success redirect'
                    : '';
            }

            $validatedIds[] = $validatedId;
        }

        // Hilangkan ID duplikat.
        $validatedIds = array_values(
            array_unique($validatedIds)
        );

        $successCount = 0;
        $failedCount = 0;

        foreach ($validatedIds as $id) {
            try {
                $this->registrationService->deleteForAdmin($id);
                $successCount++;
            } catch (ValidationException | RuntimeException $exception) {
                $failedCount++;
            }
        }

        if ($failedCount === 0) {
            View::flashMessage(
                sprintf(
                    '%d data pendaftaran berhasil dihapus.',
                    $successCount
                )
            );
        } elseif ($successCount === 0) {
            View::flashMessage(
                sprintf(
                    'Gagal menghapus %d data pendaftaran.',
                    $failedCount
                )
            );
        } else {
            View::flashMessage(
                sprintf(
                    '%d data berhasil dihapus, %d data gagal dihapus.',
                    $successCount,
                    $failedCount
                )
            );
        }

        View::redirect('/admin/registration/all');

        return getenv('APP_ENV') === 'test'
            ? 'success redirect'
            : '';
    }

    private function createAdminRequest(
        Registration $registration
    ): RegistrationSaveRequest {
        $request = new RegistrationSaveRequest();

        // Identitas berasal dari data database yang sudah diverifikasi.
        $request->id = $registration->id;
        $request->userId = $registration->userId;
        $request->registrationPeriodId =
            $registration->registrationPeriodId;

        $request->fullName = trim($_POST['full_name'] ?? '');
        $request->birthPlace = trim($_POST['birth_place'] ?? '');
        $request->birthDate = trim($_POST['birth_date'] ?? '');

        $request->provinceCode = trim($_POST['province_code'] ?? '');
        $request->regencyCode = trim($_POST['regency_code'] ?? '');
        $request->districtCode = trim($_POST['district_code'] ?? '');
        $request->villageCode = trim($_POST['village_code'] ?? '');

        $request->rt = trim($_POST['rt'] ?? '');
        $request->rw = trim($_POST['rw'] ?? '');

        $request->occupation = trim($_POST['occupation'] ?? '');
        $request->trainingLocation = trim(
            $_POST['training_location'] ?? ''
        );

        $request->fatherName = trim($_POST['father_name'] ?? '');
        $request->motherName = trim($_POST['mother_name'] ?? '');
        $request->coachName = trim($_POST['coach_name'] ?? '');
        $request->coachAddress = trim(
            $_POST['coach_address'] ?? ''
        );

        $request->smallPhoto = $this->getUploadedPhoto('small_photo');
        $request->largePhoto = $this->getUploadedPhoto('large_photo');

        return $request;
    }

    private function getUploadedPhoto(
        string $key
    ): ?UploadedFile {
        $file = $_FILES[$key] ?? null;

        if (
            $file === null ||
            !isset($file['error']) ||
            $file['error'] === UPLOAD_ERR_NO_FILE
        ) {
            return null;
        }

        return UploadedFile::fromArray($file);
    }

}
