<?php

namespace Danish\IkspiTubanRegistration\Domain;

enum RegistrationDressSize: string
{
    case MEDIUM = 'M';
    case LARGE = 'L';
    case EXTRA_LARGE = 'XL';
    case DOUBLE_EXTRA_LARGE = 'XXL';
}
