<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\Exception\UploadFileException;

class UploadRegistrationPhotoService
{
    private const SMALL_PHOTO = 'small';
    private const LARGE_PHOTO = 'large';

    public function save(
        UploadedFile $file,
        string $photoType
    ): string {
        $rules = match ($photoType) {
            self::SMALL_PHOTO => [
                'max_size' => 1 * 1024 * 1024,
                'ratio' => 1 / 1,
                'extension' => 'jpg',
            ],
            self::LARGE_PHOTO => [
                'max_size' => 2 * 1024 * 1024,
                'ratio' => 7 / 10,
                'extension' => 'jpg',
            ],
            default => throw new UploadFileException(
                'Jenis foto tidak valid'
            ),
        };

        if (!$file->hasFile()) {
            throw new UploadFileException(
                'Foto wajib diunggah'
            );
        }

        if (!$file->isSuccessful()) {
            throw new UploadFileException(
                'Upload foto gagal'
            );
        }

        if ($file->size > $rules['max_size']) {
            throw new UploadFileException(
                'Ukuran foto melebihi batas maksimal'
            );
        }

        $mimeType = $file->getActualMimeType();

        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        if (!isset($extensions[$mimeType])) {
            throw new UploadFileException(
                'Format foto harus JPG, JPEG, atau PNG'
            );
        }

        $imageInfo = getimagesize($file->temporaryPath);

        if ($imageInfo === false) {
            throw new UploadFileException(
                'File bukan gambar yang valid'
            );
        }

        [$width, $height] = $imageInfo;

        $actualRatio = $width / $height;
        $expectedRatio = $rules['ratio'];

        // Toleransi kecil untuk perbedaan pembulatan rasio.
        if (abs($actualRatio - $expectedRatio) > 0.01) {
            throw new UploadFileException(
                $photoType === self::SMALL_PHOTO
                ? 'Rasio foto kecil harus 1:1 (persegi)'
                : 'Rasio foto besar harus 7:10 (portrait)'
            );
        }

        $targetDirectory = __DIR__
            . '/../../public/uploads/registrations/';

        if (
            !is_dir($targetDirectory)
            && !mkdir($targetDirectory, 0755, true)
            && !is_dir($targetDirectory)
        ) {
            throw new UploadFileException(
                'Direktori penyimpanan foto tidak tersedia'
            );
        }

        $extension = $extensions[$mimeType];
        $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $targetPath = $targetDirectory . $fileName;

        if (is_uploaded_file($file->temporaryPath)) {
            $moved = move_uploaded_file(
                $file->temporaryPath,
                $targetPath
            );
        } else {
            // Untuk kebutuhan unit test.
            $moved = rename(
                $file->temporaryPath,
                $targetPath
            );
        }

        if (!$moved) {
            throw new UploadFileException(
                'Gagal menyimpan foto'
            );
        }

        return $fileName;
    }
}
