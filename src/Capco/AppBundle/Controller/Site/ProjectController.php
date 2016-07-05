<?php

namespace Capco\AppBundle\Controller\Site;

use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\Theme;
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
                ->get('doctrine.orm.entity_manager')
                ->getRepository('CapcoAppBundle:Project')
                ->getLastPublished($max, $offset),
        ], 'json', SerializationContext::create()->setGroups(['Projects', 'Steps', 'Themes']));

        return [
            'props' => $props,
        ];
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route("/projects/{projectSlug}/votes", name="app_project_show_user_votes")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     *
     * @param Project $project
     *
     * @return Response
     */
    public function showUserVotesAction(Project $project)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('jms_serializer');

        $props = $serializer->serialize([
            'votableSteps' => $this
                ->get('capco.proposal_votes.resolver')
                ->getVotableStepsForProject($project),
            'districts' => $em->getRepository('CapcoAppBundle:District')->findAll(),
            'themes' => $em->getRepository('CapcoAppBundle:Theme')->findAll(),
            'projectId' => $project->getId(),
        ], 'json', SerializationContext::create()->setGroups(['Steps', 'UserVotes', 'Districts', 'Themes']));

        $response = $this->render('CapcoAppBundle:Project:show_user_votes.html.twig', [
            'project' => $project,
            'props' => $props,
        ]);

        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')) {
            $response->setPublic();
            $response->setSharedMaxAge(60);
        }

        return $response;
    }

    /**
     * @Route("/projects/{projectSlug}/stats", name="app_project_show_stats")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     *
     * @param Project $project
     *
     * @return Response
     */
    public function showStatsAction(Project $project)
    {
        $serializer = $this->get('jms_serializer');
        $em = $this->get('doctrine.orm.entity_manager');

        $steps = $this
            ->get('capco.project_stats.resolver')
            ->getStepsWithStatsForProject($project)
        ;

        $props = $serializer->serialize([
            'projectId' => $project->getId(),
            'themes' => $em->getRepository('CapcoAppBundle:Theme')->findAll(),
            'districts' => $em->getRepository('CapcoAppBundle:District')->findAll(),
            'steps' => $steps,
        ], 'json', SerializationContext::create()->setGroups(['Themes', 'Districts']));

        $response = $this->render('CapcoAppBundle:Project:show_stats.html.twig', [
            'project' => $project,
            'props' => $props,
            'currentStep' => 'stats_step',
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(60);

        return $response;
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
        if (false == $project->canDisplay()) {
            throw $this->createNotFoundException($this->get('translator')->trans('project.error.not_found', [], 'CapcoAppBundle'));
        }

        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException($this->get('translator')->trans('error.access_restricted', [], 'CapcoAppBundle'));
        }

        $em = $this->get('doctrine.orm.entity_manager');

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
     *
     * @param Project      $project
     * @param AbstractStep $step
     *
     * @return Response $response
     */
    public function downloadAction(Project $project, AbstractStep $step)
    {
        if (!$project || !$step) {
            throw $this->createNotFoundException($this->get('translator')->trans('project.error.not_found', [], 'CapcoAppBundle'));
        }

        if (!$project->isExportable() && !$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException($this->get('translator')->trans('project.error.not_exportable', [], 'CapcoAppBundle'));
        }

        $path = $this->container->getParameter('kernel.root_dir').'/../web/export/';
        $filename = '';
        if ($step->getProject()) {
            $filename .= $step->getProject()->getSlug().'_';
        }
        $filename .= $step->getSlug().'.xls';

        $request = $this->get('request_stack')->getCurrentRequest();
        if (!file_exists($path.$filename)) {
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('project.download.not_yet_generated', [], 'CapcoAppBundle'));

            return $this->redirect($request->headers->get('referer'));
        }

        $resolver = $this->get('capco.project.download.resolver');
        $date = (new \DateTime())->format('Y-m-d');
        $request = $this->container->get('request_stack')->getCurrentRequest();

        $request->headers->set('X-Sendfile-Type', 'X-Accel-Redirect');
        $response = new BinaryFileResponse($path.$filename);
        $response->headers->set('X-Accel-Redirect', '/export/'.$filename);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT, $date.'_'.$filename
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
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
     * @Route("/projects/{page}", name="app_project", requirements={"page" = "\d+"}, defaults={"page" = 1} )
     * @Route("/projects/{theme}/{sort}/{page}", name="app_project_search", requirements={"page" = "\d+"}, defaults={"page" = 1, "theme" = "all"} )
     * @Route("/projects/{theme}/{sort}/{term}/{page}", name="app_project_search_term", requirements={"page" = "\d+"}, defaults={"page" = 1, "theme" = "all"} )
     * @Template("CapcoAppBundle:Project:index.html.twig")
     *
     * @param $page
     * @param $request
     * @param $theme
     * @param $sort
     * @param $term
     *
     * @return array
     */
    public function indexAction(Request $request, $page, $theme = null, $sort = null, $term = null)
    {
        $em = $this->getDoctrine()->getManager();
        $currentUrl = $this->generateUrl('app_project');
        $toggleManager = $this->get('capco.toggle.manager');
        $themesActivated = $toggleManager->isActive('themes');
        $formActivated = $toggleManager->isActive('projects_form');

        if ($formActivated) {
            $form = $this->createForm(new ProjectSearchType($this->get('capco.toggle.manager')), null, [
                'action' => $currentUrl,
                'method' => 'POST',
            ]);
        }

        if ($request->getMethod() == 'POST' && $formActivated) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                // redirect to the results page (avoids reload alerts)
                $data = $form->getData();

                return $this->redirect($this->generateUrl('app_project_search_term', [
                    'theme' => ($themesActivated && array_key_exists('theme', $data) && $data['theme']) ? $data['theme']->getSlug() : Theme::FILTER_ALL,
                    'sort' => $data['sort'],
                    'term' => $data['term'],
                ]));
            }
        } else {
            if ($formActivated) {
                $form->setData([
                    'theme' => $themesActivated ? $em->getRepository('CapcoAppBundle:Theme')->findOneBySlug($theme) : null,
                    'sort' => $sort,
                    'term' => $term,
                ]);
            }
        }

        $serializer = $this->get('jms_serializer');
        $pagination = $this->get('capco.site_parameter.resolver')->getValue('projects.pagination');
        $projectsRaw = $em->getRepository('CapcoAppBundle:Project')->getSearchResults($pagination, $page, $theme, $sort, $term);
        $count = $em->getRepository('CapcoAppBundle:Project')->countPublished();
        $props = $serializer->serialize([
            'projects' => $projectsRaw,
        ], 'json', SerializationContext::create()->setGroups(['Projects', 'Steps', 'Themes']));

        //Avoid division by 0 in nbPage calculation
        $nbPage = 1;
        if ($pagination !== null && $pagination !== 0) {
            $nbPage = ceil($count / $pagination);
        }

        $parameters = [
            'props' => $props,
            'count' => $count,
            'page' => $page,
            'nbPage' => $nbPage,
        ];

        if ($formActivated) {
            $parameters['form'] = $form->createView();
        }

        return $parameters;
    }

    /**
     * @Cache(expires="+1 minutes", maxage="60", smaxage="60", public="true")
     * @ParamConverter("project", options={"mapping": {"projectSlug": "slug"}})
     * @Template("CapcoAppBundle:Project:votes_widget.html.twig")
     *
     * @param Project $project
     *
     * @return array
     */
    public function showVotesWidgetAction(Project $project)
    {
        $serializer = $this->get('jms_serializer');

        $props = $serializer->serialize([
            'votableSteps' => $this
                ->get('capco.proposal_votes.resolver')
                ->getVotableStepsForProject($project),
            'image' => $this
                ->get('capco.site_image.resolver')
                ->getMedia('image.votes_bar'),
            'votesPageUrl' => $this->get('router')->generate('app_project_show_user_votes', ['projectSlug' => $project->getSlug()], true),
            'projectId' => $project->getId(),
        ], 'json', SerializationContext::create()->setGroups(['Steps', 'UserVotes']));

        return [
            'props' => $props,
        ];
    }
}
