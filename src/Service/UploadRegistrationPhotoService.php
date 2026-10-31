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

        $maxSize = match ($photoType) {
            self::SMALL_PHOTO => 25 * 1024 * 1024,
            self::LARGE_PHOTO => 30 * 1024 * 1024,
            default => throw new UploadFileException(
                'Jenis foto tidak valid'
            ),
        };

        if ($file->size > $maxSize) {
            throw new UploadFileException(
                'Ukuran foto melebihi batas maksimal, maksimal foto kecil 25mb foto besar 30mb'
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