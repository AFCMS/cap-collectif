<?php
namespace Capco\AppBundle\GraphQL\Mutation;

use Swarrot\Broker\Message;
use Psr\Log\LoggerInterface;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\Source;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Model\Sourceable;
use Capco\AppBundle\Form\ApiSourceType;
use Symfony\Component\Form\FormFactory;
use Doctrine\ORM\EntityManagerInterface;
use Capco\AppBundle\Entity\OpinionVersion;
use Overblog\GraphQLBundle\Error\UserError;
use Capco\AppBundle\Helper\RedisStorageHelper;
use Capco\AppBundle\Repository\SourceRepository;
use Capco\AppBundle\Repository\OpinionRepository;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Capco\AppBundle\GraphQL\Exceptions\GraphQLException;
use Capco\AppBundle\Repository\OpinionVersionRepository;
use Overblog\GraphQLBundle\Relay\Connection\Output\Edge;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\ConnectionBuilder;

class AddSourceMutation implements MutationInterface
{
    private $em;
    private $opinionRepo;
    private $versionRepo;
    private $formFactory;
    private $redisStorage;
    private $publisher;
    private $sourceRepo;
    private $logger;

    public function __construct(
        EntityManagerInterface $em,
        FormFactory $formFactory,
        OpinionRepository $opinionRepo,
        OpinionVersionRepository $versionRepo,
        RedisStorageHelper $redisStorage,
        SourceRepository $sourceRepo,
        LoggerInterface $logger
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->opinionRepo = $opinionRepo;
        $this->versionRepo = $versionRepo;
        $this->redisStorage = $redisStorage;
        $this->sourceRepo = $sourceRepo;
        $this->logger = $logger;
    }

    public function __invoke(Arg $input, User $viewer): array
    {
        $sourceableId = $input->offsetGet('sourceableId');
        $sourceable = $this->opinionRepo->find($sourceableId);

        if (!$sourceable) {
            $sourceable = $this->versionRepo->find($sourceableId);
        }

        if (!$sourceable || !$sourceable instanceof Sourceable) {
            $this->logger->error('Unknown Sourceable with id: ' . $sourceableId);
            $error = ['message' => 'Unknown Sourceable.'];

            return ['source' => null, 'sourceEdge' => null, 'userErrors' => [$error]];
        }

        if (!$sourceable->canContribute()) {
            $this->logger->error(
                'Can\'t add an source to an uncontributable sourceable with id: ' . $sourceableId
            );
            $error = ['message' => 'Can\'t add an source to an uncontributable sourceable.'];

            return ['source' => null, 'sourceEdge' => null, 'userErrors' => [$error]];
        }

        if (!$sourceable->getOpinionType()->isSourceable()) {
            $error = ['message' => 'Can\'t add an source to non-sourceable.'];

            return ['source' => null, 'sourceEdge' => null, 'userErrors' => [$error]];
        }

        $source = (new Source())
            ->setType(Source::LINK)
            ->setIsEnabled(true)
            ->setAuthor($viewer);
        if ($sourceable instanceof Opinion) {
            $source->setOpinion($sourceable);
        }
        if ($sourceable instanceof OpinionVersion) {
            $source->setOpinionVersion($sourceable);
        }

        $values = $input->getRawArguments();
        unset($values['sourceableId']);
        $form = $this->formFactory->create(ApiSourceType::class, $source);
        $form->submit($values, false);

        if (!$form->isValid()) {
            throw GraphQLException::fromFormErrors($form);
        }

        $sourceable->incrementSourcesCount();

        $this->em->persist($source);
        $this->em->flush();

        $this->redisStorage->recomputeUserCounters($viewer);

        $edge = new Edge(ConnectionBuilder::offsetToCursor(0), $source);

        return ['source' => $source, 'sourceEdge' => $edge, 'userErrors' => []];
    }
}
