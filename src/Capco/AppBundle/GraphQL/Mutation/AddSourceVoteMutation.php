<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\SourceVote;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Doctrine\DBAL\Exception\DriverException;
use Capco\AppBundle\Helper\RedisStorageHelper;
use Overblog\GraphQLBundle\Definition\Argument;
use Capco\AppBundle\Repository\SourceRepository;
use Capco\AppBundle\Repository\SourceVoteRepository;
use Overblog\GraphQLBundle\Relay\Connection\Output\Edge;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Capco\AppBundle\GraphQL\ConnectionBuilder;
use Capco\AppBundle\GraphQL\Resolver\Requirement\StepRequirementsResolver;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;

class AddSourceVoteMutation implements MutationInterface
{
    private EntityManagerInterface $em;
    private SourceRepository $sourceRepo;
    private SourceVoteRepository $sourceVoteRepo;
    private RedisStorageHelper $redisStorageHelper;
    private StepRequirementsResolver $stepRequirementsResolver;
    private Indexer $indexer;

    public function __construct(
        EntityManagerInterface $em,
        SourceRepository $sourceRepo,
        SourceVoteRepository $sourceVoteRepo,
        RedisStorageHelper $redisStorageHelper,
        StepRequirementsResolver $stepRequirementsResolver,
        Indexer $indexer
    ) {
        $this->em = $em;
        $this->sourceRepo = $sourceRepo;
        $this->sourceVoteRepo = $sourceVoteRepo;
        $this->redisStorageHelper = $redisStorageHelper;
        $this->stepRequirementsResolver = $stepRequirementsResolver;
        $this->indexer = $indexer;
    }

    public function __invoke(Argument $input, User $viewer): array
    {
        $contributionId = GlobalId::fromGlobalId($input->offsetGet('sourceId'))['id'];
        $source = $this->sourceRepo->find($contributionId);

        if (!$source) {
            throw new UserError('Unknown source with id: ' . $contributionId);
        }

        if (!$source->canContribute($viewer)) {
            throw new UserError('Can not vote for : ' . $contributionId);
        }

        $previousVote = $this->sourceVoteRepo->findOneBy([
            'user' => $viewer,
            'source' => $source,
        ]);

        if ($previousVote) {
            throw new UserError('Already voted.');
        }

        $step = $source->getStep();

        if (
            $step &&
            !$this->stepRequirementsResolver->viewerMeetsTheRequirementsResolver($viewer, $step)
        ) {
            throw new UserError('You dont meets all the requirements.');
        }

        $vote = (new SourceVote())->setSource($source)->setUser($viewer);

        try {
            $this->em->persist($vote);
            $this->em->flush();
        } catch (DriverException $e) {
            // Updating sources votes count failed
            throw new UserError($e->getMessage());
        }
        $this->redisStorageHelper->recomputeUserCounters($viewer);
        $this->indexer->index(ClassUtils::getClass($vote), $vote->getId());
        $this->indexer->finishBulk();

        $edge = new Edge(ConnectionBuilder::offsetToCursor(0), $vote);

        return [
            'voteEdge' => $edge,
            'viewer' => $viewer,
        ];
    }
}
