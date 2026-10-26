<?php

namespace Danish\IkspiTubanRegistration\Tests\Service;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\Config\Database;
use Danish\IkspiTubanRegistration\Domain\Registration;
use Danish\IkspiTubanRegistration\Domain\RegistrationDressSize;
use Danish\IkspiTubanRegistration\Exception\ValidationException;
use Danish\IkspiTubanRegistration\Model\Registration\RegistrationSaveRequest;
use Danish\IkspiTubanRegistration\Repository\AdministrativeAreaRepository;
use Danish\IkspiTubanRegistration\Repository\RegistrationRepository;
use Danish\IkspiTubanRegistration\Service\RegistrationService;
use Danish\IkspiTubanRegistration\Service\UploadRegistrationPhotoService;
use Danish\IkspiTubanRegistration\TestCase;
use RuntimeException;

class RegistrationServiceTest extends TestCase
{
    private RegistrationService $service;

    private RegistrationRepository $registrationRepository;

    private UploadRegistrationPhotoService $photoService;

    private string $targetDirectory;

    private array $createdFiles = [];

    private bool $directoryCreated = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registrationRepository = $this->createMock(
            RegistrationRepository::class
        );

        $this->photoService = $this->createMock(
            UploadRegistrationPhotoService::class
        );

        $administration = new AdministrativeAreaRepository(
            Database::getConnection()
        );

        $this->service = new RegistrationService(
            $this->registrationRepository,
            $this->photoService,
            $administration
        );

        $this->targetDirectory = dirname(__DIR__, 2)
            . '/public/uploads/registrations/';

        $this->createdFiles = [];
        $this->directoryCreated = false;

        if (!is_dir($this->targetDirectory)) {
            if (
                !mkdir($this->targetDirectory, 0777, true)
                && !is_dir($this->targetDirectory)
            ) {
                throw new RuntimeException(
                    'Gagal membuat direktori test.'
                );
            }

            $this->directoryCreated = true;
        }
    }

    protected function tearDown(): void
    {
        foreach (array_unique($this->createdFiles) as $fileName) {
            $path = $this->targetDirectory . $fileName;

            if (is_file($path)) {
                unlink($path);
            }
        }

        if (
            $this->directoryCreated
            && is_dir($this->targetDirectory)
        ) {
            rmdir($this->targetDirectory);
        }

        parent::tearDown();
    }

    public function testCreateRegistrationSuccess(): void
    {
        $request = $this->createRequest();

        $this->photoService
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(
                function (UploadedFile $file, string $type): string {
                    return match ($type) {
                        'small' => 'small-new.png',
                        'large' => 'large-new.png',
                        default => throw new RuntimeException(
                            'Jenis foto tidak valid.'
                        ),
                    };
                }
            );

        $this->registrationRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(
                function (Registration $registration): bool {
                    return $registration->smallPhoto === 'small-new.png'
                        && $registration->largePhoto === 'large-new.png';
                }
            ))
            ->willReturnCallback(
                fn(
                Registration $registration
            ): Registration => $registration
            );

        $result = $this->service->save($request);

        $this->assertInstanceOf(Registration::class, $result);
        $this->assertSame('small-new.png', $result->smallPhoto);
        $this->assertSame('large-new.png', $result->largePhoto);
    }

    public function testCreateRegistrationCleansSmallPhotoWhenLargePhotoFails(): void
    {
        $request = $this->createRequest();

        $this->createPhysicalFile('small-new.png');

        $this->photoService
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(
                function (UploadedFile $file, string $type): string {
                    return match ($type) {
                        'small' => 'small-new.png',
                        'large' => throw new RuntimeException(
                            'Gagal menyimpan foto besar.'
                        ),
                        default => throw new RuntimeException(
                            'Jenis foto tidak valid.'
                        ),
                    };
                }
            );

        $this->registrationRepository
            ->expects($this->never())
            ->method('save');

        try {
            $this->service->save($request);

            $this->fail(
                'RuntimeException seharusnya dilempar.'
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Gagal menyimpan foto besar.',
                $exception->getMessage()
            );
        }

        $this->assertFileDoesNotExist(
            $this->targetDirectory . 'small-new.png'
        );
    }

    public function testCreateRegistrationCleansPhotosWhenRepositoryFails(): void
    {
        $request = $this->createRequest();

        $this->createPhysicalFile('small-new.png');
        $this->createPhysicalFile('large-new.png');

        $this->photoService
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(
                function (UploadedFile $file, string $type): string {
                    return match ($type) {
                        'small' => 'small-new.png',
                        'large' => 'large-new.png',
                        default => throw new RuntimeException(
                            'Jenis foto tidak valid.'
                        ),
                    };
                }
            );

        $this->registrationRepository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(
                new RuntimeException('Database gagal.')
            );

        try {
            $this->service->save($request);

            $this->fail(
                'RuntimeException seharusnya dilempar.'
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Database gagal.',
                $exception->getMessage()
            );
        }

        $this->assertFileDoesNotExist(
            $this->targetDirectory . 'small-new.png'
        );

        $this->assertFileDoesNotExist(
            $this->targetDirectory . 'large-new.png'
        );
    }

    public function testUpdateRegistrationNotFoundThrowsException(): void
    {
        $request = $this->createRequest();
        $request->id = 99;

        $this->registrationRepository
            ->expects($this->once())
            ->method('findById')
            ->with(99)
            ->willReturn(null);

        $this->photoService
            ->expects($this->never())
            ->method('save');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Data pendaftaran tidak ditemukan.'
        );

        $this->service->save($request);
    }

    public function testUpdateRegistrationSuccessDeletesOldPhotos(): void
    {
        $request = $this->createRequest();
        $request->id = 10;

        $current = $this->createExistingRegistration();

        $this->createPhysicalFile('old-small.png');
        $this->createPhysicalFile('old-large.png');
        $this->createPhysicalFile('small-new.png');
        $this->createPhysicalFile('large-new.png');

        $this->registrationRepository
            ->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($current);

        $this->photoService
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(
                function (UploadedFile $file, string $type): string {
                    return match ($type) {
                        'small' => 'small-new.png',
                        'large' => 'large-new.png',
                        default => throw new RuntimeException(
                            'Jenis foto tidak valid.'
                        ),
                    };
                }
            );

        $this->registrationRepository
            ->expects($this->once())
            ->method('update')
            ->willReturn(true);

        $result = $this->service->save($request);

        $this->assertSame(10, $result->id);
        $this->assertSame('small-new.png', $result->smallPhoto);
        $this->assertSame('large-new.png', $result->largePhoto);

        $this->assertFileDoesNotExist(
            $this->targetDirectory . 'old-small.png'
        );

        $this->assertFileDoesNotExist(
            $this->targetDirectory . 'old-large.png'
        );

        $this->assertFileExists(
            $this->targetDirectory . 'small-new.png'
        );

        $this->assertFileExists(
            $this->targetDirectory . 'large-new.png'
        );
    }

    public function testUpdateRegistrationFailurePreservesOldPhotos(): void
    {
        $request = $this->createRequest();
        $request->id = 10;

        $current = $this->createExistingRegistration();

        $this->createPhysicalFile('old-small.png');
        $this->createPhysicalFile('old-large.png');
        $this->createPhysicalFile('small-new.png');
        $this->createPhysicalFile('large-new.png');

        $this->registrationRepository
            ->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($current);

        $this->photoService
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturnCallback(
                function (UploadedFile $file, string $type): string {
                    return match ($type) {
                        'small' => 'small-new.png',
                        'large' => 'large-new.png',
                        default => throw new RuntimeException(
                            'Jenis foto tidak valid.'
                        ),
                    };
                }
            );

        $this->registrationRepository
            ->expects($this->once())
            ->method('update')
            ->willReturn(false);

        try {
            $this->service->save($request);

            $this->fail(
                'RuntimeException seharusnya dilempar.'
            );
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Tidak ada data yang diperbarui.',
                $exception->getMessage()
            );
        }

        $this->assertFileExists(
            $this->targetDirectory . 'old-small.png'
        );

        $this->assertFileExists(
            $this->targetDirectory . 'old-large.png'
        );

        $this->assertFileDoesNotExist(
            $this->targetDirectory . 'small-new.png'
        );

        $this->assertFileDoesNotExist(
            $this->targetDirectory . 'large-new.png'
        );
    }

    public function testCreateRegistrationWithEmptyNameThrowsException(): void
    {
        $request = $this->createRequest();
        $request->fullName = '   ';

        $this->photoService
            ->expects($this->never())
            ->method('save');

        $this->registrationRepository
            ->expects($this->never())
            ->method('save');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(
            'Semua kolom wajib diisi kecuali alamat spesifik.'
        );

        $this->service->save($request);
    }

    private function createRequest(): RegistrationSaveRequest
    {
        $request = new RegistrationSaveRequest();

        $request->userId = 1;
        $request->registrationPeriodId = 1;

        $request->fullName = 'Ahmad Danish';
        $request->birthPlace = 'Tuban';
        $request->birthDate = '2005-01-01';

        $request->provinceCode = '35';
        $request->regencyCode = '35.23';
        $request->districtCode = '35.23.01';
        $request->villageCode = '35.23.01.2001';

        $request->rt = '001';
        $request->rw = '002';

        $request->occupation = 'Mahasiswa';
        $request->trainingLocation = 'Palang';

        $request->fatherName = 'Nama Ayah';
        $request->motherName = 'Nama Ibu';
        $request->coachName = 'Nama Pelatih';
        $request->coachAddress = 'Tuban';

        // Property wajib bertipe string.
        $request->dressSize = 'M';

        $request->smallPhoto = $this->createUploadedFile();
        $request->largePhoto = $this->createUploadedFile();

        return $request;
    }

    private function createUploadedFile(): UploadedFile
    {
        return new UploadedFile(
            temporaryPath: '/tmp/test-photo.png',
            originalName: 'test.png',
            typeFile: 'image/png',
            size: 100,
            errorCode: UPLOAD_ERR_OK
        );
    }

    private function createExistingRegistration(): Registration
    {
        $registration = new Registration();

        $registration->id = 10;
        $registration->userId = 1;
        $registration->registrationPeriodId = 1;

        $registration->fullName = 'Ahmad Danish';
        $registration->birthPlace = 'Tuban';
        $registration->birthDate = '2005-01-01';

        $registration->provinceCode = '35';
        $registration->regencyCode = '35.23';
        $registration->districtCode = '35.23.01';
        $registration->villageCode = '35.23.01.2001';

        $registration->rt = '001';
        $registration->rw = '002';

        $registration->occupation = 'Mahasiswa';
        $registration->trainingLocation = 'Palang';

        $registration->fatherName = 'Nama Ayah';
        $registration->motherName = 'Nama Ibu';
        $registration->coachName = 'Nama Pelatih';
        $registration->coachAddress = 'Tuban';

        $registration->dressSize = RegistrationDressSize::MEDIUM;

        $registration->smallPhoto = 'old-small.png';
        $registration->largePhoto = 'old-large.png';

        return $registration;
    }

    private function createPhysicalFile(string $fileName): void
    {
        $path = $this->targetDirectory . $fileName;

        if (file_put_contents($path, 'test file') === false) {
            throw new RuntimeException(
                'Gagal membuat file test: ' . $fileName
            );
        }

        $this->createdFiles[] = $fileName;
    }
}