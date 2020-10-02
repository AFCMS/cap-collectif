<?php

namespace Capco\AppBundle\Enum;

class DeleteMailingListErrorCode implements EnumType
{
    public const ID_NOT_FOUND = 'ID_NOT_FOUND';
    public const NOT_DELETABLE = 'NOT_DELETABLE';
    public const EMPTY_MAILING_LISTS = 'EMPTY_MAILING_LISTS';

    public static function isValid($value): bool
    {
        return \in_array($value, self::getAvailableTypes(), true);
    }

    public static function getAvailableTypes(): array
    {
        return [self::ID_NOT_FOUND, self::NOT_DELETABLE, self::EMPTY_MAILING_LISTS];
    }

    public static function getAvailableTypesToString(): string
    {
        return implode(' | ', self::getAvailableTypes());
    }
}
