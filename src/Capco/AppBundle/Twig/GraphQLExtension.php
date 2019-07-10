<?php

namespace Capco\AppBundle\Twig;

use Capco\AppBundle\Entity\Questionnaire;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Overblog\GraphQLBundle\Relay\Node\GlobalId;
use Capco\AppBundle\Repository\CollectStepRepository;
use Capco\AppBundle\Repository\QuestionnaireRepository;
use Overblog\GraphQLBundle\Relay\Connection\Output\ConnectionBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GraphQLExtension extends AbstractExtension
{
    private $collectStepRepo;
    private $questionnaireRepo;

    public function __construct(
        CollectStepRepository $collectStepRepo,
        QuestionnaireRepository $questionnaireRepo
    ) {
        $this->collectStepRepo = $collectStepRepo;
        $this->questionnaireRepo = $questionnaireRepo;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('graphql_offset_to_cursor', [$this, 'getOffsetToCursor']),
            new TwigFunction('graphql_list_collect_steps', [$this, 'getCollectSteps']),
            new TwigFunction('graphql_list_questionnaires', [$this, 'getQuestionnaires'])
        ];
    }

    public function getOffsetToCursor(int $key): string
    {
        return ConnectionBuilder::offsetToCursor($key);
    }

    public function getCollectSteps(): array
    {
        $steps = $this->collectStepRepo->findAll();

        return array_map(static function (CollectStep $step) {
            return [
                'id' => GlobalId::toGlobalId('CollectStep', $step->getId()),
                'label' => (string) $step
            ];
        }, $steps);
    }

    public function getQuestionnaires(): array
    {
        $questionnaires = $this->questionnaireRepo->findAll();

        return array_map(static function (Questionnaire $questionnaire) {
            return [
                'id' => GlobalId::toGlobalId('Questionnaire', $questionnaire->getId()),
                'label' => (string) $questionnaire
            ];
        }, $questionnaires);
    }
}
