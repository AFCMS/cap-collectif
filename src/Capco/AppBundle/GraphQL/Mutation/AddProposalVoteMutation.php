<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalCollectVote;
use Capco\AppBundle\Entity\ProposalSelectionVote;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Repository\AbstractStepRepository;
use Capco\AppBundle\Repository\ProposalRepository;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;

class AddProposalVoteMutation
{
    private $em;
    private $proposalRepo;
    private $stepRepo;

    public function __construct(EntityManagerInterface $em, ProposalRepository $proposalRepo, AbstractStepRepository $stepRepo)
    {
        $this->em = $em;
        $this->stepRepo = $stepRepo;
        $this->proposalRepo = $proposalRepo;
    }

    public function __invoke(Argument $input, User $user, $request)
    {
        $proposal = $this->proposalRepo->find($input->offsetGet('proposalId'));
        $step = $this->stepRepo->find($input->offsetGet('stepId'));

        if (!$proposal) {
            throw new UserError('Unknown proposal with id: ' . $input->offsetGet('proposalId'));
        }
        if (!$step) {
            throw new UserError('Unknown step with id: ' . $input->offsetGet('stepId'));
        }

        $vote = null;
        $countUserVotes = 0;
        if ($step instanceof CollectStep) {
            // Check if proposal is in step
            if ($step !== $proposal->getProposalForm()->getStep()) {
                throw new UserError('This proposal is not associated to this collect step.');
            }
            $countUserVotes = $this->em
              ->getRepository('CapcoAppBundle:ProposalCollectVote')
              ->countVotesByStepAndUser($step, $user)
          ;
            $vote = (new ProposalCollectVote())
              ->setCollectStep($step);
        } elseif ($step instanceof SelectionStep) {
            $countUserVotes = $this->em
              ->getRepository('CapcoAppBundle:ProposalSelectionVote')
              ->countVotesByStepAndUser($step, $user)
          ;
            $vote = (new ProposalSelectionVote())
              ->setSelectionStep($step)
              ;
        } else {
            throw new UserError('Wrong step with id: ' . $input->offsetGet('stepId'));
        }

        // Check if step is contributable
        if (!$step->canContribute()) {
            throw new UserError('This step is no longer contributable.');
        }

        // Check if step is votable
        if (!$step->isVotable()) {
            throw new UserError('This step is not votable.');
        }

        // Check if user has reached limit of votes
        if ($step->isNumberOfVotesLimitted()) {
            if ($countUserVotes >= $step->getVotesLimit()) {
                throw new UserError('You have reached the limit of votes.');
            }
        }

        $vote
            ->setIpAddress($request->getClientIp())
            ->setUser($user)
            ->setProposal($proposal)
        ;

        $this->em->persist($vote);
        $this->em->flush();

        return ['proposal' => $proposal];
    }
}
