<?php

namespace Danish\IkspiTubanRegistration\Domain;

class User
{
    public ?int $id = null;
    public string $email;
    public string $password;
    public UserRole $role;
}