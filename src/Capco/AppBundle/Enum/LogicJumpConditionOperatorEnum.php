<?php
namespace Capco\AppBundle\Enum;

abstract class LogicJumpConditionOperatorEnum
{
    public const CONDITION_OPERATOR_IS = 'is';
    public const CONDITION_OPERATOR_IS_NOT = 'is_not';

    protected static $operatorName = [
        self::CONDITION_OPERATOR_IS => 'logic-jump.is',
        self::CONDITION_OPERATOR_IS_NOT => 'logic-jump.is_not',
    ];

    public static function getOperatorValue($operatorShortName): string
    {
        if (!isset(static::$operatorName[$operatorShortName])) {
            throw new \RuntimeException("Unknown operator '$operatorShortName'");
        }

        return static::$operatorName[$operatorShortName];
    }

    public static function isOperatorValueValid($operatorShortName): bool
    {
        return isset(static::$operatorName[$operatorShortName]);
    }

    public static function getAvailableOperatorValues(): array
    {
        return [self::CONDITION_OPERATOR_IS, self::CONDITION_OPERATOR_IS_NOT];
    }
}
