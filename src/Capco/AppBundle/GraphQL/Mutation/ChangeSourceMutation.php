<?php
namespace Capco\AppBundle\GraphQL\Mutation;

use Swarrot\Broker\Message;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Form\ApiSourceType;
use Symfony\Component\Form\FormFactory;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Capco\AppBundle\Helper\RedisStorageHelper;
use Capco\AppBundle\Repository\SourceRepository;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Capco\AppBundle\GraphQL\Exceptions\GraphQLException;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class ChangeSourceMutation implements MutationInterface
{
    private $em;
    private $sourceRepo;
    private $formFactory;
    private $redisStorage;

    public function __construct(
        EntityManagerInterface $em,
        FormFactory $formFactory,
        SourceRepository $sourceRepo,
        RedisStorageHelper $redisStorage
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->sourceRepo = $sourceRepo;
        $this->redisStorage = $redisStorage;
    }

    public function __invoke(Arg $input, User $user): array
    {
        $sourceId = $input->offsetGet('sourceId');
        $source = $this->sourceRepo->find($sourceId);

        if (!$source) {
            throw new UserError('Unknown source with id: ' . $sourceId);
        }

        if ($user !== $source->getAuthor()) {
            throw new UserError("Can't update the source of someone else.");
        }

        if (!$source->canContribute()) {
            throw new UserError("Can't update uncontributable source.");
        }

        $values = $input->getRawArguments();
        unset($values['sourceId']);

        $form = $this->formFactory->create(ApiSourceType::class, $source);
        $form->submit($values, false);

        if (!$form->isValid()) {
            throw GraphQLException::fromFormErrors($form);
        }

        $source->setValidated(false);
        $source->resetVotes();

        $this->em->flush();

        return ['source' => $source];
    }
}
