<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\RegistrationPeriod\AdminSaveRegistrationPeriodRequest;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationPeriodRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\RegistrationPeriodService;

class RegistrationPeriodController extends BaseController
{
    private RegistrationPeriodRepository $registrationPeriodRepository;
    private RegistrationPeriodService $registrationPeriodService;

    public function __construct()
    {
        $this->registrationPeriodRepository = new RegistrationPeriodRepository(Database::getConnection());
        $this->registrationPeriodService = new RegistrationPeriodService($this->registrationPeriodRepository);

        $userRepository = new UserRepository(Database::getConnection());
        $profileRepository = new ProfileRepository(Database::getConnection());
        $sessionRepository = new SessionRepository(Database::getConnection());
        parent::__construct(
            $userRepository,
            $profileRepository,
            $sessionRepository
        );
    }

    function registrationPeriods(): string
    {
        try {
            $allPeriod = $this->registrationPeriodService->getAll();
            return View::render('Admin', 'Registration/all-period', [
                'title' => 'All Registration Period',
                'allPeriod' => $allPeriod,
                'current' => 'periods'
            ]);
        } catch (ValidationException $e) {
            return View::render('Admin', 'Registration/all-period', [
                'title' => 'All Registration Period',
                'error' => $e->getMessage(),
                'current' => 'periods'
            ]);
        }
    }

    function save()
    {
        $id = $_GET['id'] ?? '';

        $period = null;

        if ($id !== '') {
            $period = $this->registrationPeriodRepository->findById((int) $id);
            return View::render('Admin', 'Registration/edit-period', [
                'title' => 'Update Registration Period',
                'period' => $period,
                'current' => 'periods'
            ]);
        }

        return View::render('Admin', 'Registration/add-period', [
            'title' => 'Tambah Registration Period',
            'current' => 'periods'
        ]);
    }

    function postSave(): string
    {
        $request = new AdminSaveRegistrationPeriodRequest();
        $request->id = $_POST['id'] ?? null;
        $request->level = $_POST['level'];
        $request->date = $_POST['date'];
        $request->time = $_POST['time'];
        $request->location = $_POST['location'];
        $request->type = $_POST['type'];
        $request->batch = (int) $_POST['batch'];
        $request->class = $_POST['class'];
        $request->region = $_POST['region'];
        $request->isActive = isset($_POST['isActive'])
            ? match ($_POST['isActive']) {
                '1' => true,
                '0' => false,
                default => null
            }
            : null;
        $request->information = $_POST['information'];

        try {
            $this->registrationPeriodService->save($request);
            View::flashMessage(
                $request->id === null
                ? 'Sukses menambahkan periode baru'
                : 'Sukses memperbarui periode pendaftaran'
            );
            View::redirect('/registration/period/all');
            return '';
        } catch (ValidationException $e) {
            if ($request->id == null) {
                return View::render('Admin', 'Registration/add-period', [
                    'title' => 'Tambah Registration Period',
                    'current' => 'periods',
                    'error' => $e->getMessage()
                ]);
            } else {
                $period = $this->registrationPeriodRepository->findById((int) $request->id);
                return View::render('Admin', 'Registration/edit-period', [
                    'title' => 'Update Registration Period',
                    'current' => 'periods',
                    'period' => $period,
                    'error' => $e->getMessage()
                ]);
            }
        }

    }

    function postDelete()
    {
        $id = (int) $_POST['id'];
        $this->registrationPeriodRepository->deleteById($id);
        View::flashMessage('Sukses menghapus periode');
        View::redirect('/registration/period/all');
    }

}
