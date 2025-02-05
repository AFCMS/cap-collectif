<?php

namespace Capco\AppBundle\GraphQL\Resolver\Proposal;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Repository\ProposalRepository;
use Capco\UserBundle\Entity\User;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

/**
 * @deprecated This is our legacy evaluation tool.
 */
class ProposalViewerIsAnEvaluerResolver implements ResolverInterface
{
    private $proposalRepository;

    public function __construct(ProposalRepository $proposalRepository)
    {
        $this->proposalRepository = $proposalRepository;
    }

    public function __invoke(Proposal $proposal, $viewer): bool
    {
        return $viewer instanceof User
            ? $this->proposalRepository->isViewerAnEvaluer($proposal, $viewer)
            : false;
    }
}
