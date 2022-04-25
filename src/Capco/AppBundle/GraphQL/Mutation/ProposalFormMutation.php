<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Form\ProposalFormCreateType;
use Capco\AppBundle\Form\ProposalFormNotificationsConfigurationType;
use Capco\AppBundle\Repository\ProposalFormRepository;
use Capco\AppBundle\Repository\QuestionnaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Symfony\Component\Form\FormFactoryInterface;

class ProposalFormMutation
{
    private FormFactoryInterface $formFactory;
    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private ProposalFormRepository $proposalFormRepository;
    private QuestionnaireRepository $questionnaireRepository;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $em,
        LoggerInterface $logger,
        ProposalFormRepository $proposalFormRepository,
        QuestionnaireRepository $questionnaireRepository
    ) {
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->logger = $logger;
        $this->proposalFormRepository = $proposalFormRepository;
        $this->questionnaireRepository = $questionnaireRepository;
    }

    public function create(Argument $input): array
    {
        $proposalForm = new ProposalForm();

        $form = $this->formFactory->create(ProposalFormCreateType::class, $proposalForm);

        $form->submit($input->getArrayCopy(), false);

        if (!$form->isValid()) {
            throw new UserError('Input not valid : ' . (string) $form->getErrors(true, false));
        }

        $this->em->persist($proposalForm);
        $this->em->flush();

        return ['proposalForm' => $proposalForm];
    }

    public function updateNotificationsConfiguration(Argument $input)
    {
        $arguments = $input->getArrayCopy();
        $proposalForm = $this->proposalFormRepository->find($arguments['proposalFormId']);

        if (!$proposalForm) {
            throw new UserError(
                sprintf('Unknown proposal form with id "%d"', $arguments['proposalFormId'])
            );
        }

        unset($arguments['proposalFormId']);

        $form = $this->formFactory->create(
            ProposalFormNotificationsConfigurationType::class,
            $proposalForm->getNotificationsConfiguration()
        );

        $form->submit($arguments, false);

        if (!$form->isValid()) {
            $this->logger->error(
                \get_class($this) .
                    ' updateNotificationsConfiguration: ' .
                    (string) $form->getErrors(true, false)
            );

            throw new UserError('Can\'t change the notification config!');
        }

        $this->em->flush();

        return ['proposalForm' => $proposalForm];
    }

    public function setEvaluationForm(Argument $input): array
    {
        $arguments = $input->getArrayCopy();
        $proposalForm = $this->proposalFormRepository->find($arguments['proposalFormId']);

        if (!$proposalForm) {
            throw new UserError(
                sprintf('Unknown proposal form with id "%d"', $arguments['proposalFormId'])
            );
        }

        $evaluationForm = $this->questionnaireRepository->find(
            GlobalId::fromGlobalId($arguments['evaluationFormId'])['id']
        );

        $proposalForm->setEvaluationForm($evaluationForm);

        $this->em->flush();

        return ['proposalForm' => $proposalForm];
    }
}
