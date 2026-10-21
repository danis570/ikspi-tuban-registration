<?php

namespace Danish\IkspiTubanRegistration\Exception;

use Exception;

class DeleteFailedException extends Exception
{
    public function __construct(string $message)
    {
        $formatMessage = "Delete error: $message";
        parent::__construct($formatMessage);
    }
}
