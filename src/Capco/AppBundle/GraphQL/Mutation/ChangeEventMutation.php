<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\DBAL\Enum\EventReviewStatusType;
use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\AppBundle\Entity\Event;
use Capco\AppBundle\GraphQL\Mutation\Locale\LocaleUtils;
use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;
use Capco\AppBundle\Security\EventVoter;
use Capco\UserBundle\Entity\User;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Swarrot\Broker\Message;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ChangeEventMutation implements MutationInterface
{
    private EntityManagerInterface $em;
    private GlobalIdResolver $globalIdResolver;
    private FormFactoryInterface $formFactory;
    private Indexer $indexer;
    private Publisher $publisher;
    private AuthorizationCheckerInterface $authorizationChecker;
    private AddEventMutation $addEventMutation;

    public function __construct(
        GlobalIdResolver $globalIdResolver,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        AddEventMutation $addEventMutation,
        Indexer $indexer,
        Publisher $publisher,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->globalIdResolver = $globalIdResolver;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->indexer = $indexer;
        $this->publisher = $publisher;
        $this->authorizationChecker = $authorizationChecker;
        $this->addEventMutation = $addEventMutation;
    }

    public function __invoke(Arg $input, User $viewer): array
    {
        $values = $input->getArrayCopy();
        if (isset($values['customCode']) && !empty($values['customCode']) && !$viewer->isAdmin()) {
            return [
                'event' => null,
                'userErrors' => [['message' => 'You are not authorized to add customCode field.']],
            ];
        }

        /** @var Event $event */
        $event = $this->globalIdResolver->resolve($values['id'], $viewer);

        unset($values['id']);
        LocaleUtils::indexTranslations($values);

        /** @var User $newAuthor */
        $newAuthor = isset($values['author'])
            ? $this->globalIdResolver->resolve($values['author'], $viewer)
            : null;

        // admin and superAdmin can change the event's author
        if ($newAuthor && $viewer->isAdmin() && $newAuthor !== $event->getAuthor()) {
            $event->setAuthor($newAuthor);
        }

        // a user want to edit his refused event
        if (
            !$viewer->isAdmin() &&
            EventReviewStatusType::REFUSED === $event->getStatus() &&
            $event->getReview()
        ) {
            $event->getReview()->setStatus(EventReviewStatusType::AWAITING);
        }

        $this->addEventMutation->submitEventFormData($event, $values, $this->formFactory);

        $this->em->flush();

        $this->indexer->index(ClassUtils::getClass($event), $event->getId());
        $this->indexer->finishBulk();

        if (!$viewer->isProjectAdmin()) {
            $this->publisher->publish(
                'event.update',
                new Message(
                    json_encode([
                        'eventId' => $event->getId(),
                    ])
                )
            );
        }

        return ['event' => $event, 'userErrors' => []];
    }

    public function isGranted(string $eventId, User $viewer): bool
    {
        $event = $this->globalIdResolver->resolve($eventId, $viewer);

        if (!$event) {
            return false;
        }

        return $this->authorizationChecker->isGranted(EventVoter::EDIT, $event);
    }
}
