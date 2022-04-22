<?php
namespace Capco\AppBundle\GraphQL\Mutation;

use Psr\Log\LoggerInterface;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionVote;
use Doctrine\ORM\EntityManagerInterface;
use Capco\AppBundle\Entity\OpinionVersion;
use Overblog\GraphQLBundle\Error\UserError;
use Doctrine\DBAL\Exception\DriverException;
use Capco\AppBundle\Entity\OpinionVersionVote;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Capco\AppBundle\Repository\OpinionRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Capco\AppBundle\Repository\OpinionVoteRepository;
use Capco\AppBundle\Repository\OpinionVersionRepository;
use Overblog\GraphQLBundle\Relay\Connection\Output\Edge;
use Capco\AppBundle\Repository\OpinionVersionVoteRepository;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\ConnectionBuilder;

class AddOpinionVoteMutation implements MutationInterface
{
    private $em;
    private $validator;
    private $opinionRepo;
    private $opinionVoteRepo;
    private $versionRepo;
    private $versionVoteRepo;
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        OpinionRepository $opinionRepo,
        OpinionVoteRepository $opinionVoteRepo,
        OpinionVersionRepository $versionRepo,
        OpinionVersionVoteRepository $versionVoteRepo,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->validator = $validator;
        $this->opinionRepo = $opinionRepo;
        $this->opinionVoteRepo = $opinionVoteRepo;
        $this->versionRepo = $versionRepo;
        $this->versionVoteRepo = $versionVoteRepo;
        $this->logger = $logger;
    }

    public function __invoke(Argument $input, User $viewer, RequestStack $requestStack): array
    {
        $contributionId = $input->offsetGet('opinionId');
        $opinion = $this->opinionRepo->find($contributionId);
        $version = $this->versionRepo->find($contributionId);

        if (!$opinion && !$version) {
            throw new UserError('Unknown opinion/version with id: ' . $contributionId);
        }

        $contribution = $opinion ?? $version;
        $step = $contribution->getStep();

        if (!$step->canContribute()) {
            throw new UserError('This step is no longer contributable.');
        }

        if (!$contribution->canContribute()) {
            throw new UserError('Uncontribuable opinion.');
        }

        $voteValue = $input->offsetGet('value');
        $previousVote = null;

        if ($contribution instanceof Opinion) {
            $previousVote = $this->opinionVoteRepo->findOneBy([
                'user' => $viewer,
                'opinion' => $contribution,
            ]);
        }
        if ($contribution instanceof OpinionVersion) {
            $previousVote = $this->versionVoteRepo->findOneBy([
                'user' => $viewer,
                'opinionVersion' => $contribution,
            ]);
        }

        $previousVoteId = null;
        if ($previousVote) {
            $typeName = $contribution instanceof Opinion ? 'OpinionVote' : 'VersionVote';
            $previousVoteId = GlobalId::toGlobalId($typeName, $previousVote->getId());
            $this->em->remove($previousVote);
            $this->em->flush();
        }

        $vote = null;
        if ($contribution instanceof Opinion) {
            $vote = (new OpinionVote())->setOpinion($contribution);
        }
        if ($contribution instanceof OpinionVersion) {
            $vote = (new OpinionVersionVote())->setOpinionVersion($contribution);
        }

        $vote->setUser($viewer)->setValue($voteValue);

        try {
            $this->em->persist($vote);
            $this->em->flush();
        } catch (DriverException $e) {
            // Updating opinion votes count failed
            throw new UserError($e->getMessage());
        }

        $edge = new Edge(ConnectionBuilder::offsetToCursor(0), $vote);

        return [
            'vote' => $vote,
            'voteEdge' => $edge,
            'viewer' => $viewer,
            'previousVoteId' => $previousVoteId,
        ];
    }
}
