<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationPeriodRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\RegistrationPeriodService;

class RegistrationController extends BaseController
{


    private RegistrationPeriodService $registrationPeriodService;

    public function __construct()
    {
        $pdo = Database::getConnection();

        $userRepository = new UserRepository($pdo);
        $profileRepository = new ProfileRepository($pdo);
        $sessionRepository = new SessionRepository($pdo);

        $registrationPeriodRepository = new RegistrationPeriodRepository($pdo);

        $this->registrationPeriodService =
            new RegistrationPeriodService(
                $registrationPeriodRepository
            );

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

}
