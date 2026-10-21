<?php

namespace Danish\IkspiTubanRegistration\Domain;

class Profile
{
    public ?int $id = null;
    public int $userId;
    public string $name;
    public string $avatar;
}
