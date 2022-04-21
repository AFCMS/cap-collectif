<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Entity\Questionnaire;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class QuestionnairesController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a questionnaire",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    404 = "Returned when opinion is not found",
     *  }
     * )
     * @Get("/questionnaires/{id}")
     * @ParamConverter("questionnaire", options={"mapping": {"id": "id"}, "repository_method": "find", "map_method_signature": true})
     * @View(statusCode=200, serializerGroups={"Questionnaires", "Questions"})
     * @Cache(smaxage="120", public=true)
     */
    public function getQuestionnaireAction(Questionnaire $questionnaire)
    {
        return $questionnaire;
    }

    /**
     * @Get("/questionnaires-stats")
     * @View(statusCode=200)
     */
    public function getQuestionnairesStatsAction()
    {
        $questionnaires = $this->getDoctrine()->getManager()
             ->getRepository('CapcoAppBundle:Questionnaire')
             ->findAll()
        ;
        $results = [];
        foreach ($questionnaires as $questionnaire) {
            $questions = $questionnaire->getRealQuestions();
            $questionsResults = [];
            $scores = [];
            foreach ($questions as $question) {
                $type = $question->getInputType();
                if ($type === 'ranking') {
                    $questionChoices = $question->getQuestionChoices();
                    $choices = $questionChoices->map(function ($choice) {
                        return $choice->getTitle();
                    })->toArray();
                    $scores = array_combine($choices, array_map(function ($h) {
                        return 0;
                    }, $choices));
                    foreach ($question->getResponses() as $response) {
                        $reply = $response->getReply();
                        if ($reply && $reply->isEnabled() && !$reply->isExpired()) {
                            // The score is the maximum number of choices for the question
                        // 4 replies gives 4 3 2 1 points
                        // 2 replies with maximum 4 gives 4 3 points
                        $score = $question->getValidationRule()
                          ? $question->getValidationRule()->getNumber()
                          : $question->getQuestionChoices()->count()
                        ;
                            foreach ($response->getValue()['labels'] as $label) {
                                $scores[$label] += $score;
                                --$score;
                            }
                        }
                    }
                }
                if ($type === 'radio' || $type === 'select' || $type === 'checkbox') {
                    $choices = $question->getQuestionChoices()->map(function ($choice) {
                        return $choice->getTitle();
                    })->toArray();
                    $scores = array_combine($choices, array_map(function ($h) {
                        return 0;
                    }, $choices));
                    foreach ($question->getResponses() as $response) {
                        $reply = $response->getReply();
                        if ($reply && $reply->isEnabled() && !$reply->isExpired()) {
                            if (is_string($response->getValue())) {
                                $scores[$response->getValue()] += 1;
                            } else {
                                foreach ($response->getValue()['labels'] as $label) {
                                    $scores[$label] += 1;
                                }
                            }
                        }
                    }
                }
                $data = [
                  'question_title' => $question->getTitle(),
                  'question_id' => $question->getId(),
                  'question_type' => $question->getInputType(),
                ];
                if (count($scores) > 0) {
                    $data['scores'] = $scores;
                }
                $questionsResults[] = $data;
            }
            $results[] = [
              'questionnaire_id' => $questionnaire->getId(),
              'questionnaire_title' => $questionnaire->getTitle(),
              'questions' => $questionsResults,
            ];
        }

        return $results;
    }
}
