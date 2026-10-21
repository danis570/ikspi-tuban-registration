<?php

namespace Danish\IkspiTubanRegistration\Service;

use Danish\IkspiTubanRegistration\App\UploadedFile;
use Danish\IkspiTubanRegistration\Exception\UploadFileException;

class UploadAvatarService
{

    public function save(UploadedFile $file, ?string $currentAvatar = null): string
    {
        if (!$file->hasFile()) {
            if (!empty($currentAvatar)) {
                return $currentAvatar;
            }
            throw new UploadFileException('Pertama mengisi profil wajib mengisi foto dan nama');
        }

        $maxSize = 2 * 1024 * 1024;
        if ($file->size > $maxSize) {
            throw new UploadFileException('File terlalu besar (max 2mb)');
        }

        if (!$file->isSuccessful()) {
            throw new UploadFileException('File terlalu besar (server)');
        }

        $fileType = $file->getActualMimeType();
        $allowedType = ['image/jpg', 'image/png', 'image/jpeg'];

        if (!in_array($fileType, $allowedType)) {
            throw new UploadFileException('Tipe gambar tidak valid');
        }

        $targetDirectory = __DIR__ . '/../../public/uploads/avatars/';
        $extension = strtolower(pathinfo($file->originalName, PATHINFO_EXTENSION));
        $newFileName = bin2hex(random_bytes(16)) . '.' . $extension;
        $directory = $targetDirectory . $newFileName;

        if (is_uploaded_file($file->temporaryPath)) {

            $moved = move_uploaded_file($file->temporaryPath, $directory);
        } else {
            // FOR UNIT TESTS
            $moved = rename($file->temporaryPath, $directory);
        }

        if (!$moved) {
            throw new UploadFileException('Gagal memindahkan file ke direktori');
        }

        return $newFileName;
    }
}
