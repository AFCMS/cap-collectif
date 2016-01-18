<?php

namespace Capco\AppBundle\Resolver;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalVote;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Repository\ProposalVoteRepository;
use Capco\UserBundle\Entity\User;

class ProposalVotesResolver
{
    protected $repository;

    public function __construct(ProposalVoteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function addVotesToProposalsForSelectionStepAndUser(array $proposals, SelectionStep $selectionStep, User $user)
    {
        $usersVotesForSelectionStep = $this
            ->repository
            ->findBy(
                [
                    'selectionStep' => $selectionStep,
                    'user' => $user,
                ]
            );
        $results = [];
        foreach ($proposals as $proposal) {
            $proposal['userHasVote'] = $this->proposalHasVote($proposal, $usersVotesForSelectionStep);
            $results[] = $proposal;
        }

        return $results;
    }

    public function proposalHasVote($proposal, $usersVotesForSelectionStep)
    {
        foreach ($usersVotesForSelectionStep as $vote) {
            if ($vote->getProposal()->getId() === $proposal['id']) {
                return true;
            }
        }

        return false;
    }

    public function voteIsPossible(ProposalVote $vote)
    {
        $selectionStep = $vote->getSelectionStep();
        $proposal = $vote->getProposal();
        $otherVotes = [];
        if ($vote->getUser()) {
            $otherVotes = $this
                ->repository
                ->findBy(
                    [
                        'selectionStep' => $selectionStep,
                        'user' => $vote->getUser(),
                    ]
                )
            ;
        } else if ($vote->getEmail()) {
            $otherVotes = $this
                ->repository
                ->findBy(
                    [
                        'selectionStep' => $selectionStep,
                        'email' => $vote->getEmail(),
                    ]
                )
            ;
        }

        $project = $selectionStep->getProject();
        if ($project && $project->getBudget()) {
            $left = $project->getBudget() - $this->getAmountSpentForVotes($otherVotes);
            return $left >= $proposal->getEstimation();
        }
        return true;
    }

    public function getAmountSpentForVotes(array $votes)
    {
        $spent = 0;
        foreach ($votes as $vote) {
            $spent += $vote->getProposal()->getEstimation();
        }
        return $spent;
    }

    public function getSpentCreditsForUser(User $user, SelectionStep $selectionStep)
    {
        $votes = $this
            ->repository
            ->findBy(
                [
                    'selectionStep' => $selectionStep,
                    'user' => $user,
                ]
            )
        ;
        return $this->getAmountSpentForVotes($votes);

    }

    public function getCreditsLeftForUser(User $user = null, SelectionStep $selectionStep)
    {
        $creditsLeft = $selectionStep->getProject()->getBudget();
        if ($creditsLeft > 0 && $user) {
            $creditsLeft -= $this
                ->getSpentCreditsForUser($user, $selectionStep)
            ;
        }
        return $creditsLeft;
    }
}
