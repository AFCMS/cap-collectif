<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\CapcoAppBundleMessagesTypes;
use Capco\AppBundle\DBAL\Enum\ProposalRevisionStateType;
use Capco\AppBundle\Elasticsearch\Indexer;
use Capco\AppBundle\Entity\Follower;
use Capco\AppBundle\Entity\Interfaces\FollowerNotifiedOfInterface;
use Capco\AppBundle\Entity\Interfaces\Trashable;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Entity\ProposalRevision;
use Capco\AppBundle\Entity\ProposalSocialNetworks;
use Capco\AppBundle\Entity\Selection;
use Capco\AppBundle\Entity\Status;
use Capco\AppBundle\Enum\ProposalPublicationStatus;
use Capco\AppBundle\Form\ProposalAdminType;
use Capco\AppBundle\Form\ProposalEvaluersType;
use Capco\AppBundle\Form\ProposalNotationType;
use Capco\AppBundle\Form\ProposalProgressStepType;
use Capco\AppBundle\Form\ProposalType;
use Capco\AppBundle\GraphQL\DataLoader\Proposal\ProposalLikersDataLoader;
use Capco\AppBundle\GraphQL\DataLoader\ProposalForm\ProposalFormProposalsDataLoader;
use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;
use Capco\AppBundle\GraphQL\Resolver\Traits\ResolverTrait;
use Capco\AppBundle\Helper\RedisStorageHelper;
use Capco\AppBundle\Helper\ResponsesFormatter;
use Capco\AppBundle\Repository\ProposalFormRepository;
use Capco\AppBundle\Repository\ProposalRepository;
use Capco\AppBundle\Repository\SelectionRepository;
use Capco\AppBundle\Repository\StatusRepository;
use Capco\AppBundle\Toggle\Manager;
use Capco\UserBundle\Entity\User;
use DateTime;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Error\UserErrors;
use Psr\Log\LoggerInterface;
use Swarrot\Broker\Message;
use Swarrot\SwarrotBundle\Broker\Publisher;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;

class ProposalMutation implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ResolverTrait;

    private LoggerInterface $logger;
    private ProposalLikersDataLoader $proposalLikersDataLoader;
    private GlobalIdResolver $globalIdResolver;
    private Publisher $publisher;
    private EntityManagerInterface $em;
    private FormFactoryInterface $formFactory;
    private Manager $manager;

    public function __construct(
        LoggerInterface $logger,
        ProposalLikersDataLoader $proposalLikersDataLoader,
        GlobalIdResolver $globalidResolver,
        Publisher $publisher,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        Manager $manager
    ) {
        $this->logger = $logger;
        $this->proposalLikersDataLoader = $proposalLikersDataLoader;
        $this->globalIdResolver = $globalidResolver;
        $this->publisher = $publisher;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->manager = $manager;
    }

    public function changeNotation(Argument $input, $user)
    {
        $values = $input->getArrayCopy();
        /** @var Proposal $proposal */
        $proposal = $this->globalIdResolver->resolve($values['proposalId'], $user);
        unset($values['proposalId']); // This only useful to retrieve the proposal

        foreach ($values['likers'] as &$userGlobalId) {
            $userGlobalId = GlobalIdResolver::getDecodedId($userGlobalId)['id'];
        }

        $form = $this->formFactory->create(ProposalNotationType::class, $proposal);
        $form->submit($values);

        if (!$form->isValid()) {
            throw new UserError('Input not valid : ' . $form->getErrors(true, false));
        }

        $this->em->flush();
        $this->proposalLikersDataLoader->invalidate($proposal);

        return ['proposal' => $proposal];
    }

    public function changeEvaluers(Argument $input, $user)
    {
        $values = $input->getArrayCopy();
        $proposal = $this->globalIdResolver->resolve($values['proposalId'], $user);

        unset($values['proposalId']);

        $form = $this->formFactory->create(ProposalEvaluersType::class, $proposal);
        $form->submit($values);

        if (!$form->isValid()) {
            throw new UserError('Input not valid : ' . $form->getErrors(true, false));
        }

        $this->em->flush();

        return ['proposal' => $proposal];
    }

    public function changeFollowers(string $proposalId, $user)
    {
        $proposal = $this->globalIdResolver->resolve($proposalId, $user);

        if (!$proposal) {
            throw new UserError('Cant find the proposal');
        }

        $proposal->addFollower($user);
        $this->em->flush();

        return ['proposal' => $proposal];
    }

    public function changeProgressSteps(Argument $input, $user): array
    {
        $values = $input->getArrayCopy();
        /** @var Proposal $proposal */
        $proposal = $this->globalIdResolver->resolve($values['proposalId'], $user);
        if (!$proposal) {
            throw new UserError(sprintf('Unknown proposal with id "%s"', $values['proposalId']));
        }
        unset($values['proposalId']); // This only useful to retrieve the proposal

        $form = $this->formFactory->create(ProposalProgressStepType::class, $proposal);
        $form->submit($values);

        if (!$form->isValid()) {
            throw new UserError('Input not valid : ' . $form->getErrors(true, false));
        }

        $this->em->flush();

        return ['proposal' => $proposal];
    }

    public function changeCollectStatus(string $proposalId, $user, ?string $statusId = null): array
    {
        $proposal = $this->globalIdResolver->resolve($proposalId, $user);
        if (!$proposal) {
            throw new UserError('Cant find the proposal');
        }

        $status = null;
        if ($statusId) {
            $status = $this->container->get(StatusRepository::class)->find($statusId);
        }
        $proposal->setStatus($status);
        $this->em->flush();

        $this->publisher->publish(
            CapcoAppBundleMessagesTypes::PROPOSAL_UPDATE_STATUS,
            new Message(
                json_encode([
                    'proposalId' => $proposal->getId(),
                    'date' => new DateTime(),
                ])
            )
        );

        // Synchronously index
        $indexer = $this->container->get(Indexer::class);
        $indexer->index(ClassUtils::getClass($proposal), $proposal->getId());
        $indexer->finishBulk();

        return ['proposal' => $proposal];
    }

    public function changeSelectionStatus(
        string $proposalId,
        string $stepId,
        $user,
        ?string $statusId = null
    ): array {
        $proposalId = GlobalIdResolver::getDecodedId($proposalId);
        $stepId = GlobalIdResolver::getDecodedId($stepId);
        /** @var Selection $selection */
        $selection = $this->container->get(SelectionRepository::class)->findOneBy([
            'proposal' => \is_array($proposalId) ? $proposalId['id'] : $proposalId,
            'selectionStep' => \is_array($stepId) ? $stepId['id'] : $stepId,
        ]);

        if (!$selection) {
            throw new UserError('Cant find the selection');
        }

        $status = null;
        if ($statusId) {
            /** @var Status $status */
            $status = $this->container->get(StatusRepository::class)->find($statusId);
        }

        $selection->setStatus($status);
        $this->em->flush();

        $proposal = $this->globalIdResolver->resolve(
            \is_array($proposalId) ? $proposalId['id'] : $proposalId,
            $user
        );

        $this->publisher->publish(
            CapcoAppBundleMessagesTypes::PROPOSAL_UPDATE_STATUS,
            new Message(
                json_encode([
                    'proposalId' => $proposal->getId(),
                    'date' => new DateTime(),
                ])
            )
        );

        // Synchronously index
        $indexer = $this->container->get(Indexer::class);
        $indexer->index(ClassUtils::getClass($proposal), $proposal->getId());
        $indexer->finishBulk();

        return ['proposal' => $proposal];
    }

    public function unselectProposal(string $proposalId, string $stepId, $user): array
    {
        $proposalId = GlobalIdResolver::getDecodedId($proposalId);
        $stepId = GlobalIdResolver::getDecodedId($stepId);

        $selection = $this->container->get(SelectionRepository::class)->findOneBy([
            'proposal' => \is_array($proposalId) ? $proposalId['id'] : $proposalId,
            'selectionStep' => \is_array($stepId) ? $stepId['id'] : $stepId,
        ]);

        if (!$selection) {
            throw new UserError('Cant find the selection');
        }
        $this->em->remove($selection);
        $this->em->flush();

        $proposal = $this->globalIdResolver->resolve(
            \is_array($proposalId) ? $proposalId['id'] : $proposalId,
            $user
        );
        // Synchronously index
        $indexer = $this->container->get(Indexer::class);
        $indexer->index(ClassUtils::getClass($proposal), $proposal->getId());
        $indexer->finishBulk();

        return ['proposal' => $proposal];
    }

    public function selectProposal(
        string $proposalId,
        string $stepId,
        User $user,
        ?string $statusId = null
    ): array {
        $proposalId = GlobalIdResolver::getDecodedId($proposalId);
        $stepId = GlobalIdResolver::getDecodedId($stepId);

        $selection = $this->container->get(SelectionRepository::class)->findOneBy([
            'proposal' => \is_array($proposalId) ? $proposalId['id'] : $proposalId,
            'selectionStep' => \is_array($stepId) ? $stepId['id'] : $stepId,
        ]);
        if ($selection) {
            throw new UserError('Already selected');
        }

        $selectionStatus = null;

        if ($statusId) {
            $selectionStatus = $this->container->get(StatusRepository::class)->find($statusId);
        }

        $proposal = $this->globalIdResolver->resolve($proposalId['id'], $user);
        $step = $this->globalIdResolver->resolve($stepId['id'], $user);
        $selection = new Selection();
        $selection->setSelectionStep($step);
        $selection->setStatus($selectionStatus);
        $proposal->addSelection($selection);

        $this->em->persist($selection);
        $this->em->flush();

        $this->publisher->publish(
            CapcoAppBundleMessagesTypes::PROPOSAL_UPDATE_STATUS,
            new Message(
                json_encode([
                    'proposalId' => $proposal->getId(),
                    'date' => new DateTime(),
                ])
            )
        );
        // Synchronously index
        $indexer = $this->container->get(Indexer::class);
        $indexer->index(ClassUtils::getClass($proposal), $proposal->getId());
        $indexer->finishBulk();

        return ['proposal' => $proposal];
    }

    public function changePublicationStatus(Argument $values, $user): array
    {
        if ($user && $user->isAdmin() && $this->em->getFilters()->isEnabled('softdeleted')) {
            // If user is an admin, we allow to retrieve deleted proposal
            $this->em->getFilters()->disable('softdeleted');
        }
        /** @var Proposal $proposal */
        $proposal = $this->globalIdResolver->resolve($values['proposalId'], $user);
        if (!$proposal) {
            throw new UserError(sprintf('Unknown proposal with id "%s"', $values['proposalId']));
        }

        switch ($values['publicationStatus']) {
            case ProposalPublicationStatus::TRASHED:
                $proposal
                    ->setTrashedStatus(Trashable::STATUS_VISIBLE)
                    ->setTrashedReason($values['trashedReason'])
                    ->setDeletedAt(null);

                break;
            case ProposalPublicationStatus::PUBLISHED:
                $proposal
                    ->setPublishedAt(new DateTime())
                    ->setDraft(false)
                    ->setTrashedStatus(null)
                    ->setDeletedAt(null);

                break;
            case ProposalPublicationStatus::TRASHED_NOT_VISIBLE:
                $proposal
                    ->setTrashedStatus(Trashable::STATUS_INVISIBLE)
                    ->setTrashedReason($values['trashedReason'])
                    ->setDeletedAt(null);

                break;
            case ProposalPublicationStatus::DRAFT:
                $proposal
                    ->setDraft(true)
                    ->setTrashedStatus(null)
                    ->setDeletedAt(null);

                break;
            default:
                break;
        }

        $this->em->flush();

        // Synchronously index
        $indexer = $this->container->get(Indexer::class);
        $indexer->index(ClassUtils::getClass($proposal), $proposal->getId());
        $indexer->finishBulk();

        return ['proposal' => $proposal];
    }

    public function create(Argument $input, $user): array
    {
        $proposalFormRepo = $this->container->get(ProposalFormRepository::class);

        $values = $input->getArrayCopy();

        /** @var ProposalForm $proposalForm */
        $proposalForm = $proposalFormRepo->find($values['proposalFormId']);
        if (!$proposalForm) {
            $error = sprintf('Unknown proposalForm with id "%s"', $values['proposalFormId']);
            $this->logger->error($error);

            throw new UserError($error);
        }
        if (!$proposalForm->canContribute($user) && !$user->isAdmin()) {
            throw new UserError('You can no longer contribute to this collect step.');
        }
        unset($values['proposalFormId']); // This only useful to retrieve the proposalForm

        $draft = false;
        if (isset($values['draft'])) {
            $draft = $values['draft'];
            unset($values['draft']);
        }

        if (
            \count(
                $this->container
                    ->get(ProposalRepository::class)
                    ->findCreatedSinceIntervalByAuthor($user, 'PT1M', 'author')
            ) >= 2
        ) {
            $this->logger->error('You contributed too many times.');
            $error = ['message' => 'You contributed too many times.'];

            return ['argument' => null, 'argumentEdge' => null, 'userErrors' => [$error]];
        }

        $values = $this->fixValues($values, $proposalForm);
        $proposal = new Proposal();
        $follower = new Follower();
        $follower->setUser($user);
        $follower->setProposal($proposal);
        $follower->setNotifiedOf(FollowerNotifiedOfInterface::ALL);

        $proposal
            ->setDraft($draft)
            ->setAuthor($user)
            ->setProposalForm($proposalForm)
            ->addFollower($follower);
        if (
            $proposalForm->getStep() &&
            ($defaultStatus = $proposalForm->getStep()->getDefaultStatus())
        ) {
            $proposal->setStatus($defaultStatus);
        }

        $values = $this::hydrateSocialNetworks($values, $proposal, $proposalForm, true);
        $form = $this->formFactory->create(ProposalType::class, $proposal, [
            'proposalForm' => $proposalForm,
            'validation_groups' => [$draft ? 'ProposalDraft' : 'Default'],
        ]);

        $this->logger->info('createProposal: ' . json_encode($values, true));

        $form->submit($values);

        if (!$form->isValid()) {
            $this->handleErrors($form);
        }

        $this->em->persist($follower);
        $this->em->persist($proposal);
        $this->em->flush();

        $this->container->get(RedisStorageHelper::class)->recomputeUserCounters($user);

        // Synchronously index
        $indexer = $this->container->get(Indexer::class);
        $indexer->index(ClassUtils::getClass($proposal), $proposal->getId());
        $indexer->finishBulk();

        $this->container->get(ProposalFormProposalsDataLoader::class)->invalidate($proposalForm);

        $this->container
            ->get('swarrot.publisher')
            ->publish(
                CapcoAppBundleMessagesTypes::PROPOSAL_CREATE,
                new Message(json_encode(['proposalId' => $proposal->getId()]))
            );

        return ['proposal' => $proposal];
    }

    public function changeContent(Argument $input, $viewer): array
    {
        $viewer = $this->preventNullableViewer($viewer);
        $values = $input->getArrayCopy();
        /** @var Proposal $proposal */
        $proposal = $this->globalIdResolver->resolve($values['id'], $viewer);

        if (!$proposal) {
            $error = sprintf('Unknown proposal with id "%s"', $values['id']);
            $this->logger->error($error);

            throw new UserError($error);
        }
        if (isset($values['likers'])) {
            foreach ($values['likers'] as &$userGlobalId) {
                $userGlobalId = GlobalIdResolver::getDecodedId($userGlobalId)['id'];
            }
        }

        // Save the previous draft status to send the good notif.
        $wasDraft = $proposal->isDraft();

        $proposalRevisionsEnabled = $this->manager->isActive(Manager::proposal_revisions);
        // catch all revisions with state pending or expired
        $revisions = $proposalRevisionsEnabled
            ? $proposal
                ->getRevisions()
                ->filter(
                    fn(ProposalRevision $revision) => ProposalRevisionStateType::REVISED !==
                        $revision->getState()
                )
            : [];
        $wasInRevision = $proposalRevisionsEnabled ? $proposal->isInRevision() : false;

        $author = $proposal->getAuthor();

        unset($values['id']); // This only useful to retrieve the proposal
        $proposalForm = $proposal->getProposalForm();

        if ($viewer !== $author && !$viewer->isAdmin()) {
            $error = 'You must be the author to update a proposal.';
            $this->logger->error($error);

            throw new UserError($error);
        }

        if (!$proposal->canContribute($viewer) && !$viewer->isAdmin()) {
            $error = 'Sorry, you can\'t contribute to this proposal anymore.';
            $this->logger->error($error);

            throw new UserError($error);
        }

        $draft = false;

        $this->shouldBeDraft($proposal, $author, $values, $wasDraft, $draft);

        $values = $this->fixValues($values, $proposalForm);
        $values = $this::hydrateSocialNetworks($values, $proposal, $proposalForm);

        /** @var Form $form */
        $form = $this->formFactory->create(ProposalAdminType::class, $proposal, [
            'proposalForm' => $proposalForm,
            'validation_groups' => [$draft ? 'ProposalDraft' : 'Default'],
        ]);

        if (!$viewer->isAdmin()) {
            if (isset($values['author'])) {
                $error = 'Only a user with role ROLE_ADMIN can update an author.';
                $this->logger->error($error);
                // For now we only log an error and unset the submitted value…
                unset($values['author']);
            }
            $form->remove('author');
        }

        $this->logger->info(__METHOD__ . ' : ' . var_export($values, true));
        $form->submit($values, false);

        if (!$form->isValid()) {
            $this->handleErrors($form);
        }
        $now = new DateTime();
        if ($viewer === $author) {
            $proposal->setUpdatedAt($now);

            if ($proposalRevisionsEnabled) {
                // set all revision (in pending or expired) with state revised
                /** @var ProposalRevision $revision */
                foreach ($revisions as $revision) {
                    $revision->setRevisedAt($now);
                    $revision->setState(ProposalRevisionStateType::REVISED);
                }
            }
        }

        $proposal->setUpdateAuthor($viewer);
        $this->em->flush();

        $messageData = ['proposalId' => $proposal->getId()];
        if ($wasDraft && !$proposal->isDraft()) {
            $proposalQueue = CapcoAppBundleMessagesTypes::PROPOSAL_CREATE;
            $sendNotification = true;
        } elseif ($wasInRevision) {
            $proposalQueue = CapcoAppBundleMessagesTypes::PROPOSAL_REVISION_REVISE;
            $messageData['date'] = $now->format('Y-m-d H:i:s');
            $sendNotification = true;
        } else {
            $sendNotification = $viewer->isAdmin() && $author !== $viewer ? false : true;
            $proposalQueue = CapcoAppBundleMessagesTypes::PROPOSAL_UPDATE;
            $messageData['date'] = $proposal->getUpdatedAt()->format('Y-m-d H:i:s');
        }
        $indexer = $this->container->get(Indexer::class);
        if ($sendNotification) {
            $this->container
                ->get('swarrot.publisher')
                ->publish($proposalQueue, new Message(json_encode($messageData)));
        }
        if (isset($values['likers'])) {
            $this->proposalLikersDataLoader->invalidate($proposal);
        }

        $indexer->index(ClassUtils::getClass($proposal), $proposal->getId());
        $indexer->finishBulk();

        return ['proposal' => $proposal];
    }

    public static function hydrateSocialNetworks(
        array $values,
        Proposal $proposal,
        ProposalForm $proposalForm,
        bool $create = false,
        $unsetValue = true
    ): array {
        $socialNetworks =
            !$create && $proposal->getProposalSocialNetworks()
                ? $proposal->getProposalSocialNetworks()
                : (new ProposalSocialNetworks())->setProposal($proposal);

        $proposal->setProposalSocialNetworks($socialNetworks);
        if ($proposalForm->isUsingWebPage() && isset($values['webPageUrl'])) {
            $socialNetworks->setWebPageUrl($values['webPageUrl']);
        }
        if ($proposalForm->isUsingFacebook() && isset($values['facebookUrl'])) {
            $socialNetworks->setFacebookUrl($values['facebookUrl']);
        }
        if ($proposalForm->isUsingTwitter() && isset($values['twitterUrl'])) {
            $socialNetworks->setTwitterUrl($values['twitterUrl']);
        }
        if ($proposalForm->isUsingInstagram() && isset($values['instagramUrl'])) {
            $socialNetworks->setInstagramUrl($values['instagramUrl']);
        }
        if ($proposalForm->isUsingLinkedIn() && isset($values['linkedInUrl'])) {
            $socialNetworks->setLinkedInUrl($values['linkedInUrl']);
        }
        if ($proposalForm->isUsingYoutube() && isset($values['youtubeUrl'])) {
            $socialNetworks->setYoutubeUrl($values['youtubeUrl']);
        }

        if ($unsetValue) {
            unset(
                $values['webPageUrl'],
                $values['facebookUrl'],
                $values['twitterUrl'],
                $values['instagramUrl'],
                $values['linkedInUrl'],
                $values['youtubeUrl']
            );
        }

        return $values;
    }

    private function fixValues(array $values, ProposalForm $proposalForm)
    {
        $toggleManager = $this->container->get(Manager::class);

        if (
            isset($values['theme']) &&
            (!$toggleManager->isActive('themes') || !$proposalForm->isUsingThemes())
        ) {
            unset($values['theme']);
        }

        if (isset($values['category']) && !$proposalForm->isUsingCategories()) {
            unset($values['category']);
        }

        if (
            isset($values['districts']) &&
            (!$toggleManager->isActive('districts') || !$proposalForm->isUsingDistrict())
        ) {
            unset($values['district']);
        }

        if (
            isset($values['tipsmeeeId']) &&
            (!$toggleManager->isActive(Manager::unstable__tipsmeee) ||
                !$proposalForm->isUsingTipsmeee())
        ) {
            unset($values['tipsmeeeId']);
        }

        if (isset($values['address']) && !$proposalForm->getUsingAddress()) {
            unset($values['address']);
        }

        if (isset($values['responses'])) {
            $values['responses'] = $this->container
                ->get(ResponsesFormatter::class)
                ->format($values['responses']);
        }

        return $values;
    }

    private function handleErrors(Form $form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $this->logger->error(__METHOD__ . ' : ' . $error->getMessage());
            $this->logger->error(
                __METHOD__ .
                    ' : ' .
                    $form->getName() .
                    ' ' .
                    'Extra data: ' .
                    implode('', $form->getExtraData())
            );
            $errors[] = (string) $error->getMessage();
        }
        if (!empty($errors)) {
            throw new UserErrors($errors);
        }
    }

    private function shouldBeDraft(
        Proposal $proposal,
        User $author,
        array &$values,
        bool $wasDraft,
        bool &$draft
    ): void {
        if (isset($values['draft'])) {
            if ($wasDraft) {
                $draft = $values['draft'];
                if (!$draft) {
                    if ($author && $author->isEmailConfirmed()) {
                        $proposal->setPublishedAt(new DateTime());
                    }
                    $proposal->setDraft(false);
                }
            }
            unset($values['draft']);
        }
    }
}
