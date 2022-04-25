<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Repository\QuestionnaireAbstractQuestionRepository;
use Capco\AppBundle\Repository\RegistrationFormRepository;
use Capco\AppBundle\Toggle\Manager;
use Capco\AppBundle\Form\ApiQuestionType;
use Capco\AppBundle\Entity\QuestionChoice;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Patch;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Delete;
use Capco\AppBundle\Entity\Questions\SimpleQuestion;
use Capco\AppBundle\Entity\Questions\AbstractQuestion;
use Capco\AppBundle\Entity\Questions\MultipleChoiceQuestion;
use Capco\AppBundle\Entity\Questions\QuestionnaireAbstractQuestion;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class FeaturesController extends AbstractFOSRestController
{
    private $registrationFormRepository;
    private $toggleManager;
    private $questionRepository;

    public function __construct(
        RegistrationFormRepository $registrationFormRepository,
        Manager $toggleManager,
        QuestionnaireAbstractQuestionRepository $questionRepository
    ) {
        $this->registrationFormRepository = $registrationFormRepository;
        $this->toggleManager = $toggleManager;
        $this->questionRepository = $questionRepository;
    }

    /**
     * @Post("/registration_form/questions")
     * @View(statusCode=200, serializerGroups={"Questions"})
     */
    public function postRegistrationQuestionAction(Request $request)
    {
        $viewer = $this->getUser();
        if (!$viewer || !$viewer->isSuperAdmin()) {
            throw new AccessDeniedHttpException('Not authorized.');
        }
        $form = $this->createForm(ApiQuestionType::class);
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $form;
        }

        $data = $form->getData();
        $question = null;
        if ('0' === $data['type']) {
            $question = new SimpleQuestion();
        }
        if ('4' === $data['type']) {
            $question = new MultipleChoiceQuestion();
            foreach ($data['choices'] as $key => $choice) {
                $questionChoice = new QuestionChoice();
                $questionChoice->setTitle($choice['label']);
                $questionChoice->setPosition($key);
                $question->addQuestionChoice($questionChoice);
            }
        }
        $question->setType((int) $data['type']);
        $question->setTitle($data['question']);
        $question->setRequired($data['required']);

        $abs = new QuestionnaireAbstractQuestion();
        $registrationForm = $this->registrationFormRepository->findCurrent();
        $abs->setRegistrationForm($registrationForm);
        $abs->setQuestion($question);
        $abs->setPosition(0);

        $em = $this->get('doctrine')->getManager();
        $em->persist($abs);
        $em->flush();

        return $question;
    }

    /**
     * Used to reorder questions.
     *
     * @Patch("/registration_form/questions")
     * @View(statusCode=204, serializerGroups={})
     */
    public function patchRegistrationQuestionAction(Request $request)
    {
        $viewer = $this->getUser();
        if (!$viewer || !$viewer->isSuperAdmin()) {
            throw new AccessDeniedHttpException('Not authorized.');
        }

        $orderedQuestions = json_decode($request->getContent(), true)['questions'];
        $registrationForm = $this->registrationFormRepository->findCurrent();
        $absQuestions = $this->questionRepository->findByRegistrationForm($registrationForm);

        foreach ($orderedQuestions as $key => $orderQuestion) {
            foreach ($absQuestions as $absQuestion) {
                if ($orderQuestion['id'] === $absQuestion->getQuestion()->getId()) {
                    $absQuestion->setPosition($key);
                }
            }
        }
        $this->get('doctrine')
            ->getManager()
            ->flush();
    }

    /**
     * @Put("/registration_form/questions/{id}")
     * @Entity("question", options={"mapping": {"id": "id"}})
     * @View(statusCode=200, serializerGroups={"Questions"})
     */
    public function putRegistrationQuestionAction(Request $request, AbstractQuestion $question)
    {
        $viewer = $this->getUser();
        if (!$viewer || !$viewer->isSuperAdmin()) {
            throw new AccessDeniedHttpException('Not authorized.');
        }

        $form = $this->createForm(ApiQuestionType::class);
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->get('doctrine')->getManager();
        $data = $form->getData();

        if ((int) $data['type'] !== $question->getType()) {
            // type has changed we remove and create a new question
            $this->deleteRegistrationQuestionAction($question);

            return $this->postRegistrationQuestionAction($request);
        }
        if ('4' === $data['type']) {
            // Remove previous choices
            $question->resetQuestionChoices();
            foreach ($data['choices'] as $key => $choice) {
                $questionChoice = new QuestionChoice();
                $questionChoice->setTitle($choice['label']);
                $questionChoice->setPosition($key);
                $question->addQuestionChoice($questionChoice);
            }
        }
        $question->setType((int) $data['type']);
        $question->setTitle($data['question']);
        $question->setRequired($data['required']);

        $em->flush();

        return $question;
    }

    /**
     * @Delete("/registration_form/questions/{id}")
     * @Entity("question", options={"mapping": {"id": "id"}})
     * @View(statusCode=204, serializerGroups={})
     */
    public function deleteRegistrationQuestionAction(AbstractQuestion $question)
    {
        $viewer = $this->getUser();
        if (!$viewer || !$viewer->isAdmin()) {
            throw new AccessDeniedHttpException('Not authorized.');
        }

        $em = $this->get('doctrine')->getManager();
        $em->remove($question);
        $em->flush();
    }
}
