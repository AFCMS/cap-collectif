<?php

namespace Capco\AppBundle\GraphQL\Resolver\Question;

use Capco\AppBundle\Repository\QuestionnaireRepository;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class AvailableQuestionnaireResolver implements ResolverInterface
{
    private $questionnaireRepository;

    public function __construct(QuestionnaireRepository $questionnaireRepository)
    {
        $this->questionnaireRepository = $questionnaireRepository;
    }

    public function __invoke(Argument $args): array
    {
        return $this->questionnaireRepository->getAvailableQuestionnaires($args['term']);
    }
}
