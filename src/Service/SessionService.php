<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Domain\Session;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Model\Session\UserAddSessionRequest;
use Danish\IkspiTubanRegistration\Model\Session\UserAddSessionResponse;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;

class SessionService
{
    public static string $cookieName = 'CURRENT-SESSION';
    private SessionRepository $sessionRepository;
    private UserRepository $userRepository;

    public function __construct(SessionRepository $sessionRepository, UserRepository $userRepository)
    {
        $this->sessionRepository = $sessionRepository;
        $this->userRepository = $userRepository;
    }

    public function create(int $userId): Session
    {
        $session = new Session();
        $session->id = bin2hex(random_bytes(32));
        $session->userId = $userId;

        $result = $this->sessionRepository->save($session);
        setcookie(self::$cookieName, $result->id, time() + (60 * 60 * 24 * 30), '/');
        return $result;
    }

    public function getCurrentUser(): ?User
    {
        $sessionId = $_COOKIE[self::$cookieName] ?? '';

        if ($sessionId === '') {
            return null;
        }

        $session = $this->sessionRepository->findById($sessionId);


        if ($session == null) {
            return null;
        }

        return $this->userRepository->findById($session->userId);
    }

    public function destroy()
    {
        $sessionId = $_COOKIE[self::$cookieName] ?? '';
        $this->sessionRepository->deleteById($sessionId);
        setcookie(self::$cookieName, '', 1, '/');
        unset($_COOKIE[self::$cookieName]);
    }
}