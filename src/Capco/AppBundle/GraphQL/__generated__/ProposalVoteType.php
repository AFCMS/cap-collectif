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
final class ProposalVoteType extends ObjectType implements GeneratedTypeInterface
{

    public function __construct(ConfigProcessor $configProcessor, GlobalVariables $globalVariables = null)
    {
        $configLoader = function(GlobalVariables $globalVariable) {
            return [
            'name' => 'ProposalVote',
            'description' => 'Fetches an object given its ID',
            'fields' => function () use ($globalVariable) {
                return [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'The ID of an object',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'createdAt' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('DateTime')),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'Date of vote.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'author' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('User'),
                    'args' => [
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return (($value->isPrivate()) ? (null) : ($value->getUser()));
                    },
                    'description' => 'The author of the contribution. If null, the vote is anonymous.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'related' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('Contribution'),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'The contribution that was voted.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'kind' => [
                    'type' => Type::nonNull(Type::string()),
                    'args' => [
                    ],
                    'resolve' => function () use ($globalVariable) {
                        return 'proposalVote';
                    },
                    'description' => 'Returns \'proposalVote\'.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'published' => [
                    'type' => Type::nonNull(Type::boolean()),
                    'args' => [
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $value->isPublished();
                    },
                    'description' => '`true` if the object is published.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'publishableUntil' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('DateTime'),
                    'args' => [
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $value->getPublishableUntil();
                    },
                    'description' => 'Identifies when the entity can no more be published.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'publishedAt' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('DateTime'),
                    'args' => [
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $value->getPublishedAt();
                    },
                    'description' => 'Identifies when the entity was published at.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'notPublishedReason' => [
                    'type' => $globalVariable->get('typeResolver')->resolve('NotPublishedReason'),
                    'args' => [
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $globalVariable->get('resolverResolver')->resolve(["Capco\\AppBundle\\GraphQL\\Resolver\\Publishable\\PublishableNotPublishedReasonResolver", array(0 => $value)]);
                    },
                    'description' => 'Reason that the entity is not published.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'private' => [
                    'type' => Type::boolean(),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'Anonymous or not.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'anonymous' => [
                    'type' => Type::nonNull(Type::boolean()),
                    'args' => [
                    ],
                    'resolve' => function ($value, $args, $context, ResolveInfo $info) use ($globalVariable) {
                        return $value->isPrivate();
                    },
                    'description' => 'Is the vote anonymous ?',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'proposal' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('Proposal')),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'Return the proposal.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
                'step' => [
                    'type' => Type::nonNull($globalVariable->get('typeResolver')->resolve('ProposalStep')),
                    'args' => [
                    ],
                    'resolve' => null,
                    'description' => 'Return the step.',
                    'deprecationReason' => null,
                    'complexity' => null,
                    # public and access are custom options managed only by the bundle
                    'public' => null,
                    'access' => null,
                ],
            ];
            },
            'interfaces' => function () use ($globalVariable) {
                return [$globalVariable->get('typeResolver')->resolve('Vote'), $globalVariable->get('typeResolver')->resolve('Publishable'), $globalVariable->get('typeResolver')->resolve('PrivatableVote')];
            },
            'isTypeOf' => null,
            'resolveField' => null,
        ];
        };
        $config = $configProcessor->process(LazyConfig::create($configLoader, $globalVariables))->load();
        parent::__construct($config);
    }
}
