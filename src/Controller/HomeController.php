<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;

class HomeController extends BaseController
{
    private SessionService $sessionService;

    public function __construct()
    {
        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $profileRepository = new ProfileRepository(Database::getConnection());
        $this->sessionService = new SessionService($sessionRepository, $userRepository);

        parent::__construct(
            $userRepository,
            $profileRepository,
            $sessionRepository
        );
    }

    function home(): string
    {
        $session = $this->sessionService->getCurrentUser();
        if ($session !== null && $session->role->value == 'admin') {
            return View::render('Admin', 'home', [
                'title' => 'User Management'
            ]);
        }
        if ($session !== null) {
            return View::render('User', 'home', [
                'title' => 'User Management'
            ]);
        }
        return View::render('Public', 'home', [
            'title' => 'User Management',
        ]);
    }

    function about(): string
    {
        return View::render('Public', 'about', [
            'title' => 'About',
            'current' => 'about'
        ]);
    }
}