<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
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
use Danish\IkspiTubanRegistration\Service\RegistrationPeriodService;
use Danish\IkspiTubanRegistration\Service\RegistrationService;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Danish\IkspiTubanRegistration\Service\UploadRegistrationPhotoService;
use RuntimeException;

class RegistrationController extends BaseController
{
    private SessionService $sessionService;
    private RegistrationService $registrationService;
    private RegistrationRepository $registrationRepository;
    private RegistrationPeriodRepository $registrationPeriodRepository;
    private RegistrationPeriodService $registrationPeriodService;
    private AdministrativeAreaRepository $administrativeAreaRepository;

    public function __construct()
    {
        $connection = Database::getConnection();

        $userRepository = new UserRepository($connection);
        $profileRepository = new ProfileRepository($connection);
        $sessionRepository = new SessionRepository($connection);

        $this->registrationRepository =
            new RegistrationRepository($connection);

        $this->registrationPeriodRepository =
            new RegistrationPeriodRepository($connection);

        $this->sessionService = new SessionService(
            $sessionRepository,
            $userRepository
        );

        $this->administrativeAreaRepository = new AdministrativeAreaRepository($connection);

        $this->registrationService = new RegistrationService(
            $this->registrationRepository,
            new UploadRegistrationPhotoService(),
            $this->administrativeAreaRepository
        );

        $this->registrationPeriodService = new RegistrationPeriodService($this->registrationPeriodRepository);

        parent::__construct(
            $userRepository,
            $profileRepository,
            $sessionRepository
        );
    }

    public function registration(): string
    {
        $periods = $this->registrationPeriodService->findAllActive();

        return View::render('User', 'Registration/registration', [
            'title' => 'Registrasi',
            'periods' => $periods,
            'current' => 'registration'
        ]);
    }

    public function saveLevel1(): string
    {
        return $this->showForm(RegistrationPeriodLevel::LEVEL_1);
    }

    public function saveLevel2(): string
    {
        return $this->showForm(RegistrationPeriodLevel::LEVEL_2);
    }

    private function showForm(
        RegistrationPeriodLevel $level
    ): string {
        $user = $this->sessionService->getCurrentUser();

        if ($user === null) {
            View::redirect('/login');

            return '';
        }

        $period = $this->registrationPeriodRepository
            ->findActiveByLevel($level);

        // Cari kecamatan berdasarkan email user.
        $initialAddress = null;

        $district = $this->administrativeAreaRepository
            ->findDistrictMentionedInEmail($user->email);

        if ($district !== null) {
            $initialAddress = $this->administrativeAreaRepository
                ->findDistrictHierarchy($district->code);
        }

        $view = $level === RegistrationPeriodLevel::LEVEL_1
            ? 'Registration/save-registration-level-1'
            : 'Registration/save-registration-level-2';

        return View::render('User', $view, [
            'title' => 'Pendaftaran IKSPI',
            'current' => 'registration',
            'registrationPeriod' => $period,
            'registration' => null,
            'initialAddress' => $initialAddress,
        ]);
    }

    public function postSaveLevel1(): string
    {
        return $this->postSave(RegistrationPeriodLevel::LEVEL_1);
    }

    public function postSaveLevel2(): string
    {
        return $this->postSave(RegistrationPeriodLevel::LEVEL_2);
    }

    public function postEdit(): string
    {
        return $this->postSave(null);
    }


    private function postSave(
        ?RegistrationPeriodLevel $level
    ): string {
        $user = $this->sessionService->getCurrentUser();

        if ($user === null) {
            View::redirect('/login');
            return '';
        }

        $current = null;
        $period = null;
        $view = '';

        try {
            $id = filter_var(
                $_POST['id'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($level === null) {
                // EDIT
                if ($id === false || $id === null || $id < 1) {
                    throw new ValidationException(
                        'ID pendaftaran tidak valid.'
                    );
                }

                $current = $this->registrationRepository
                    ->findById($id);

                if (
                    $current === null ||
                    $current->userId !== $user->id
                ) {
                    throw new ValidationException(
                        'Data pendaftaran tidak ditemukan.'
                    );
                }

                $period = $this->registrationPeriodRepository
                    ->findById($current->registrationPeriodId);

                if ($period === null) {
                    throw new ValidationException(
                        'Periode pendaftaran tidak ditemukan.'
                    );
                }
            } else {
                // CREATE
                $period = $this->registrationPeriodRepository
                    ->findActiveByLevel($level);

                if ($period === null) {
                    throw new ValidationException(
                        'Periode pendaftaran tidak tersedia.'
                    );
                }
            }

            $view = $period->level === RegistrationPeriodLevel::LEVEL_1
                ? 'Registration/save-registration-level-1'
                : 'Registration/save-registration-level-2';

            $request = $this->createRequest($user->id, $period);

            if ($level === null) {
                // Pastikan ID request sesuai dengan data yang diizinkan diedit.
                if ($request->id !== $current->id) {
                    throw new ValidationException(
                        'ID pendaftaran tidak sesuai.'
                    );
                }
            } elseif ($request->id !== null) {
                // Route tambah tidak boleh mengubah data sembarang.
                throw new ValidationException(
                    'Permintaan pendaftaran tidak valid.'
                );
            }

            $this->registrationService->save($request);

            View::flashMessage(
                $request->id === null
                ? 'Pendaftaran berhasil disimpan.'
                : 'Pendaftaran berhasil diperbarui.'
            );

            View::redirect('/registration/all');

            return getenv('APP_ENV') === 'test'
                ? 'success redirect'
                : '';
        } catch (
            ValidationException |
            UploadFileException |
            RuntimeException $exception
        ) {
            $initialAddress = null;

            if ($current !== null) {
                // EDIT: ambil alamat dari data yang tersimpan.
                $initialAddress = $this->administrativeAreaRepository
                    ->findDistrictHierarchy($current->districtCode);

                if ($initialAddress !== null) {
                    $initialAddress['village_code'] =
                        $current->villageCode;
                }
            } else {
                // CREATE: ambil alamat dari input yang dikirim.
                $districtCode = trim($_POST['district_code'] ?? '');
                $villageCode = trim($_POST['village_code'] ?? '');

                if ($districtCode !== '') {
                    $initialAddress = $this->administrativeAreaRepository
                        ->findDistrictHierarchy($districtCode);

                    if ($initialAddress !== null) {
                        $initialAddress['village_code'] = $villageCode;
                    }
                }
            }

            return View::render('User', $view, [
                'title' => 'Pendaftaran IKSPI',
                'current' => $current !== null
                    ? 'registrationByUserId'
                    : 'registration',
                'registrationPeriod' => $period,
                'registration' => $current,
                'initialAddress' => $initialAddress,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function edit(): string
    {
        $user = $this->sessionService->getCurrentUser();

        if ($user === null) {
            View::redirect('/login');
            return '';
        }

        $id = filter_input(
            INPUT_GET,
            'id',
            FILTER_VALIDATE_INT
        );

        if ($id === false || $id === null || $id < 1) {
            View::redirect('/registration/all');
            return '';
        }

        $registration = $this->registrationRepository
            ->findById($id);

        // Pastikan data ditemukan dan milik pengguna yang login.
        if (
            $registration === null ||
            $registration->userId !== $user->id
        ) {
            View::redirect('/registration/all');
            return '';
        }

        $period = $this->registrationPeriodRepository
            ->findById($registration->registrationPeriodId);

        if ($period === null) {
            View::redirect('/registration/all');
            return '';
        }

        // Ambil hierarki wilayah dari data yang tersimpan.
        $initialAddress = $this->administrativeAreaRepository
            ->findDistrictHierarchy($registration->districtCode);

        if ($initialAddress !== null) {
            $initialAddress['village_code'] =
                $registration->villageCode;
        }

        $view = $period->level === RegistrationPeriodLevel::LEVEL_1
            ? 'Registration/save-registration-level-1'
            : 'Registration/save-registration-level-2';

        return View::render('User', $view, [
            'title' => 'Edit Pendaftaran IKSPI',
            'current' => 'registrationByUserId',
            'registrationPeriod' => $period,
            'registration' => $registration,
            'initialAddress' => $initialAddress,
        ]);
    }

    public function postDelete(): string
    {
        $user = $this->sessionService->getCurrentUser();

        if ($user === null) {
            View::redirect('/login');
            return '';
        }

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($id === false || $id === null || $id < 1) {
            View::flashMessage('ID pendaftaran tidak valid.');
            View::redirect('/registration/all');

            return '';
        }

        try {
            $this->registrationService->delete($id, $user->id);

            View::flashMessage(
                'Data pendaftaran berhasil dihapus.'
            );
        } catch (ValidationException | RuntimeException $exception) {
            View::flashMessage($exception->getMessage());
        }

        View::redirect('/registration/all');

        return getenv('APP_ENV') === 'test'
            ? 'success redirect'
            : '';
    }

    private function createRequest(
        int $userId,
        RegistrationPeriod $period
    ): RegistrationSaveRequest {
        $request = new RegistrationSaveRequest();

        $id = filter_var(
            $_POST['id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $request->id = $id !== false ? $id : null;

        $request->userId = $userId;
        $request->registrationPeriodId = $period->id;

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

    private function getUploadedPhoto(string $key): ?UploadedFile
    {
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


    public function registrationByUserId(): string
    {
        $user = $this->sessionService->getCurrentUser();

        if ($user === null) {
            View::redirect('/login');
            return '';
        }

        $level = trim($_GET['level'] ?? '');
        $batchInput = trim($_GET['batch'] ?? '');

        // Validasi filter tingkat.
        if (!in_array($level, ['', 'I', 'II'], true)) {
            $level = '';
        }

        // Validasi filter angkatan.
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
            ->findByUserId(
                $user->id,
                $level !== '' ? $level : null,
                $batch
            );

        return View::render(
            'User',
            'Registration/user-registration-all',
            [
                'title' => 'Data Registrasi',
                'registrations' => $registrations,
                'current' => 'registrationByUserId',
            ]
        );
    }

}