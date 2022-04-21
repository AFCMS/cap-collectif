<?php

namespace Capco\AppBundle\Twig;

use Capco\AppBundle\Repository\ThemeRepository;
use Capco\AppBundle\Repository\ProjectRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Capco\AppBundle\Helper\StepHelper;

class ThemeExtension extends \Twig_Extension
{
    protected $themeRepo;
    protected $projectRepo;
    protected $twig;
    private $serializer;
    private $router;

    public function __construct(
        ThemeRepository $themeRepo,
        ProjectRepository $projectRepo,
        $twig,
        Serializer $serializer,
        Router $router,
        StepHelper $stepHelper
        ) {
        $this->themeRepo = $themeRepo;
        $this->projectRepo = $projectRepo;
        $this->twig = $twig;
        $this->serializer = $serializer;
        $this->router = $router;
        $this->stepHelper = $stepHelper;
    }

    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('list_projectsById', [$this, 'listProjects']),
            new \Twig_SimpleFunction('themes_list', [$this, 'listThemes']),
        ];
    }

    public function listProjects(): array
    {
        $projects = $this->projectRepo->findAll();
        $data = [];
        foreach ($projects as $project) {
            $projectStepsData = [];
            $projectStepsByIdData = [];
            foreach ($project->getSteps() as $step) {
                $realStep = $step->getStep();
                $projectStepsStatus = [];
                foreach ($realStep->getStatuses() as $status) {
                    $projectStepsStatus[] = [
                      'id' => $status->getId(),
                      'name' => $status->getName(),
                    ];
                }
                $formater = \IntlDateFormatter::create(null, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE);
                $stepData = [
                  'id' => $realStep->getId(),
                  'title' => $realStep->getTitle(),
                  'body' => $realStep->getBody(),
                  'startAt' => $realStep->getStartAt() ? $formater->format($realStep->getStartAt()) : null,
                  'endAt' => $realStep->getStartAt() ? $formater->format($realStep->getEndAt()): null,
                  'position' => $realStep->getPosition(),
                  'type' => $realStep->getType(),
                  'showProgressSteps' => method_exists($realStep, 'isAllowingProgressSteps') ? $realStep->isAllowingProgressSteps() : false, //|default(false),
                  'statuses' => $projectStepsStatus,
                  'status' => $this->stepHelper->getStatus($realStep),
                  'open' => $realStep->isOpen(),
                  'proposalFormId' => method_exists($realStep, 'getProposalFormId') ? $realStep->getProposalFormId() : null,
                  'voteThreshold' => method_exists($realStep, 'getVoteThreshold') ? $realStep->getVoteThreshold() : null,
                  'votesHelpText' => method_exists($realStep, 'getVotesHelpText') ? $realStep->getVotesHelpText() : null,
                  'votesLimit' => method_exists($realStep, 'getVotesLimit') ? $realStep->getVotesLimit() : null,
                  'votable' => method_exists($realStep, 'isVotable') ? $realStep->isVotable() : false,
                  'voteType' => method_exists($realStep, 'getVoteType') ? $realStep->getVoteType() : false,
                  'budget' => method_exists($realStep, 'getBudget') ? $realStep->getBudget() : null,
                  'timeless' => $realStep->isTimeless(),
                  'isPrivate' => method_exists($realStep, 'isPrivate') ? $realStep->isPrivate() : null,
                  'titleHelpText' => method_exists($realStep, 'getTitleHelpText') ? $realStep->getTitleHelpText() : null,
                  'descriptionHelpText' => method_exists($realStep, 'getDescriptionHelpText') ? $realStep->getDescriptionHelpText() : null,
                  '_links' => [
                      'show' => $realStep->getType() == 'other' ? '' :
                        $this->router->generate(
                          'app_project_show_'.$realStep->getType(),
                          ['projectSlug' => $project->getSlug(), 'stepSlug' => $realStep->getSlug()],
                          UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                  ],
                ];
                $projectStepsData[] = $stepData;
                $projectStepsByIdData[$realStep->getId()] = $stepData;
            }
            $context = new SerializationContext();
            $context->setGroups(['Projects', 'UserDetails', 'UserVotes', 'ThemeDetails', 'ProjectType']);
            $projectSerialized = $this->serializer->serialize($project, 'json', $context);

            $projectData = json_decode($projectSerialized, true);
            $projectData['steps'] = $projectStepsData;
            $projectData['stepsById'] = $projectStepsByIdData;
            $data[$project->getId()] = $projectData;
        }

        return $data;
    }

    public function listThemes(): array
    {
        $themes = $this->themeRepo->findBy(['isEnabled' => true]);
        $list = [];
        foreach ($themes as $theme) {
            $list[] = ['id' => $theme->getId(), 'title' => $theme->getTitle(), 'slug' => $theme->getSlug()];
        }

        return $list;
    }
}
