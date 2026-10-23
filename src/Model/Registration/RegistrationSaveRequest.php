<?php

namespace Danish\IkspiTubanRegistration\Model\Registration;

use Danish\IkspiTubanRegistration\App\UploadedFile;

class RegistrationSaveRequest
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

    public ?string $rt = null;
    public ?string $rw = null;

    public ?string $occupation = null;
    public ?string $trainingLocation = null;

    public ?string $fatherName = null;
    public ?string $motherName = null;
    public ?string $coachName = null;
    public ?string $coachAddress = null;

    public ?UploadedFile $smallPhoto = null;
    public ?UploadedFile $largePhoto = null;
    public string $dressSize;
}
