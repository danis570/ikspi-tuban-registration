<?php

namespace Danish\IkspiTubanRegistration\Domain;

enum RegistrationPeriodType: string
{
    case LONG_DISTANCE = 'JARAK JAUH';
    case SHORT_DISTANCE = 'JARAK DEKAT';
}