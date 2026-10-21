<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Exception\UploadFileException;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\Profile\UserSaveProfileRequest;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\ProfileService;
use Danish\IkspiTubanRegistration\Service\SessionService;

class ProfileController extends BaseController
{
    private SessionService $sessionService;
    private ProfileService $profileService;
    public function __construct()
    {
        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $profileRepository = new ProfileRepository(Database::getConnection());

        $this->sessionService = new SessionService($sessionRepository, $userRepository);
        $this->profileService = new ProfileService($profileRepository, $userRepository);

        parent::__construct(
            $userRepository,
            $profileRepository,
            $sessionRepository
        );
    }

    function save(): string
    {
        $user = $this->sessionService->getCurrentUser();
        $profile = $this->profileRepository->findByUserId($user->id);

        if ($user !== null && $user->role->value == 'admin') {
            return View::render('Admin', 'save-profile', [
                'title' => 'Update Profile',
                'profile' => $profile ?? '',
                'current' => 'profile'
            ]);
        }

        return View::render('User', 'save-profile', [
            'title' => 'Update Profile',
            'profile' => $profile ?? '',
            'current' => 'profile'
        ]);

    }

    public function postSave(): string
    {
        $user = $this->sessionService->getCurrentUser();
        $profile = $this->profileRepository->findByUserId($user->id);

        $request = new UserSaveProfileRequest();
        $request->name = $_POST['name'] ?? '';
        $request->avatar = UploadedFile::fromArray($_FILES['avatar']);

        $viewType = $user->role->value === 'admin' ? 'Admin' : 'User';

        try {
            $this->profileService->save($user->id, $request);

            View::flashMessage('Sukses update profil');
            View::redirect('/profile');

            return getenv('APP_ENV') === 'test' ? 'success redirect' : '';
        } catch (ValidationException | UploadFileException $e) {
            return View::render($viewType, 'save-profile', [
                'title' => 'Update Profile',
                'profile' => $profile ?? '',
                'error' => $e->getMessage(),
                'current' => 'profile'
            ]);
        }
    }

}
