<?php

namespace Danish\IkspiTubanRegistration\Domain;

class Registration
{
    public ?int $id = null;
    public int $userId;
    public int $registrationPeriodId;

    public string $fullName;
    public string $birthPlace;
    public string $birthDate;

    public string $provinceCode;
    public string $regencyCode;
    public string $districtCode;
    public string $villageCode;

    public string $rt;
    public string $rw;
    public ?string $specificAddress = null;

    public string $occupation;
    public string $trainingLocation;

    public string $fatherName;
    public string $motherName;

    public string $coachName;
    public string $coachAddress;

    public ?string $smallPhoto = null;
    public ?string $largePhoto = null;
    public RegistrationDressSize $dressSize;
}