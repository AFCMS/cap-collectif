<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Entity\Steps\CollectStep;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class StatusesController extends FOSRestController
{
    /**
     * Get statuses.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get statuses",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    404 = "Returned when statuses are not found",
     *  }
     * )
     *
     * @Get("/collect_steps/{collectStepId}/statuses")
     * @ParamConverter("collectStep", options={"mapping": {"collectStepId": "id"}, "repository_method": "find", "map_method_signature": true})
     * @View(statusCode=200, serializerGroups={"Statuses"})
     */
    public function getStatusesAction(CollectStep $collectStep)
    {
        return $collectStep->getStatuses();
    }
}
