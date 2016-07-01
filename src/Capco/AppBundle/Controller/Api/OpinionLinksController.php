<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionAppendix;
use Capco\AppBundle\Entity\OpinionVote;
use Capco\AppBundle\Entity\OpinionType;
use Capco\AppBundle\Entity\OpinionVersion;
use Capco\AppBundle\Entity\OpinionVersionVote;
use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Source;
use Capco\AppBundle\Entity\Reporting;
use Capco\AppBundle\Entity\Steps\ConsultationStep;
use Capco\AppBundle\Form\ApiSourceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Capco\AppBundle\Form\OpinionVersionType;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;

class OpinionLinksController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Create an opinion link.",
     *  statusCodes={
     *    201 = "Returned when successful",
     *    404 = "Returned when opinion not found",
     *  }
     * )
     *
     * @Post("/opinions/{id}/links")
     * @ParamConverter("opinion", options={"mapping": {"id": "id"}})
     * @Security("has_role('ROLE_USER')")
     * @View(statusCode=201, serializerGroups={})
     */
    public function postOpinionLinkAction(Request $request, Opinion $opinion)
    {
        $type = $opinion->getOpinionType();
        $step = $opinion->getStep();
        $project = $step->getProject();

        if (!$step->canContribute()) {
            throw new BadRequestHttpException('This step is not contribuable.');
        }

        if ($type->getConsultationStepType() != $step->getConsultationStepType()) {
            throw new BadRequestHttpException('Not linked to this opinionType.');
        }

        if (!$type->getIsEnabled()) {
            throw new BadRequestHttpException('This opinion type is not enabled.');
        }

        if (!$type->isLinkable()) {
            throw new BadRequestHttpException('This opinion type is not linkable.');
        }

        if ($opinion->getOpinionType() != $type) {
            throw new BadRequestHttpException('Not linked to this opinionType.');
        }

        $link = (new Opinion())
            ->setAuthor($this->getUser())
            ->addParentConnection($opinion)
            ->setStep($step)
            ->setIsEnabled(true)
        ;

        $form = $this->createForm('opinion_link', $link);
        $form->submit($request->request->all(), false);

        if (!$link->getOpinionType()->isLinkable()) {
            throw new BadRequestHttpException('This opinion type is not linkable.');
        }

        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($link);
        $em->flush();

        return $link;
    }

    /**
     * Get all links of an opinion.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get all links of an opinion",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    404 = "Returned when opinion not found",
     *  }
     * )
     *
     * @Get("/opinions/{id}/links")
     * @ParamConverter("opinion", options={"mapping": {"id": "id"}, "repository_method": "getOne", "map_method_signature" = true})
     * @QueryParam(name="filter", requirements="(old|last)", default="last")
     * @View(statusCode=200, serializerGroups={"OpinionLinkPreviews", "UsersInfos", "OpinionTypeDetails"})
     */
    public function cgetOpinionLinksAction(Opinion $opinion, ParamFetcherInterface $paramFetcher)
    {
        $filter = $paramFetcher->get('filter');

        return [
            'links' => $opinion->getConnections($filter),
        ];
    }
}
