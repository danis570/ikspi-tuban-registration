<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;

abstract class BaseController
{
    protected UserRepository $userRepository;
    protected ProfileRepository $profileRepository;
    protected SessionRepository $sessionRepository;

    public function __construct(
        UserRepository $userRepository,
        ProfileRepository $profileRepository,
        SessionRepository $sessionRepository
    ) {
        $this->userRepository = $userRepository;
        $this->profileRepository = $profileRepository;
        $this->sessionRepository = $sessionRepository;

        $sessionId = $_COOKIE[SessionService::$cookieName] ?? null;

        if ($sessionId) {
            $currentSession = $this->sessionRepository->findById($sessionId);

            if ($currentSession) {
                $currentUser = $this->userRepository->findById($currentSession->userId);
                $currentProfile = $this->profileRepository->findByUserId($currentSession->userId);

                View::share('currentUser', $currentUser);
                View::share('currentProfile', $currentProfile);
            }
        }
    }
}
