<?php

namespace Danish\IkspiTubanRegistration\Exception;

use Exception;

class DataNotFoundException extends Exception
{
    public function __construct(string $message)
    {
        $formatMessage = "Data empty: $message";
        parent::__construct($formatMessage);
    }
}
