<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\ProjectAbstractStep;
use Capco\AppBundle\Form\ProjectType;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Notifier\ProjectNotifier;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use FOS\RestBundle\Controller\Annotations\Get;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Capco\AppBundle\Resolver\ProjectStatsResolver;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProjectsController extends AbstractFOSRestController
{
    /**
     * @Post("/projects")
     * @View(statusCode=201, serializerGroups={"ProjectAdmin"})
     */
    public function postProjectAction(Request $request)
    {
        $viewer = $this->getUser();
        if (!$viewer || !$viewer->isAdmin()) {
            throw new AccessDeniedHttpException('Not authorized.');
        }

        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $form;
        }

        // We make sure the author can edit his project
        $author = $project->getFirstAuthor();
        $author->addRole('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $em->persist($project);
        $em->flush();

        $this->get(ProjectNotifier::class)->onCreate($project);

        return $project;
    }

    /**
     * @Get("/projects/{projectId}/steps")
     * @Entity("project", options={"mapping": {"projectId": "id"}})
     * @View(serializerGroups={"Steps", "Default", "Statuses"})
     */
    public function getProjectStepsAction(Project $project)
    {
        return $project->getSteps()->map(function (ProjectAbstractStep $step) {
            return $step->getStep();
        });
    }

    /**
     * @Get("/project_stats/{step_id}")
     * @Entity("step", options={"mapping": {"step_id": "id"}})
     * @QueryParam(name="key", requirements="themes|districts|userTypes|costs|votes")
     * @QueryParam(name="limit", requirements="[0-9.]+", default=0)
     * @QueryParam(name="theme", default=null)
     * @QueryParam(name="district", default=null)
     * @QueryParam(name="category", default=null)
     * @View()
     */
    public function getProjectStatsAction(AbstractStep $step, ParamFetcherInterface $paramFetcher)
    {
        $key = $paramFetcher->get('key');
        $limit = $paramFetcher->get('limit');
        $theme = $paramFetcher->get('theme');
        $district = $paramFetcher->get('district');
        $category = $paramFetcher->get('category');

        if ('votes' === $key && $step instanceof CollectStep) {
            throw new BadRequestHttpException('Collect steps have no votes stats.');
        }

        if ('votes' !== $key && $step instanceof SelectionStep) {
            throw new BadRequestHttpException('Selection steps have no ' . $key . ' stats.');
        }

        if ('votes' !== $key && (null !== $theme || null !== $district)) {
            $theme = null;
            $district = null;
            // throw new BadRequestHttpException('Only votes stats can be filtered by theme or district.');
        }

        $data = $this->get(ProjectStatsResolver::class)->getStatsForStepByKey(
            $step,
            $key,
            $limit,
            $theme,
            $district,
            $category
        );

        return ['data' => $data];
    }

    /**
     * @Get("/projects/{projectId}")
     * @Entity("project", options={"mapping": {"projectId": "id"}})
     * @View(serializerGroups={"Projects", "Default"})
     */
    public function getProjectAction(Project $project)
    {
        return $project;
    }
}
