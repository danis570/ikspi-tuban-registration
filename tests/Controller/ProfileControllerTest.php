<?php

namespace Danish\IkspiTubanRegistration\Controller;

use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\User;
use Danish\IkspiTubanRegistration\Domain\UserRole;
use Danish\IkspiTubanRegistration\Repository\UserRepository;
use Danish\IkspiTubanRegistration\Repository\SessionRepository;
use Danish\IkspiTubanRegistration\Repository\ProfileRepository;
use Danish\IkspiTubanRegistration\Service\SessionService;
use Danish\IkspiTubanRegistration\TestCase;
use Override;

class ProfileControllerTest extends TestCase
{
    private ProfileController $profileController;
    private UserRepository $userRepository;
    private SessionRepository $sessionRepository;
    private ProfileRepository $profileRepository;
    private SessionService $sessionService;
    private array $filesToClean = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $connection = Database::getConnection();
        $this->userRepository = new UserRepository($connection);
        $this->sessionRepository = new SessionRepository($connection);
        $this->profileRepository = new ProfileRepository($connection);
        $this->sessionService = new SessionService($this->sessionRepository, $this->userRepository);

        // Bersihkan data lama
        $this->profileRepository->deleteAll();
        $this->sessionRepository->deleteAll();
        $this->userRepository->deleteAll();

        $this->profileController = new ProfileController();
        $this->filesToClean = [];

        // Pastikan APP_ENV diatur ke 'test' agar redirect mengembalikan string
        putenv('APP_ENV=test');

        // Reset superglobals
        $_POST = [];
        $_FILES = [];
        $_COOKIE = [];
    }

    /**
     * Helper untuk membuat user dan mensimulasikan login session
     */
    private function loginAsUser(UserRole $role): User
    {
        $user = new User();
        $user->email = 'danish_test@gmail.com';
        $user->password = 'secret';
        $user->role = $role;
        $savedUser = $this->userRepository->save($user);

        $session = $this->sessionService->create($savedUser->id);

        $_COOKIE[SessionService::$cookieName] = $session->id;

        return $savedUser;
    }

    /**
     * Helper untuk membuat data $_FILES tiruan yang valid
     */
    private function prepareFakeImagePost(string $inputName): void
    {
        $tmpFilePath = tempnam(sys_get_temp_dir(), 'fake_ctrl_');
        $jpeg1x1 = base64_decode('/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wgALCAABAAEBAREA/8QAFBABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQABPxA=');
        file_put_contents($tmpFilePath, $jpeg1x1);

        $_FILES[$inputName] = [
            'name' => 'avatar_test.jpg',
            'tmp_name' => $tmpFilePath,
            'size' => filesize($tmpFilePath),
            'error' => 0,
            'type' => 'image/jpeg'
        ];
    }

    /**
     * TEST 1: Memastikan halaman render GET request (save) berjalan untuk User
     */
    public function testSavePageForUser()
    {
        $this->loginAsUser(UserRole::USER);

        $output = $this->profileController->save();

        self::assertIsString($output);
        self::assertStringContainsString('Update Profile', $output);
    }

    /**
     * TEST 2: Memastikan proses POST Save (postSave) berhasil melakukan redirect jika sukses
     */
    public function testPostSaveSuccess()
    {
        $user = $this->loginAsUser(UserRole::USER);

        // Simulasi input form $_POST dan $_FILES
        $_POST['name'] = 'Danish Member';
        $this->prepareFakeImagePost('avatar');

        // Jalankan controller
        $output = $this->profileController->postSave();

        // Karena diatur ke 'test', output harus mengembalikan 'success redirect'
        self::assertEquals('success redirect', $output);

        // Validasi ke Database apakah data benar-benar tersimpan
        $profile = $this->profileRepository->findByUserId($user->id);
        self::assertNotNull($profile);
        self::assertEquals('Danish Member', $profile->name);

        // Catat file avatar yang baru agar bisa dibersihkan di tearDown
        $this->filesToClean[] = realpath(__DIR__ . '/../../public/uploads/avatars/') . DIRECTORY_SEPARATOR . $profile->avatar;
    }

    /**
     * TEST 3: Gagal karena validasi nama kosong
     */
    public function testPostSaveFailedValidation()
    {
        $this->loginAsUser(UserRole::USER);

        $_POST['name'] = ''; // Sengaja dikosongkan untuk memicu error
        $this->prepareFakeImagePost('avatar');

        $output = $this->profileController->postSave();

        self::assertIsString($output);
        // Pastikan pesan error dari ValidationException tercetak di layar HTML View
        self::assertStringContainsString('Nama wajib di isi', $output);
    }

    #[Override]
    protected function tearDown(): void
    {
        // Bersihkan sisa file gambar hasil testing agar harddisk tidak penuh
        foreach ($this->filesToClean as $filePath) {
            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Hapus file temporary yang sempat tertinggal di $_FILES akibat test gagal di tengah jalan
        if (isset($_FILES['avatar']['tmp_name']) && file_exists($_FILES['avatar']['tmp_name'])) {
            @unlink($_FILES['avatar']['tmp_name']);
        }

        parent::tearDown();
    }
}
