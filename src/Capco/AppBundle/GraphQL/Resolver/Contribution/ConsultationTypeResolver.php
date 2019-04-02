<?php

namespace Capco\AppBundle\GraphQL\Resolver\Contribution;

use Capco\AppBundle\Entity\Answer;
use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\Entity\Comment;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionVersion;
use Capco\AppBundle\Entity\OpinionVersionVote;
use Capco\AppBundle\Entity\OpinionVote;
use Capco\AppBundle\Entity\Post;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\Reply;
use Capco\AppBundle\Entity\Reporting;
use Capco\AppBundle\Entity\Source;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Resolver\TypeResolver;

class ConsultationTypeResolver implements ResolverInterface
{
    private $typeResolver;

    public function __construct(TypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    public function __invoke($data)
    {
        $currentSchemaName = $this->typeResolver->getCurrentSchemaName();

        if ($data instanceof Opinion) {
            return $this->typeResolver->resolve('Opinion');
        }
        if ($data instanceof OpinionVote) {
            return $this->typeResolver->resolve('OpinionVote');
        }
        if ($data instanceof OpinionVersion) {
            return $this->typeResolver->resolve('Version');
        }
        if ($data instanceof OpinionVersionVote) {
            return $this->typeResolver->resolve('VersionVote');
        }
        if ($data instanceof Argument) {
            return $this->typeResolver->resolve('Argument');
        }
        if ($data instanceof Source) {
            return $this->typeResolver->resolve('Source');
        }
        if ($data instanceof Reporting) {
            return $this->typeResolver->resolve('Reporting');
        }
        if ($data instanceof Comment) {
            return $this->typeResolver->resolve('Comment');
        }
        if ($data instanceof Proposal) {
            if ('preview' === $currentSchemaName) {
                return $this->typeResolver->resolve('PreviewProposal');
            }

            return $this->typeResolver->resolve('InternalProposal');
        }
        if ($data instanceof Reply) {
            return $this->typeResolver->resolve('InternalReply');
        }
        if ($data instanceof Answer) {
            return $this->typeResolver->resolve('Answer');
        }
        if ($data instanceof Post) {
            return $this->typeResolver->resolve('Post');
        }

        throw new UserError('Could not resolve type of Contribution.');
    }
}
