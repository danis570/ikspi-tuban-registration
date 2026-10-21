<?php

namespace Danish\IkspiTubanRegistration\Model\RegistrationPeriod;

class AdminSaveRegistrationPeriodRequest
{
    public ?int $id = null;
    public ?string $level = null;
    public ?string $date = null;
    public ?string $time = null;
    public ?string $location = null;
    public ?int $batch = null;
    public ?string $type = null;
    public ?string $class = null;
    public ?string $region = null;
    public ?bool $isActive = null;
    public ?string $information = null;
}
