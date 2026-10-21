<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\Domain\Profile;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\Profile\UserSaveProfileRequest;
use Danish\IkspiTubanRegistration\Model\Profile\UserSaveProfileResponse;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Exception;

class ProfileService
{
    private ProfileRepository $profileRepository;
    private UserRepository $userRepository;

    public function __construct(
        ProfileRepository $profileRepository,
        UserRepository $userRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->userRepository = $userRepository;
    }

    function save(int $userId, UserSaveProfileRequest $request): UserSaveProfileResponse
    {
        $user = $this->userRepository->findById($userId);
        if ($user === null) {
            throw new ValidationException('User id tidak valid');
        }

        if (trim($request->name) === '') {
            throw new ValidationException('Nama wajib di isi');
        }

        $profile = $this->profileRepository->findByUserId($userId);

        if ($profile === null) {
            $profile = $this->create($userId, $request);
        } else {
            $profile = $this->update($userId, $request, $profile);
        }

        $response = new UserSaveProfileResponse();
        $response->profile = $profile;

        return $response;
    }

    public function create(int $userId, UserSaveProfileRequest $request): Profile
    {
        $avatar = new UploadAvatarService();
        $avatarName = $avatar->save($request->avatar);

        $profile = new Profile();
        $profile->userId = $userId;
        $profile->name = $request->name;
        $profile->avatar = $avatarName;

        $result = $this->profileRepository->save($profile);
        return $profile;
    }

    public function update(
        int $userId,
        UserSaveProfileRequest $request,
        Profile $oldProfile
    ): Profile {
        $avatarService = new UploadAvatarService();

        $avatarName = $avatarService->save(
            $request->avatar,
            $oldProfile->avatar
        );

        if (
            $oldProfile->name === $request->name &&
            $oldProfile->avatar === $avatarName
        ) {
            throw new ValidationException('Tidak ada perubahan profil.');
        }

        $profile = new Profile();
        $profile->userId = $userId;
        $profile->name = $request->name;
        $profile->avatar = $avatarName;

        $this->profileRepository->update($profile);

        $baseDir = realpath(__DIR__ . '/../../public/uploads/avatars/');

        if (
            $baseDir !== false &&
            !empty($oldProfile->avatar) &&
            $oldProfile->avatar !== $avatarName
        ) {
            $oldAvatarFile = $baseDir . DIRECTORY_SEPARATOR . $oldProfile->avatar;

            if (is_file($oldAvatarFile)) {
                unlink($oldAvatarFile);
            }
        }

        return $profile;
    }

}
