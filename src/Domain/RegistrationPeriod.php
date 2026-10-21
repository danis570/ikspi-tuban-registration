<?php

namespace Danish\IkspiTubanRegistration\Domain;

use DateTimeImmutable;

class RegistrationPeriod
{
    public ?int $id = null;
    public RegistrationPeriodLevel $level;
    public DateTimeImmutable $date;
    public string $time;
    public string $location;
    public int $batch;
    public RegistrationPeriodType $type;
    public string $class;
    public string $region;
    public bool $isActive;
    public ?string $information = null;
}