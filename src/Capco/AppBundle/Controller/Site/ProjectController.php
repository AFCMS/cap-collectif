<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Form\ProjectSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use JMS\Serializer\SerializationContext;

class ProjectController extends Controller
{
    /**
     * @Cache(expires="+1 minutes", maxage="60", smaxage="60", public="true")
     * @Template("CapcoAppBundle:Project:lastProjects.html.twig")
     */
    public function lastProjectsAction($max = 4, $offset = 0)
    {
        $props = $this->get('jms_serializer')->serialize([
            'projects' => $this
                ->getDoctrine()
                ->getRepository('CapcoAppBundle:Project')
                ->getLastPublished($max, $offset),
        ], 'json', SerializationContext::create()->setGroups([
            'Projects', 'Steps', 'UserDetails', 'StepTypes', 'ThemeDetails', 'ProjectType',
        ]));

        return [
            'props' => $props,
        ];
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/projects/{projectSlug}/votes", name="app_project_show_user_votes")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_user_votes.html.twig")
     */
    public function showUserVotesAction(Project $project)
    {
        $userVotesByStepId = $this->get('capco.proposal_votes.resolver')->getUserVotesByStepIdForProject($project, $this->getUser());

        $serializer = $this->get('serializer');
        $proposalRepo = $this->getDoctrine()->getManager()->getRepository('CapcoAppBundle:Proposal');

        $userVotesByStepIdSerialized = [];
        foreach ($userVotesByStepId as $stepId => $proposals) {
            $userVotesByStepIdSerialized[$stepId] = [];
            foreach ($proposals as $proposalId) {
                $userVotesByStepIdSerialized[$stepId][] = json_decode(
                    $serializer->serialize(
                        $proposalRepo->find($proposalId),
                        'json',
                        SerializationContext::create()->setGroups(['Proposals', 'UsersInfos'])
                    ),
                    true
                );
            }
        }

        return [
          'project' => $project,
          'props' => ['userVotesByStepId' => $userVotesByStepIdSerialized],
        ];
    }

    /**
     * @Route("/projects/{projectSlug}/stats", name="app_project_show_stats")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     * @Cache(smaxage="60", public="true")
     * @Template("CapcoAppBundle:Project:show_stats.html.twig")
     */
    public function showStatsAction(Project $project)
    {
        $serializer = $this->get('serializer');

        $steps = $this
            ->get('capco.project_stats.resolver')
            ->getStepsWithStatsForProject($project)
        ;

        $props = $serializer->serialize([
            'projectId' => $project->getId(),
            'steps' => $steps,
        ], 'json');

        return [
            'project' => $project,
            'props' => $props,
            'currentStep' => 'stats_step',
        ];
    }

    /**
     * @Route("/projects/{projectSlug}/trashed", name="app_project_show_trashed", defaults={"_feature_flags" = "project_trash"} )
     * @Route("/consultations/{projectSlug}/trashed", name="app_consultation_show_trashed", defaults={"_feature_flags" = "project_trash"} )
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_trashed.html.twig")
     *
     * @param Project $project
     *
     * @return array
     */
    public function showTrashedAction(Project $project)
    {
        if (!$project->canDisplay()) {
            throw $this->createNotFoundException($this->get('translator')->trans('project.error.not_found', [], 'CapcoAppBundle'));
        }

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', [], 'CapcoAppBundle'));
        }

        $em = $this->getDoctrine()->getManager();

        $opinions = $em->getRepository('CapcoAppBundle:Opinion')->getTrashedOrUnpublishedByProject($project);
        $versions = $em->getRepository('CapcoAppBundle:OpinionVersion')->getTrashedOrUnpublishedByProject($project);
        $arguments = $em->getRepository('CapcoAppBundle:Argument')->getTrashedOrUnpublishedByProject($project);
        $sources = $em->getRepository('CapcoAppBundle:Source')->getTrashedOrUnpublishedByProject($project);

        $proposals = $em->getRepository('CapcoAppBundle:Proposal')->getTrashedOrUnpublishedByProject($project);

        return [
            'project' => $project,
            'opinions' => $opinions,
            'versions' => $versions,
            'arguments' => $arguments,
            'sources' => $sources,
            'proposals' => $proposals,
            'argumentsLabels' => Argument::$argumentTypesLabels,
            'currentStep' => 'trash_step',
        ];
    }

    /**
     * @Route("/projects/{projectSlug}/step/{stepSlug}/download", name="app_project_download")
     * @Security("has_role('ROLE_ADMIN')")
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @ParamConverter("step", class="CapcoAppBundle:Steps\AbstractStep", options={"mapping": {"stepSlug": "slug"}})
     */
    public function downloadAction(Request $request, Project $project, AbstractStep $step)
    {
        $trans = $this->get('translator');
        if (!$project || !$step) {
            throw $this->createNotFoundException($trans->trans('project.error.not_found', [], 'CapcoAppBundle'));
        }

        if (!$project->isExportable() && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException($trans->trans('project.error.not_exportable', [], 'CapcoAppBundle'));
        }

        $path = $this->container->getParameter('kernel.root_dir').'/../web/export/';
        $filename = '';
        if ($step->getProject()) {
            $filename .= $step->getProject()->getSlug().'_';
        }
        $filename .= $step->getSlug();
        $filename .= '.csv';

        if (!file_exists($path.$filename)) {
            $this->get('session')->getFlashBag()->add('danger', $trans->trans('project.download.not_yet_generated', [], 'CapcoAppBundle'));

            return $this->redirect($request->headers->get('referer'));
        }

        $date = (new \DateTime())->format('Y-m-d');

        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $response = new BinaryFileResponse($path.$filename);
        $response->headers->set('X-Accel-Redirect', '/export/'.$filename);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, $date.'_'.$filename
        );
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response;
    }

    /**
     * @Route("/projects/{projectSlug}/events", name="app_project_show_events", defaults={"_feature_flags" = "calendar"})
     * @Route("/consultations/{projectSlug}/events", name="app_consultation_show_events", defaults={"_feature_flags" = "calendar"})
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_events.html.twig")
     *
     * @param $project
     *
     * @return array
     */
    public function showEventsAction(Project $project)
    {
        $groupedEvents = $this->get('capco.event.resolver')->getEventsGroupedByYearAndMonth(null, null, $project->getSlug(), null);
        $nbEvents = $this->get('capco.event.resolver')->countEvents(null, null, $project->getSlug(), null);

        return [
            'project' => $project,
            'years' => $groupedEvents,
            'nbEvents' => $nbEvents,
            'currentStep' => 'events_step',
        ];
    }

    /**
     * @Route("/projects/{projectSlug}/posts/{page}", name="app_project_show_posts", requirements={"page" = "\d+"}, defaults={"_feature_flags" = "blog", "page" = 1} )
     * @Route("/consultations/{projectSlug}/posts/{page}", name="app_consultation_show_posts", requirements={"page" = "\d+"}, defaults={"_feature_flags" = "blog", "page" = 1} )
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_posts.html.twig")
     *
     * @param $page
     * @param $project
     *
     * @return array
     */
    public function showPostsAction(Project $project, $page)
    {
        $pagination = $this->get('capco.site_parameter.resolver')->getValue('blog.pagination.size');

        $posts = $this->get('capco.blog.post.repository')->getSearchResults(
            $pagination,
            $page,
            null,
            $project->getSlug()
        );

        //Avoid division by 0 in nbPage calculation
        $nbPage = 1;
        if ($pagination !== null && $pagination !== 0) {
            $nbPage = ceil(count($posts) / $pagination);
        }

        return [
            'project' => $project,
            'posts' => $posts,
            'page' => $page,
            'nbPage' => $nbPage,
            'currentStep' => 'posts_step',
        ];
    }

    /**
     * @Route("/projects/{projectSlug}/participants/{page}", name="app_project_show_contributors", requirements={"page" = "\d+"}, defaults={"page" = 1} )
     * @Route("/consultations/{projectSlug}/participants/{page}", name="app_consultation_show_contributors", requirements={"page" = "\d+"}, defaults={"page" = 1} )
     * @ParamConverter("project", class="CapcoAppBundle:Project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:show_contributors.html.twig")
     * @Cache(smaxage="120", public=true)
     *
     * @param $page
     * @param $project
     */
    public function showContributorsAction(Project $project, $page)
    {
        $pagination = $this->get('capco.site_parameter.resolver')->getValue('contributors.pagination');

        $contributors = $this->get('capco.contribution.resolver')->getProjectContributorsOrdered($project, true, $pagination, $page);
        //Avoid division by 0 in nbPage calculation
        $nbPage = 1;
        if ($pagination !== null && $pagination !== 0) {
            $nbPage = ceil(count($contributors) / $pagination);
        }

        $showVotes = $this->get('capco.project.helper')->hasStepWithVotes($project);

        return [
            'project' => $project,
            'contributors' => $contributors,
            'page' => $page,
            'pagination' => $pagination,
            'nbPage' => $nbPage,
            'currentStep' => 'contributors_step',
            'showVotes' => $showVotes,
        ];
    }

    /**
     * @Template("CapcoAppBundle:Project:show_meta.html.twig")
     *
     * @param $projectSlug
     * @param $currentStepSlug
     *
     * @return array
     */
    public function showMetaAction($projectSlug, $currentStepSlug)
    {
        $em = $this->getDoctrine();
        $project = $em->getRepository('CapcoAppBundle:Project')->getOneBySlugWithStepsAndEventsAndPosts($projectSlug);
        $projectSteps = $em->getRepository('CapcoAppBundle:Steps\AbstractStep')->getByProjectSlug($projectSlug);

        return [
            'project' => $project,
            'currentStep' => $currentStepSlug,
            'projectSteps' => $projectSteps,
        ];
    }

    /**
     * @Route("/projects", name="app_project")
     * @Template("CapcoAppBundle:Project:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $parameters = [];
        $form = $this->createForm(new ProjectSearchType($this->get('capco.toggle.manager')));
        $form->submit($request->query->all());

        if ($form->isValid()) {
            $parameters = $form->getData();
            $parameters['type'] = $parameters['type'] ? $parameters['type']->getSlug() : null;

            if (isset($parameters['theme'])) {
                $parameters['theme'] = $parameters['theme'] ? $parameters['theme']->getSlug() : null;
            }
        }

        $parameters['projectTypes'] = $this->getDoctrine()->getRepository('CapcoAppBundle:ProjectType')
            ->findAll();

        return [
            'props' => $this->get('serializer')->serialize(['project' => $parameters], 'json',
                SerializationContext::create()->setGroups(['ProjectType'])),
            'params' => $parameters,
        ];
    }
}
