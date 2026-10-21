<?php

namespace Danish\IkspiTubanRegistration\Model\User;

class UserChangePasswordRequest
{
    public string $oldPassword;
    public string $newPassword;
    public string $confirmPassword;
}
