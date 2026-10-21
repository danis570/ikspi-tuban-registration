<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\User\UserChangePasswordRequest;
use Danish\IkspiTubanRegistration\Model\User\UserLoginRequest;
use Danish\IkspiTubanRegistration\Model\User\UserRegisterRequest;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Override;
use Danish\IkspiTubanRegistration\TestCase;
use Dotenv\Exception\ValidationException as ExceptionValidationException;

class UserAuthServiceTest extends TestCase
{
    private UserAuthService $userAuthService;
    private UserRepository $userRepository;
    private SessionService $sessionService;
    private SessionRepository $sessionRepository;
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new UserRepository(Database::getConnection());
        $this->sessionRepository = new SessionRepository(Database::getConnection());
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);
        $this->userAuthService = new UserAuthService($this->userRepository, $this->sessionService);

        $this->userRepository->deleteAll();
    }

    function testUserChangePassword()
    {
        $registerRequest = $this->createRegisterRequest();
        $this->userAuthService->register($registerRequest);

        $loginRequest = $this->createLoginRequest();
        $response = $this->userAuthService->login($loginRequest);

        $request = new UserChangePasswordRequest();
        $request->oldPassword = 'rahasia';
        $request->newPassword = 'ok';
        $request->confirmPassword = 'ok';

        $session = $this->sessionService->create($response->user->id);
        $_COOKIE[SessionService::$cookieName] = $session->id;

        $result = $this->userAuthService->changePassword($request);

        $user = $this->userRepository->findByEmail($loginRequest->email);

        self::assertTrue(password_verify($request->confirmPassword, $user->password));
        self::assertEquals($user->email, $loginRequest->email);

    }

    function testUserChangePasswordValidationExceptionConfirmPasswordWrong()
    {
        $registerRequest = $this->createRegisterRequest();
        $this->userAuthService->register($registerRequest);

        $loginRequest = $this->createLoginRequest();
        $response = $this->userAuthService->login($loginRequest);

        $request = new UserChangePasswordRequest();
        $request->oldPassword = 'rahasia';
        $request->newPassword = 'ok';
        $request->confirmPassword = 'k';

        $session = $this->sessionService->create($response->user->id);
        $_COOKIE[SessionService::$cookieName] = $session->id;

        self::expectException(ValidationException::class);
        self::expectExceptionMessageIs('Perhatian: Password baru dan Konfirmasi password harus sama');
        $result = $this->userAuthService->changePassword($request);

        $user = $this->userRepository->findByEmail($loginRequest->email);

    }

    private function createRegisterRequest(): UserRegisterRequest
    {
        $request = new UserRegisterRequest();
        $request->email = 'ahmad@gmail.com';
        $request->password = 'rahasia';

        return $request;
    }

    private function createLoginRequest(): UserLoginRequest
    {
        $request = new UserLoginRequest();
        $request->email = 'ahmad@gmail.com';
        $request->password = 'rahasia';

        return $request;
    }

    function testLoginSuccess()
    {
        $registerRequest = $this->createRegisterRequest();
        $this->userAuthService->register($registerRequest);

        $loginRequest = $this->createLoginRequest();
        $response = $this->userAuthService->login($loginRequest);

        $session = $this->sessionRepository->findByUserId($response->user->id);
        self::assertEquals($session->userId, $response->user->id);

        self::assertEquals($loginRequest->email, $response->user->email);
        self::assertTrue(password_verify($loginRequest->password, $response->user->password));
    }

    function testLogout()
    {
        $registerRequest = $this->createRegisterRequest();
        $this->userAuthService->register($registerRequest);

        $loginRequest = $this->createLoginRequest();
        $response = $this->userAuthService->login($loginRequest);

        $session = $this->sessionRepository->findByUserId($response->user->id);

        $_COOKIE[SessionService::$cookieName] = $session->id;
        $this->userAuthService->logout();

        $session = $this->sessionRepository->findByUserId($response->user->id);

        self::assertNull($session);
    }

    function testLoginFailedWrongPassword()
    {
        self::expectException(ValidationException::class);
        self::expectExceptionMessageIs('Perhatian: Email atau password salah');

        $registerRequest = $this->createRegisterRequest();
        $this->userAuthService->register($registerRequest);

        $loginRequest = $this->createLoginRequest();
        $loginRequest->password = 'salah';
        $response = $this->userAuthService->login($loginRequest);

    }

    function testLoginFailedWrongEmail()
    {
        self::expectException(ValidationException::class);
        self::expectExceptionMessageIs('Perhatian: Email atau password salah');

        $registerRequest = $this->createRegisterRequest();
        $this->userAuthService->register($registerRequest);

        $loginRequest = $this->createLoginRequest();
        $loginRequest->email = 'salah';
        $response = $this->userAuthService->login($loginRequest);

    }

    function testLoginFailedEmptyEmaill()
    {
        self::expectException(ValidationException::class);
        self::expectExceptionMessageIs('Perhatian: Email atau password tidak boleh kosong');

        $registerRequest = $this->createRegisterRequest();
        $this->userAuthService->register($registerRequest);

        $loginRequest = $this->createLoginRequest();
        $loginRequest->email = '';
        $response = $this->userAuthService->login($loginRequest);

    }

    function testLoginFailedEmptyPassword()
    {
        self::expectException(ValidationException::class);
        self::expectExceptionMessageIs('Perhatian: Email atau password tidak boleh kosong');

        $registerRequest = $this->createRegisterRequest();
        $this->userAuthService->register($registerRequest);

        $loginRequest = $this->createLoginRequest();
        $loginRequest->password = '';
        $response = $this->userAuthService->login($loginRequest);

    }

    function testRegisterSeccess()
    {
        $request = $this->createRegisterRequest();

        $result = $this->userAuthService->register($request);

        self::assertEquals($request->email, $result->user->email);
        self::assertTrue(password_verify($request->password, $result->user->password));
    }

    function testRegisterFailedBlankEmail()
    {
        self::expectException(ValidationException::class);
        self::expectExceptionMessageIs('Perhatian: Email atau password tidak boleh kosong');

        $request = $this->createRegisterRequest();
        $request->email = '';

        $result = $this->userAuthService->register($request);
    }

    function testRegisterFailedBlankPassword()
    {
        self::expectException(ValidationException::class);
        self::expectExceptionMessageIs('Perhatian: Email atau password tidak boleh kosong');

        $request = $this->createRegisterRequest();
        $request->password = '';

        $result = $this->userAuthService->register($request);
    }
}