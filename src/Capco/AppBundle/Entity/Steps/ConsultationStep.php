<?php

namespace Capco\AppBundle\Entity\Steps;

use Capco\AppBundle\Entity\Interfaces\ParticipativeStepInterface;
use Capco\AppBundle\Traits\TimelessStepTrait;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Capco\AppBundle\Model\IndexableInterface;

/**
 * Class ConsultationStep.
 *
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\ConsultationStepRepository")
 */
class ConsultationStep extends AbstractStep implements IndexableInterface, ParticipativeStepInterface
{
    use TimelessStepTrait;

    public function isIndexable()
    {
        return $this->getIsEnabled();
    }

    /**
     * @var int
     *
     * @ORM\Column(name="opinion_count", type="integer")
     */
    private $opinionCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="trashed_opinion_count", type="integer")
     */
    private $trashedOpinionCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="opinion_versions_count", type="integer")
     */
    private $opinionVersionsCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="trashed_opinion_versions_count", type="integer")
     */
    private $trashedOpinionVersionsCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="argument_count", type="integer")
     */
    private $argumentCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="trashed_argument_count", type="integer")
     */
    private $trashedArgumentCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="sources_count", type="integer")
     */
    private $sourcesCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="trashed_sources_count", type="integer")
     */
    private $trashedSourceCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="votes_count", type="integer")
     */
    private $votesCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="contributors_count", type="integer")
     */
    private $contributorsCount = 0;

    /**
     * @var
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Opinion", mappedBy="step",  cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $opinions;

    /**
     * @var
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Steps\ConsultationStepType")
     * @ORM\JoinColumn(name="consultation_step_type_id", onDelete="SET NULL", nullable=true)
     */
    private $consultationStepType;

    public function __construct()
    {
        parent::__construct();
        $this->opinions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getOpinionCount()
    {
        return $this->opinionCount;
    }

    /**
     * @param $opinionCount
     *
     * @return $this
     */
    public function setOpinionCount($opinionCount)
    {
        $this->opinionCount = $opinionCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getTrashedOpinionCount()
    {
        return $this->trashedOpinionCount;
    }

    /**
     * @param $trashedOpinionCount
     *
     * @return $this
     */
    public function setTrashedOpinionCount($trashedOpinionCount)
    {
        $this->trashedOpinionCount = $trashedOpinionCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getOpinionVersionsCount()
    {
        return $this->opinionVersionsCount;
    }

    /**
     * @param int $opinionVersionsCount
     */
    public function setOpinionVersionsCount($opinionVersionsCount)
    {
        $this->opinionVersionsCount = $opinionVersionsCount;
    }

    /**
     * @return int
     */
    public function getTrashedOpinionVersionsCount()
    {
        return $this->trashedOpinionVersionsCount;
    }

    /**
     * @param int $trashedOpinionVersionsCount
     */
    public function setTrashedOpinionVersionsCount($trashedOpinionVersionsCount)
    {
        $this->trashedOpinionVersionsCount = $trashedOpinionVersionsCount;
    }

    /**
     * @return int
     */
    public function getArgumentCount()
    {
        return $this->argumentCount;
    }

    /**
     * @param $argumentCount
     *
     * @return $this
     */
    public function setArgumentCount($argumentCount)
    {
        $this->argumentCount = $argumentCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getTrashedArgumentCount()
    {
        return $this->trashedArgumentCount;
    }

    /**
     * @param $trashedArgumentCount
     *
     * @return $this
     */
    public function setTrashedArgumentCount($trashedArgumentCount)
    {
        $this->trashedArgumentCount = $trashedArgumentCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getSourcesCount()
    {
        return $this->sourcesCount;
    }

    /**
     * @param int $sourcesCount
     */
    public function setSourcesCount($sourcesCount)
    {
        $this->sourcesCount = $sourcesCount;
    }

    /**
     * @return int
     */
    public function getTrashedSourceCount()
    {
        return $this->trashedSourceCount;
    }

    /**
     * @param int $trashedSourceCount
     */
    public function setTrashedSourceCount($trashedSourceCount)
    {
        $this->trashedSourceCount = $trashedSourceCount;
    }

    /**
     * @return int
     */
    public function getVotesCount()
    {
        return $this->votesCount;
    }

    /**
     * @param int $votesCount
     *
     * @return $this
     */
    public function setVotesCount($votesCount)
    {
        $this->votesCount = $votesCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getContributorsCount()
    {
        return $this->contributorsCount;
    }

    /**
     * @param int $contributorsCount
     *
     * @return $this
     */
    public function setContributorsCount($contributorsCount)
    {
        $this->contributorsCount = $contributorsCount;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getOpinions()
    {
        return $this->opinions;
    }

    /**
     * @param $opinion
     *
     * @return $this
     */
    public function addOpinion($opinion)
    {
        if (!$this->opinions->contains($opinion)) {
            $this->opinions->add($opinion);
        }

        return $this;
    }

    /**
     * @param $opinion
     *
     * @return $this
     */
    public function removeOpinion($opinion)
    {
        $this->opinions->removeElement($opinion);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getConsultationStepType()
    {
        return $this->consultationStepType;
    }

    /**
     * @param mixed $consultationStepType
     */
    public function setConsultationStepType($consultationStepType)
    {
        $this->consultationStepType = $consultationStepType;
    }

    // **************************** Custom methods *******************************

    public function getProjectId()
    {
        if (!$this->projectAbstractStep) {
            return;
        }

        return $this->projectAbstractStep
                    ->getProject()
                    ->getId()
            ;
    }

    public function getType()
    {
        return 'consultation';
    }

    public function isConsultationStep()
    {
        return true;
    }

    /**
     * @return int
     */
    public function getContributionsCount()
    {
        return $this->argumentCount + $this->opinionCount + $this->trashedArgumentCount + $this->trashedOpinionCount + $this->opinionVersionsCount + $this->trashedOpinionVersionsCount + $this->sourcesCount + $this->trashedSourceCount;
    }

    public function getLabelTitle()
    {
        $label = $this->getTitle();
        if ($this->getProject()) {
            $label = $this->getProject()->getTitle().' - '.$label;
        }

        return $label;
    }

    public function isParticipative() : bool
    {
        return true;
    }
}
