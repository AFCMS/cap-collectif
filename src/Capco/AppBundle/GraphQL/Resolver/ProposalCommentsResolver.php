<?php

namespace Capco\AppBundle\GraphQL\Resolver;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Repository\ProposalCommentRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Psr\Log\LoggerInterface;

class ProposalCommentsResolver
{
    private $logger;
    private $commentRepository;

    public function __construct(ProposalCommentRepository $repository, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->commentRepository = $repository;
    }

    public function __invoke(Proposal $proposal, Argument $args)
    {
        try {
            $paginator = new Paginator(function (int $offset, int $limit) use ($proposal) {
                return $this->commentRepository->getEnabledByProposal($proposal, $offset, $limit)->getIterator()->getArrayCopy();
            });

            $totalCount = $this->commentRepository->countCommentsAndAnswersEnabledByProposal($proposal);

            return $paginator->auto($args, $totalCount);
        } catch (\RuntimeException $exception) {
            $this->logger->error(__METHOD__ . ' : ' . $exception->getMessage());
            throw new \RuntimeException('Could not find proposals for selection step');
        }
    }
}
