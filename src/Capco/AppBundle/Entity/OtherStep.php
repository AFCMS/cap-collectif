<?php

namespace Capco\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class OtherStep.
 *
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\OtherStepRepository")
 */
class OtherStep extends AbstractStep
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getType()
    {
        return 'other';
    }

    public function isOtherStep()
    {
        return true;
    }
}
