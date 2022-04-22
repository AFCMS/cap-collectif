<?php
namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\Follower;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Repository\FollowerRepository;
use Capco\AppBundle\Repository\ProposalRepository;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;

class UnfollowProposalMutation
{
    private $em;
    private $proposalRepository;
    private $followerRepository;

    public function __construct(
        EntityManagerInterface $em,
        ProposalRepository $proposalRepository,
        FollowerRepository $followerRepository
    ) {
        $this->em = $em;
        $this->proposalRepository = $proposalRepository;
        $this->followerRepository = $followerRepository;
    }

    public function __invoke(Argument $args, User $user): array
    {
        $proposal = '';
        if (isset($args['proposalId'])) {
            /** @var Proposal $proposal */
            $proposal = $this->proposalRepository->find($args['proposalId']);
            $this->unfollowAProposal($proposal, $user);
        }

        if (isset($args['ids'])) {
            foreach ($args['ids'] as $proposalId) {
                /** @var Proposal $proposal */
                $proposal = $this->proposalRepository->find($proposalId);
                $this->unfollowAProposal($proposal, $user);
            }
        }

        $this->em->flush();

        return ['proposal' => $proposal, 'unfollowerId' => $user->getId()];
    }

    protected function unfollowAProposal(Proposal $proposal, User $user)
    {
        /** @var Follower $follower */
        $follower = $this->followerRepository->findOneBy([
            'user' => $user,
            'proposal' => $proposal,
        ]);

        if (!$follower) {
            throw new UserError('Cant find the follower');
        }

        $this->em->remove($follower);
    }
}
