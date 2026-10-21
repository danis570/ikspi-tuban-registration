<?php

namespace Danish\IkspiTubanRegistration\Exception;

use Exception;

class UploadFileException extends Exception
{
    public function __construct(string $message)
    {
        $format = 'Upload file: ' . $message;
        parent::__construct($format);
    }
}
