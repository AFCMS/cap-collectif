<?php

namespace Capco\AppBundle\GraphQL\Resolver\Reply;

use Capco\AppBundle\Entity\AbstractReply;
use Capco\AppBundle\Entity\ReplyAnonymous;
use Capco\AppBundle\Repository\AbstractQuestionRepository;
use Capco\AppBundle\Repository\AbstractResponseRepository;
use Psr\Log\LoggerInterface;
use Capco\AppBundle\Entity\Reply;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Capco\AppBundle\GraphQL\Resolver\Traits\ResponsesResolverTrait;

class ReplyResponsesResolver implements ResolverInterface
{
    use ResponsesResolverTrait;

    private $logger;

    public function __construct(
        LoggerInterface $logger,
        AbstractQuestionRepository $abstractQuestionRepository,
        AbstractResponseRepository $abstractResponseRepository
    ) {
        $this->logger = $logger;
        $this->abstractQuestionRepository = $abstractQuestionRepository;
        $this->abstractResponseRepository = $abstractResponseRepository;
    }

    public function __invoke(AbstractReply $reply, $viewer, \ArrayObject $context): iterable
    {
        $skipVerification =
            $context &&
            $context->offsetExists('disable_acl') &&
            true === $context->offsetGet('disable_acl');

        $responses = [];

        if ($reply instanceof Reply) {
            $author = $reply->getAuthor();

            if (
                !$skipVerification &&
                $reply->getQuestionnaire()->isPrivateResult() &&
                (!$viewer || (!$viewer->isAdmin() && $viewer->getId() !== $author->getId()))
            ) {
                $this->logger->warn('Tried to access private responses on a reply.');

                return [];
            }
            $responses = $this->filterVisibleResponses(
                $this->getResponsesForReply($reply),
                $author,
                $viewer,
                $context
            );
        } elseif ($reply instanceof ReplyAnonymous) {
            $responses = $this->getResponsesForReply($reply);
        }

        $iterator = $responses->getIterator();
        $responsesArray = iterator_to_array($iterator);

        usort($responsesArray, function ($a, $b) {
            return $a->getQuestion()->getPosition() - $b->getQuestion()->getPosition();
        });

        return $responsesArray;
    }
}
