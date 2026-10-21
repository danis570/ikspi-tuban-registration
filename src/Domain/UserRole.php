<?php

namespace Danish\IkspiTubanRegistration\Domain;

enum UserRole: string
{
    case USER = 'user';
    case ADMIN = 'admin';
}