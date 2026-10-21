<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Exception\DataNotFoundException;
use Danish\IkspiTubanRegistration\Exception\DeleteFailedException;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\UserService;

class UserController extends BaseController
{
    private UserService $userService;

    public function __construct()
    {

        $sessionRepository = new SessionRepository(Database::getConnection());
        $userRepository = new UserRepository(Database::getConnection());
        $profileRepository = new ProfileRepository(Database::getConnection());

        $this->userService = new UserService($userRepository); // Menggunakan variabel lokal $userRepository

        parent::__construct(
            $userRepository,
            $profileRepository,
            $sessionRepository
        );
    }

    function allUser(): string
    {
        try {
            $users = $this->userService->getAll();
            return View::render('Admin', 'all-user', [
                'title' => 'All User',
                'users' => $users,
                'current' => 'users'
            ]);
        } catch (DataNotFoundException $e) {
            return View::render('Admin', 'all-user', [
                'title' => 'All User',
                'users' => [],
                'error' => $e->getMessage(),
                'current' => 'users'
            ]);
        }
    }

    function postDelete(): string
    {
        $id = $_POST['id'];

        try {
            $this->userService->deleteById($id);
            View::flashMessage('Success delete user id - ' . $id);
            View::redirect('/user/all');
            if (getenv('APP_ENV') === 'test') {
                return 'success redirect';
            }
            return '';
        } catch (DeleteFailedException $e) {
            $users = $this->userService->getAll();
            return View::render('Admin', 'all-user', [
                'title' => 'All User',
                'users' => $users,
                'error' => $e->getMessage(),
                'current' => 'users'
            ]);
        }
    }
}
