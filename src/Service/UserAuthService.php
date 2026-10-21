<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\User\UserChangePasswordRequest;
use Danish\IkspiTubanRegistration\Model\User\UserChangePasswordResponse;
use Danish\IkspiTubanRegistration\Model\User\UserLoginRequest;
use Danish\IkspiTubanRegistration\Model\User\UserLoginResponse;
use Danish\IkspiTubanRegistration\Model\User\UserRegisterRequest;
use Danish\IkspiTubanRegistration\Model\User\UserRegisterResponse;
use Danish\IkspiTubanRegistration\Repository\UserRepository;


class UserAuthService
{
    private UserRepository $userRepository;
    private SessionService $sessionService;

    public function __construct(UserRepository $userRepository, SessionService $sessionService)
    {
        $this->userRepository = $userRepository;
        $this->sessionService = $sessionService;
    }

    public function changePassword(UserChangePasswordRequest $request): UserChangePasswordResponse
    {
        $user = $this->sessionService->getCurrentUser();

        if ($user == null) {
            throw new ValidationException('User not valid');
        }

        if (
            !trim($request->newPassword) ||
            !trim($request->confirmPassword) ||
            !trim($request->oldPassword)
        ) {
            throw new ValidationException('Semua kolom wajib di isi');
        }

        if (!password_verify($request->oldPassword, $user->password)) {
            throw new ValidationException('Password lama salah');
        }

        if ($request->newPassword !== $request->confirmPassword) {
            throw new ValidationException('Password baru dan Konfirmasi password harus sama');
        }

        $newPassword = password_hash($request->confirmPassword, PASSWORD_BCRYPT);

        $this->userRepository->updatePasswordById($user->id, $newPassword);
        $result = $this->userRepository->findById($user->id);

        $response = new UserChangePasswordResponse();
        $response->user = $result;
        return $response;
    }

    public function register(UserRegisterRequest $request): UserRegisterResponse
    {
        // VALIDATION
        $this->registerValidation($request);
        if ($this->userRepository->findByEmail($request->email) !== null) {
            throw new ValidationException('Email already exist');
        }

        $user = new User();
        $user->email = $request->email;
        $user->password = password_hash($request->password, PASSWORD_BCRYPT);
        $user->role = UserRole::USER;

        $result = $this->userRepository->save($user);

        $response = new UserRegisterResponse();
        $response->user = $user;

        return $response;
    }

    private function registerValidation(UserRegisterRequest $request)
    {
        if (
            !trim($request->email) ||
            !trim($request->password)
        ) {
            throw new ValidationException('Email atau password tidak boleh kosong');
        }
    }

    public function login(UserLoginRequest $request): UserLoginResponse
    {
        $this->loginValidation($request);

        $user = $this->userRepository->findByEmail($request->email);
        if ($user === null) {
            throw new ValidationException('Email atau password salah');
        }

        if (!password_verify($request->password, $user->password)) {
            throw new ValidationException('Email atau password salah');
        }

        $this->sessionService->create($user->id);

        $response = new UserLoginResponse();
        $response->user = $user;

        return $response;
    }
    private function loginValidation(UserLoginRequest $request)
    {
        if (
            !trim($request->email) ||
            !trim($request->password)
        ) {
            throw new ValidationException('Email atau password tidak boleh kosong');
        }
    }

    public function logout()
    {
        $this->sessionService->destroy();
    }
}