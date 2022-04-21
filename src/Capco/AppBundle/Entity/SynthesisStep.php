<?php

namespace Capco\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class SynthesisStep.
 *
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\SynthesisStepRepository")
 */
class SynthesisStep extends AbstractStep
{

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Synthesis\Synthesis")
     * @ORM\JoinColumn(name="synthesis_id", referencedColumnName="id", onDelete="SET NULL")
     */
    private $synthesis = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getSynthesis()
    {
        return $this->synthesis;
    }

    /**
     * @param mixed $synthesis
     */
    public function setSynthesis($synthesis)
    {
        $this->synthesis = $synthesis;
    }

    // **************************** Custom methods *******************************

    public function getType()
    {
        return 'synthesis';
    }

    public function isSynthesisStep()
    {
        return true;
    }


}
