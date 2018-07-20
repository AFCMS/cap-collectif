<?php
namespace Capco\AppBundle\GraphQL\__GENERATED__;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;
use Overblog\GraphQLBundle\Definition\ConfigProcessor;
use Overblog\GraphQLBundle\Definition\LazyConfig;
use Overblog\GraphQLBundle\Definition\GlobalVariables;
use Overblog\GraphQLBundle\Definition\Type\GeneratedTypeInterface;

/**
 * THIS FILE WAS GENERATED AND SHOULD NOT BE MODIFIED!
 */
final class ChangeVersionInputType extends InputObjectType implements GeneratedTypeInterface
{

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'ChangeVersionInput',
            'description' => null,
            'fields' => function () use ($globalVariable) {
                return [
                'versionId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The version Node id.',
                ],
                'body' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The contents of the version body.',
                ],
                'title' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The contents of the version title.',
                ],
                'comment' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The contents of the version comment.',
                ],
                'clientMutationId' => [
                    'type' => Type::string(),
                    'description' => null,
                ],
            ];
            },
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
