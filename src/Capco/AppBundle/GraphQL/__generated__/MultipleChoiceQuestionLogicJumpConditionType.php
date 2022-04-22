<?php
namespace Capco\AppBundle\GraphQL\__GENERATED__;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ConfigProcessor;
use Overblog\GraphQLBundle\Definition\LazyConfig;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Definition\Type\GeneratedTypeInterface;

/**
 * THIS FILE WAS GENERATED AND SHOULD NOT BE MODIFIED!
 */
final class MultipleChoiceQuestionLogicJumpConditionType extends ObjectType implements GeneratedTypeInterface
{

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'MultipleChoiceQuestionLogicJumpCondition',
            'description' => 'A particular condition in a logic jump in a multiple choice question.',
            'fields' => function () use ($globalVariable) {
                return [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'args' => [
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["relay_globalid_field", array(0 => $value, 1 => $info, 2 => null, 3 => null)]);
                    },
                    'description' => 'The ID of an object',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'operator' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('LogicJumpConditionOperator')),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'Return the operator for this condition.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'question' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('Question')),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'Return the question which is going to be tested against the condition.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'value' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('QuestionChoice'),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'The value that the condition should meet to be fullfiled (depending on the operator).',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
            ];
            },
            'interfaces' => function () use ($globalVariable) {
                return [$globalVariable->get('typeResolver')->resolve('LogicJumpCondition')];
            },
            'isTypeOf' => null,
            'resolveField' => null,
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
