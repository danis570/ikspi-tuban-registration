<?php

namespace Danish\IkspiTubanRegistration\Tests\Service;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\Exception\UploadFileException;
use Danish\IkspiTubanRegistration\Service\UploadRegistrationPhotoService;
use PHPUnit\Framework\TestCase;

class UploadRegistrationPhotoServiceTest extends TestCase
{
    private UploadRegistrationPhotoService $service;

    private array $temporaryFiles = [];

    private array $savedFiles = [];

    private string $targetDirectory;

    protected function setUp(): void
    {
        $this->service = new UploadRegistrationPhotoService();

        $this->targetDirectory = dirname(__DIR__, 2)
            . '/public/uploads/registrations/';
    }

    protected function tearDown(): void
    {
        foreach ($this->temporaryFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        foreach ($this->savedFiles as $fileName) {
            $path = $this->targetDirectory . $fileName;

            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function testSaveSmallPhotoSuccess(): void
    {
        $file = $this->createImage(300, 300);

        $fileName = $this->service->save($file, 'small');

        $this->savedFiles[] = $fileName;

        $this->assertFileExists(
            $this->targetDirectory . $fileName
        );

        $this->assertSame('png', pathinfo(
            $fileName,
            PATHINFO_EXTENSION
        ));
    }

    public function testSaveLargePhotoSuccess(): void
    {
        $file = $this->createImage(700, 1000);

        $fileName = $this->service->save($file, 'large');

        $this->savedFiles[] = $fileName;

        $this->assertFileExists(
            $this->targetDirectory . $fileName
        );
    }

    public function testSaveWithoutFileThrowsException(): void
    {
        $file = new UploadedFile(
            temporaryPath: '',
            originalName: '',
            typeFile: '',
            size: 0,
            errorCode: UPLOAD_ERR_NO_FILE
        );

        $this->expectException(UploadFileException::class);

        $this->service->save($file, 'small');
    }

    public function testSaveSmallPhotoExceedingMaximumSizeThrowsException(): void
    {
        $file = $this->createLargeTemporaryFile(
            1 * 1024 * 1024 + 1
        );

        $this->expectException(UploadFileException::class);

        $this->service->save($file, 'small');
    }

    public function testSaveLargePhotoExceedingMaximumSizeThrowsException(): void
    {
        $file = $this->createLargeTemporaryFile(
            2 * 1024 * 1024 + 1
        );

        $this->expectException(UploadFileException::class);

        $this->service->save($file, 'large');
    }

    public function testSaveInvalidMimeTypeThrowsException(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'registration_');

        $this->temporaryFiles[] = $path;

        file_put_contents($path, 'This is not an image.');

        $file = new UploadedFile(
            temporaryPath: $path,
            originalName: 'document.txt',
            typeFile: 'text/plain',
            size: filesize($path),
            errorCode: UPLOAD_ERR_OK
        );

        $this->expectException(UploadFileException::class);

        $this->service->save($file, 'small');
    }

    public function testSaveLargePhotoWithWrongRatioThrowsException(): void
    {
        // Rasio 1:1, padahal foto besar harus 7:10.
        $file = $this->createImage(300, 300);

        $this->expectException(UploadFileException::class);

        $this->service->save($file, 'large');
    }

    public function testSaveSmallPhotoWithWrongRatioThrowsException(): void
    {
        // Rasio 7:10, padahal foto kecil harus 1:1.
        $file = $this->createImage(700, 1000);

        $this->expectException(UploadFileException::class);

        $this->service->save($file, 'small');
    }

    public function testSaveWithInvalidPhotoTypeThrowsException(): void
    {
        $file = $this->createImage(300, 300);

        $this->expectException(UploadFileException::class);

        $this->service->save($file, 'medium');
    }

    private function createImage(
        int $width,
        int $height
    ): UploadedFile {
        if (!function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped(
                'Ekstensi GD PHP diperlukan untuk membuat gambar pengujian.'
            );
        }

        $path = tempnam(sys_get_temp_dir(), 'registration_');

        $this->temporaryFiles[] = $path;

        $image = imagecreatetruecolor($width, $height);

        try {
            imagepng($image, $path);
        } finally {
           
        }

        return new UploadedFile(
            temporaryPath: $path,
            originalName: 'test.png',
            typeFile: 'image/png',
            size: filesize($path),
            errorCode: UPLOAD_ERR_OK
        );
    }

    private function createLargeTemporaryFile(
        int $size
    ): UploadedFile {
        $path = tempnam(sys_get_temp_dir(), 'registration_');

        $this->temporaryFiles[] = $path;

        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new \RuntimeException(
                'Gagal membuat file pengujian.'
            );
        }

        try {
            $remaining = $size;
            $chunk = str_repeat('a', 8192);

            while ($remaining > 0) {
                $length = min($remaining, strlen($chunk));
                fwrite($handle, substr($chunk, 0, $length));
                $remaining -= $length;
            }
        } finally {
            fclose($handle);
        }

        return new UploadedFile(
            temporaryPath: $path,
            originalName: 'large.png',
            typeFile: 'image/png',
            size: filesize($path),
            errorCode: UPLOAD_ERR_OK
        );
    }
}
