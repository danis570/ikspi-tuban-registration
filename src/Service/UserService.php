<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Exception\DataNotFoundException;
use Danish\IkspiTubanRegistration\Exception\DeleteFailedException;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Repository\UserRepository;

class UserService
{
    private UserRepository $userRepository;
    private SessionService $sessionService;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    function getAll(): array
    {
        $users = $this->userRepository->findAll();

        if ($users === []) {
            throw new DataNotFoundException('Users');
        }

        return $users;
    }

    function deleteById(int $id): bool
    {
        $status = $this->userRepository->deleteById($id);
        if ($status == false) {
            throw new DeleteFailedException('User');
        }

        return $status;
    }
}
