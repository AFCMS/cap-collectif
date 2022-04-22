<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\Questionnaire;
use Capco\AppBundle\Entity\Reply;
use Capco\AppBundle\Form\ReplyType;
use Capco\AppBundle\Helper\RedisStorageHelper;
use Capco\AppBundle\Helper\ResponsesFormatter;
use Capco\AppBundle\Notifier\UserNotifier;
use Capco\AppBundle\Repository\QuestionnaireRepository;
use Capco\AppBundle\Repository\ReplyRepository;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Error\UserErrors;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactory;

class AddReplyMutation
{
    private $em;
    private $formFactory;
    private $proposalRepo;
    private $questionnaireRepo;
    private $redisStorageHelper;
    private $responsesFormatter;
    private $logger;
    private $replyRepo;
    private $userNotifier;

    public function __construct(
      EntityManagerInterface $em,
      FormFactory $formFactory,
      ReplyRepository $replyRepo,
      QuestionnaireRepository $questionnaireRepo,
      RedisStorageHelper $redisStorageHelper,
      ResponsesFormatter $responsesFormatter,
      LoggerInterface $logger,
      UserNotifier $userNotifier
    ) {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->replyRepo = $replyRepo;
        $this->questionnaireRepo = $questionnaireRepo;
        $this->redisStorageHelper = $redisStorageHelper;
        $this->responsesFormatter = $responsesFormatter;
        $this->logger = $logger;
        $this->userNotifier = $userNotifier;
    }

    public function __invoke(Argument $input, User $user)
    {
        $values = $input->getRawArguments();

        $questionnaire = $this->questionnaireRepo->find($values['questionnaireId']);
        unset($values['questionnaireId']);

        if (!$questionnaire->canContribute()) {
            throw new UserError('You can no longer contribute to this questionnaire step.');
        }

        if (!$questionnaire->isMultipleRepliesAllowed()) {
            $previousReply = $this->replyRepo->getOneForUserAndQuestionnaire($questionnaire, $user);
            if ((bool) $previousReply) {
                throw new UserError('Only one reply by user is allowed for this questionnaire.');
            }
        }

        if ($questionnaire->isPhoneConfirmationRequired() && !$user->isPhoneConfirmed()) {
            throw new UserError('You must confirm your account via sms to post a reply.');
        }

        $reply = (new Reply())
          ->setAuthor($user)
          ->setQuestionnaire($questionnaire)
          ->setEnabled(true)
        ;

        $values['responses'] = $this->responsesFormatter->format($values['responses']);

        $form = $this->formFactory->create(ReplyType::class, $reply, ['anonymousAllowed' => $questionnaire->isAnonymousAllowed()]);
        $form->submit($values, false);

        if (!$form->isValid()) {
            $this->handleErrors($form);
        }

        $this->em->persist($reply);
        $this->em->flush();
        $this->redisStorageHelper->recomputeUserCounters($user);

        if ($questionnaire->isAcknowledgeReplies()) {
            $this->userNotifier->acknowledgeReply($questionnaire->getStep()->getProject(), $reply);
        }

        return ['questionnaire' => $questionnaire, 'reply' => $reply];
    }

    private function handleErrors($form)
    {
        $errors = [];
        foreach ($form->getErrors() as $error) {
            $this->logger->error((string) $error->getMessage());
            $this->logger->error(implode($form->getExtraData()));
            $errors[] = (string) $error->getMessage();
        }
        if (!empty($errors)) {
            throw new UserErrors($errors);
        }
    }
}
