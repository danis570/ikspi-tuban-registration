<?php

namespace Danish\IkspiTubanRegistration\Exception;

use Exception;

class ValidationException extends Exception
{
    public function __construct(string $message)
    {
        $formatMessage = "Perhatian: $message";
        parent::__construct($formatMessage);
    }
}