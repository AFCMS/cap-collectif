<?php

namespace Capco\AppBundle\Controller\Api;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\Annotations\Get;

class DistrictsController extends FOSRestController
{
    /**
     * Get all districts.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get all districts",
     *  statusCodes={
     *    200 = "Returned when successful",
     *    404 = "Returned when districts are not found",
     *  }
     * )
     *
     * @Get("/districts")
     * @View(statusCode=200, serializerGroups={"Districts"})
     *
     * @return array
     */
    public function getDistrictsAction()
    {
        $districtRepository = $this->getDoctrine()->getRepository('CapcoAppBundle:District');
        $districts          = $districtRepository->findAll();

        return $districts;
    }
}
