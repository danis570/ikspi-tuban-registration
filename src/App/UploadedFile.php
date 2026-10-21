<?php

namespace Danish\IkspiTubanRegistration\App;

class UploadedFile
{
    public function __construct(
        public readonly string $temporaryPath,
        public readonly string $originalName,
        public readonly string $typeFile,
        public readonly int $size,
        public readonly int $errorCode
    ) {
    }

    public static function fromArray(array $file): self
    {
        return new self(
            temporaryPath: $file['tmp_name'] ?? '',
            originalName: $file['name'] ?? '',
            typeFile: $file['type'] ?? '',
            size: (int) ($file['size'] ?? 0),
            errorCode: (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)
        );
    }

    public function hasFile(): bool
    {
        return $this->errorCode !== UPLOAD_ERR_NO_FILE;
    }

    public function isSuccessful(): bool
    {
        return $this->errorCode === UPLOAD_ERR_OK
            && $this->temporaryPath !== '';
    }

    public function getActualMimeType(): string
    {
        if (!$this->isSuccessful() || !file_exists($this->temporaryPath)) {
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $this->temporaryPath);

        return $mimeType;
    }
}
