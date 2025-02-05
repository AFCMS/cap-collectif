<?php

namespace Capco\AppBundle\Enum;

class UpdateUserEmailErrorCode implements EnumType
{
    public const SPECIFY_PASSWORD = 'SPECIFY_PASSWORD';
    public const ALREADY_USED_EMAIL = 'ALREADY_USED_EMAIL';
    public const UNAUTHORIZED_EMAIL_DOMAIN = 'UNAUTHORIZED_EMAIL_DOMAIN';

    public static function isValid($value): bool
    {
        return \in_array($value, self::getAvailableTypes(), true);
    }

    public static function getAvailableTypes(): array
    {
        return [self::SPECIFY_PASSWORD, self::ALREADY_USED_EMAIL, self::UNAUTHORIZED_EMAIL_DOMAIN];
    }

    public static function getAvailableTypesToString(): string
    {
        return implode(' | ', self::getAvailableTypes());
    }
}
