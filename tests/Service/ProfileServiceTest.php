<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\Model\Profile\UserSaveProfileRequest;
use Danish\IkspiTubanRegistration\Model\Profile\UserSaveProfileResponse;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class ProfileServiceTest extends TestCase
{
    private ProfileRepository $profileRepository;
    private UserRepository $userRepository;
    private ProfileService $profileService;

    private array $filesToClean = []; // Menyimpan daftar file untuk dibersihkan setelah test

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->profileRepository = new ProfileRepository(Database::getConnection());
        $this->userRepository = new UserRepository(Database::getConnection());
        $this->profileService = new ProfileService($this->profileRepository, $this->userRepository);

        $this->profileRepository->deleteAll();
        $this->userRepository->deleteAll();
        $this->filesToClean = [];
    }

    /**
     * Helper untuk membuat objek UploadedFile tiruan
     */
    private function createFakeUploadedFile(string $filename = 'danish.jpg'): UploadedFile
    {
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'fake_');
        $jpeg1x1 = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        file_put_contents($tmpFilePath, $jpeg1x1);

        $dummyImg = [
            'name' => $filename,
            'tmp_name' => $tmpFilePath,
            'size' => filesize($tmpFilePath),
            'error' => 0,
            'type' => 'image/jpeg'
        ];

        return UploadedFile::fromArray($dummyImg);
    }

    function testSaveInCreate()
    {
        $user = new User();
        $user->email = 'danish@gmail.com';
        $user->password = 'rhs';
        $user->role = UserRole::USER;
        $resultUser = $this->userRepository->save($user);

        $request = new UserSaveProfileRequest();
        $request->name = 'danish';
        $request->avatar = $this->createFakeUploadedFile('avatar_create.jpg');

        $result = $this->profileService->save($resultUser->id, $request);

        self::assertNotNull($result);
        self::assertInstanceOf(UserSaveProfileResponse::class, $result);
        self::assertEquals('danish', $result->profile->name);

        $profile = $this->profileRepository->findByUserId($resultUser->id);
        self::assertNotNull($profile);

        // Catat file avatar yang terbuat agar bisa dibersihkan di tearDown
        $this->filesToClean[] = __DIR__ . '/../../public/uploads/avatars/' . $profile->avatar;
    }

    function testSaveInUpdate()
    {
        // 1. Siapkan User awal
        $user = new User();
        $user->email = 'danish_update@gmail.com';
        $user->password = 'rhs';
        $user->role = UserRole::USER;
        $resultUser = $this->userRepository->save($user);

        // 2. JALANKAN PROSES CREATE (Simpan profil awal terlebih dahulu)
        $createRequest = new UserSaveProfileRequest();
        $createRequest->name = 'danish awal';
        $createRequest->avatar = $this->createFakeUploadedFile('avatar_awal.jpg');

        $createResult = $this->profileService->save($resultUser->id, $createRequest);
        $avatarLama = $createResult->profile->avatar; // Catat nama file lama

        // 3. JALANKAN PROSES UPDATE (Ganti data nama dan lampirkan file foto baru)
        $updateRequest = new UserSaveProfileRequest();
        $updateRequest->name = 'danish baru'; // Mengubah nama profil
        $updateRequest->avatar = $this->createFakeUploadedFile('avatar_baru.jpg'); // Mengunggah foto baru

        $updateResult = $this->profileService->save($resultUser->id, $updateRequest);

        // 4. ASSERTION (Validasi perubahan)
        self::assertNotNull($updateResult);
        self::assertEquals('danish baru', $updateResult->profile->name);
        self::assertNotEquals($avatarLama, $updateResult->profile->avatar); // Pastikan nama file gambarnya berubah/baru

        // Ambil data profil terbaru langsung dari database repository
        $profileDiDb = $this->profileRepository->findByUserId($resultUser->id);
        self::assertEquals('danish baru', $profileDiDb->name);

        // Pastikan file gambar lama di dalam folder public otomatis terhapus (lolos dari @unlink)
        $pathAvatarLama = __DIR__ . '/../../public/uploads/avatars/' . $avatarLama;
        self::assertFileDoesNotExist($pathAvatarLama);

        // Catat file avatar yang baru agar bisa dibersihkan di tearDown setelah test selesai
        $this->filesToClean[] = __DIR__ . '/../../public/uploads/avatars/' . $profileDiDb->avatar;
    }

    #[Override]
    protected function tearDown(): void
    {
        // Bersihkan seluruh file sampah hasil pengujian di folder public agar storage tetap hemat
        foreach ($this->filesToClean as $filePath) {
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
        parent::tearDown();
    }
}
