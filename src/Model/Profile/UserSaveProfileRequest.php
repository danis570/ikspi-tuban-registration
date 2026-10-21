<?php

namespace Danish\IkspiTubanRegistration\Model\Profile;

use Danish\IkspiTubanRegistration\App\UploadedFile;

class UserSaveProfileRequest
{
    public string $name = '';
    public ?UploadedFile $avatar = null;
}
