<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\App\View;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\User\UserChangePasswordRequest;
use Danish\IkspiTubanRegistration\Model\User\UserLoginRequest;
use Danish\IkspiTubanRegistration\Model\User\UserRegisterRequest;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Danish\IkspiTubanRegistration\Service\UserAuthService;
use Dotenv\Exception\ValidationException as ExceptionValidationException;

class UserAuthController extends BaseController
{
    private SessionService $sessionService;
    private UserAuthService $userAuthService;

    public function __construct()
    {
        $userRepository = new UserRepository(Database::getConnection());
        $sessionRepository = new SessionRepository(Database::getConnection());
        $profileRepository = new ProfileRepository(Database::getConnection());

        $this->sessionService = new SessionService($sessionRepository, $userRepository);

        $this->userAuthService = new UserAuthService($userRepository, $this->sessionService);

        parent::__construct(
            $userRepository,
            $profileRepository,
            $sessionRepository
        );
    }
    public function changePassword(): string
    {
        $user = $this->sessionService->getCurrentUser();
        $viewType = ($user !== null && $user->role->value === 'admin') ? 'Admin' : 'User';

        return View::render($viewType, 'change-password', [
            'title' => 'Change Password',
            'current' => 'account'
        ]);
    }

    function postChangePassword()
    {
        $user = $this->sessionService->getCurrentUser();

        $request = new UserChangePasswordRequest();
        $request->oldPassword = $_POST['old_password'];
        $request->newPassword = $_POST['new_password'];
        $request->confirmPassword = $_POST['confirm_password'];

        $viewType = ($user !== null && $user->role->value === 'admin') ? 'Admin' : 'User';

        try {
            $this->userAuthService->changePassword($request);
            View::flashMessage('Sukses update password');
            View::redirect('/auth/password');
            return (getenv('APP_ENV') == 'test') ? 'success redirect' : '';
        } catch (ValidationException $e) {
            return View::render($viewType, 'change-password', [
                'title' => 'Change Password',
                'error' => $e->getMessage(),
                'current' => 'account'
            ]);
        }
    }

    function register(): string
    {
        return View::render('Admin', 'register', [
            'title' => 'Register',
            'current' => 'users'
        ]);
    }

    function postRegister(): string
    {
        $request = new UserRegisterRequest();
        $request->email = $_POST['email'];
        $request->password = $_POST['password'];

        try {
            $this->userAuthService->register($request);
            View::flashMessage('SSukses menambahkan pengguna baru - ' . $request->email);
            View::redirect('/user/all');
            if (getenv('APP_ENV') === 'test') {
                return 'success redirect';
            }
            return '';
        } catch (ValidationException $e) {
            return View::render('Admin', 'register', [
                'title' => 'Register',
                'error' => $e->getMessage(),
                'current' => 'users'
            ]);
        }
    }

    function login(): string
    {
        return View::render('Public', 'login', [
            'title' => 'Login',
            'current' => 'login'
        ]);
    }

    function postLogin(): string
    {
        $request = new UserLoginRequest();
        $request->email = $_POST['email'];
        $request->password = $_POST['password'];

        try {
            $this->userAuthService->login($request);
            View::redirect('/');
            if (getenv('APP_ENV') === 'test') {
                return 'success redirect';
            }
            return '';
        } catch (ValidationException $e) {
            return View::render('Public', 'login', [
                'title' => 'Login',
                'error' => $e->getMessage(),
                'current' => 'login'
            ]);
        }
    }

    function postLogout(): void
    {
        $this->sessionService->destroy();
        View::redirect('/');
    }

}