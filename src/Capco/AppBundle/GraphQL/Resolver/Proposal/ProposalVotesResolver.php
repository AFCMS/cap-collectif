<?php

namespace Capco\AppBundle\GraphQL\Resolver\Proposal;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Repository\AbstractStepRepository;
use Capco\AppBundle\Repository\ProposalCollectVoteRepository;
use Capco\AppBundle\Repository\ProposalSelectionVoteRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Psr\Log\LoggerInterface;

class ProposalVotesResolver
{
    private $logger;
    private $abstractStepRepository;
    private $proposalCollectVoteRepository;
    private $proposalSelectionVoteRepository;

    public function __construct(AbstractStepRepository $repository,
                                ProposalCollectVoteRepository $proposalCollectVoteRepository,
                                ProposalSelectionVoteRepository $proposalSelectionVoteRepository,
                                LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->abstractStepRepository = $repository;
        $this->proposalCollectVoteRepository = $proposalCollectVoteRepository;
        $this->proposalSelectionVoteRepository = $proposalSelectionVoteRepository;
    }

    public function __invoke(Proposal $proposal, Argument $args): Connection
    {
        try {
            $step = $this->abstractStepRepository->find($args['step']);
            $field = $args->offsetGet('orderBy')['field'];
            $direction = $args->offsetGet('orderBy')['direction'];
            if ($step instanceof CollectStep) {
                $paginator = new Paginator(function (int $offset, int $limit) use ($proposal, $step, $field, $direction) {
                    return $this->proposalCollectVoteRepository->getByProposalAndStep($proposal, $step, $limit, $offset, $field, $direction)->getIterator()->getArrayCopy();
                });

                $totalCount = $this->proposalCollectVoteRepository->countVotesByProposalAndStep($proposal, $step);
            } else {
                $paginator = new Paginator(function (int $offset, int $limit) use ($proposal, $step, $field, $direction) {
                    return $this->proposalSelectionVoteRepository->getByProposalAndStep($proposal, $step, $limit, $offset, $field, $direction)->getIterator()->getArrayCopy();
                });

                $totalCount = $this->proposalSelectionVoteRepository->countVotesByProposalAndStep($proposal, $step);
            }

            return $paginator->auto($args, $totalCount);
        } catch (\RuntimeException $exception) {
            $this->logger->error(__METHOD__ . ' : ' . $exception->getMessage());
            throw new \RuntimeException($exception->getMessage());
        }
    }
}
