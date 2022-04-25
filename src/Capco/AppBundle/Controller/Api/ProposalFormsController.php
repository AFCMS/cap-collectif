<?php

namespace Capco\AppBundle\Controller\Api;

use Capco\AppBundle\Entity\ProposalForm;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

class ProposalFormsController extends AbstractFOSRestController
{
    /**
     * Get a proposal form.
     * )
     *
     * @Get("/proposal_forms/{id}")
     * @Entity("proposalForm", options={"mapping": {"id": "id"}, "repository_method": "getOne", "map_method_signature": true})
     * @View(statusCode=200, serializerGroups={"ProposalForms", "Questions", "Districts", "DistrictDetails"})
     */
    public function getProposalFormAction(ProposalForm $proposalForm)
    {
        return $proposalForm;
    }
}
