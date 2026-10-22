<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationPeriodRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\RegistrationExcelService;
use Danish\IkspiTubanRegistration\Service\RegistrationPdfService;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Throwable;

class RegistrationGenerateController extends BaseController
{
    private RegistrationRepository $registrationRepository;
    private RegistrationPeriodRepository $registrationPeriodRepository;
    private SessionService $sessionService;
    private RegistrationPdfService $registrationPdfService;
    private RegistrationExcelService $registrationExcelService;

    public function __construct()
    {
        $connection = Database::getConnection();

        $this->registrationRepository =
            new RegistrationRepository($connection);

        $this->registrationPeriodRepository =
            new RegistrationPeriodRepository($connection);

        $userRepository = new UserRepository($connection);
        $sessionRepository = new SessionRepository($connection);

        $this->sessionService = new SessionService(
            $sessionRepository,
            $userRepository
        );

        $this->registrationPdfService =
            new RegistrationPdfService();
        $this->registrationExcelService =
            new RegistrationExcelService();
        $profileRepository = new ProfileRepository($connection);

        parent::__construct(
            $userRepository,
            $profileRepository,
            $sessionRepository
        );
    }

    public function postExcel(): string
    {
        $registrations = $this->getSelectedRegistrationsForUser();

        if ($registrations === null) {
            return '';
        }

        try {
            $excel = $this->registrationExcelService->generate(
                $registrations
            );

            header(
                'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
            header(
                'Content-Disposition: attachment; filename="registrasi-peserta.xlsx"'
            );
            header('Content-Length: ' . strlen($excel));
            header('Cache-Control: private, max-age=0, must-revalidate');

            return $excel;
        } catch (Throwable $exception) {
            error_log(
                'Gagal generate Excel registrasi: '
                . $exception->getMessage()
            );

            $_SESSION['flash']['type'] = 'error';
            $_SESSION['flash']['message'] =
                'Gagal membuat Excel. Silakan coba kembali.';

            View::redirect('/registration/all');

            return '';
        }
    }

    public function postSelected(): string
    {
        $registrations = $this->getSelectedRegistrationsForUser();

        if ($registrations === null) {
            return '';
        }

        return View::render(
            'User',
            'Registration/registration-generate-confirmation',
            [
                'title' => 'Konfirmasi Generate',
                'registrations' => $registrations,
                'current' => 'registrationByUserId',
            ]
        );
    }

    public function postPdf(): string
    {
        $registrations = $this->getSelectedRegistrationsForUser();

        if ($registrations === null) {
            return '';
        }

        try {
            $pdf = $this->registrationPdfService->generate(
                $registrations
            );

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="registrasi-peserta.pdf"');
            header('Content-Length: ' . strlen($pdf));
            header('Cache-Control: private, max-age=0, must-revalidate');

            return $pdf;
        } catch (Throwable $exception) {
            error_log(
                'Gagal generate PDF registrasi: '
                . $exception->getMessage()
            );

            $_SESSION['flash']['type'] = 'error';
            $_SESSION['flash']['message'] =
                'Gagal membuat PDF. Silakan coba kembali.';

            View::redirect('/registration/all');

            return '';
        }
    }

    /**
     * Mengambil dan memvalidasi peserta dari ID yang dikirim melalui POST.
     *
     * @return array<int, array{
     *     registration: \Danish\IkspiTubanRegistration\Domain\Registration,
     *     period: \Danish\IkspiTubanRegistration\Domain\RegistrationPeriod
     * }>|null
     */

    private function getSelectedRegistrations(): ?array
    {
        $user = $this->sessionService->getCurrentUser();

        if ($user === null) {
            View::redirect('/login');
            return null;
        }

        // JavaScript mengirim selected_registration_ids[] sebagai array.
        $rawIds = $_POST['selected_registration_ids'] ?? null;

        if (!is_array($rawIds) || $rawIds === []) {
            $_SESSION['flash']['type'] = 'error';
            $_SESSION['flash']['message'] =
                'Pilih minimal satu peserta terlebih dahulu.';

            View::redirect('/admin/registration/all');
            return null;
        }

        $selectedIds = [];

        foreach ($rawIds as $id) {
            if (
                (!is_string($id) && !is_int($id)) ||
                !ctype_digit((string) $id) ||
                (int) $id < 1
            ) {
                $_SESSION['flash']['type'] = 'error';
                $_SESSION['flash']['message'] =
                    'Format pilihan peserta tidak valid.';

                View::redirect('/admin/registration/all');
                return null;
            }

            $selectedIds[] = (int) $id;
        }

        $selectedIds = array_values(array_unique($selectedIds));
        $selectedRegistrations = [];

        foreach ($selectedIds as $id) {
            $registration =
                $this->registrationRepository->findById($id);

            if ($registration === null) {
                $_SESSION['flash']['type'] = 'error';
                $_SESSION['flash']['message'] =
                    'Salah satu peserta tidak ditemukan.';

                View::redirect('/admin/registration/all');
                return null;
            }

            $period = $this->registrationPeriodRepository->findById(
                $registration->registrationPeriodId
            );

            if ($period === null) {
                $_SESSION['flash']['type'] = 'error';
                $_SESSION['flash']['message'] =
                    'Periode registrasi peserta tidak ditemukan.';

                View::redirect('/admin/registration/all');
                return null;
            }

            $selectedRegistrations[] = [
                'registration' => $registration,
                'period' => $period,
            ];
        }

        return $selectedRegistrations;
    }


    /**
     * @return array<int, array{
     *     registration: \Danish\IkspiTubanRegistration\Domain\Registration,
     *     period: \Danish\IkspiTubanRegistration\Domain\RegistrationPeriod
     * }>|null
     */
    private function getSelectedRegistrationsForUser(): ?array
    {
        $user = $this->sessionService->getCurrentUser();

        if ($user === null) {
            View::redirect('/login');
            return null;
        }

        $rawIds = $_POST['selected_registration_ids'] ?? '';


        if (!is_string($rawIds)) {
            $_SESSION['flash']['type'] = 'error';
            $_SESSION['flash']['message'] =
                'Format pilihan peserta tidak valid.';

            View::redirect('/registration/all');
            return null;
        }

        $ids = json_decode($rawIds, true);

        if (!is_array($ids) || $ids === []) {
            $_SESSION['flash']['type'] = 'error';
            $_SESSION['flash']['message'] =
                'Pilih minimal satu peserta terlebih dahulu.';

            View::redirect('/registration/all');
            return null;
        }

        $selectedIds = [];

        foreach ($ids as $id) {
            if (
                (!is_int($id) && !is_string($id)) ||
                !ctype_digit((string) $id) ||
                (int) $id < 1
            ) {
                $_SESSION['flash']['type'] = 'error';
                $_SESSION['flash']['message'] =
                    'Format pilihan peserta tidak valid.';

                View::redirect('/registration/all');
                return null;
            }

            $selectedIds[] = (int) $id;
        }

        $selectedIds = array_values(array_unique($selectedIds));
        $selectedRegistrations = [];

        foreach ($selectedIds as $id) {
            $registration = $this->registrationRepository->findById($id);

            if (
                $registration === null ||
                $registration->userId !== $user->id
            ) {
                $_SESSION['flash']['type'] = 'error';
                $_SESSION['flash']['message'] =
                    'Peserta tidak ditemukan atau tidak dapat diakses.';

                View::redirect('/registration/all');
                return null;
            }

            $period = $this->registrationPeriodRepository->findById(
                $registration->registrationPeriodId
            );

            if ($period === null) {
                $_SESSION['flash']['type'] = 'error';
                $_SESSION['flash']['message'] =
                    'Periode registrasi peserta tidak ditemukan.';

                View::redirect('/registration/all');
                return null;
            }

            $selectedRegistrations[] = [
                'registration' => $registration,
                'period' => $period,
            ];
        }

        return $selectedRegistrations;
    }

    public function postAdminPdf(): string
    {
        $registrations = $this->getSelectedRegistrations();

        if ($registrations === null) {
            return '';
        }

        try {
            $pdf = $this->registrationPdfService->generate($registrations);

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="registrasi-peserta.pdf"');
            header('Content-Length: ' . strlen($pdf));
            header('Cache-Control: private, max-age=0, must-revalidate');

            return $pdf;
        } catch (Throwable $exception) {
            error_log('Gagal generate PDF admin: ' . $exception->getMessage());

            $_SESSION['flash']['type'] = 'error';
            $_SESSION['flash']['message'] =
                'Gagal membuat PDF. Silakan coba kembali.';

            View::redirect('/admin/registration/all');

            return '';
        }
    }

    public function postAdminExcel(): string
    {
        $registrations = $this->getSelectedRegistrations();

        if ($registrations === null) {
            return '';
        }

        try {
            $excel = $this->registrationExcelService->generate($registrations);

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="registrasi-peserta.xlsx"');
            header('Content-Length: ' . strlen($excel));
            header('Cache-Control: private, max-age=0, must-revalidate');

            return $excel;
        } catch (Throwable $exception) {
            error_log('Gagal generate Excel admin: ' . $exception->getMessage());

            $_SESSION['flash']['type'] = 'error';
            $_SESSION['flash']['message'] =
                'Gagal membuat Excel. Silakan coba kembali.';

            View::redirect('/admin/registration/all');

            return '';
        }
    }

}
