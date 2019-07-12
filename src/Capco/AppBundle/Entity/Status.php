<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Enum\StatusColor;
use Capco\AppBundle\Traits\ColorableTrait;
use Capco\AppBundle\Traits\PositionableTrait;
use Capco\AppBundle\Traits\TimestampableTrait;
use Capco\AppBundle\Traits\UuidTrait;
use Capco\AppBundle\Validator\Constraints as CapcoAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="status")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\StatusRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Status
{
    use UuidTrait;
    use TimestampableTrait;
    use PositionableTrait;
    use ColorableTrait;

    public static $statuses = [
        'normal' => StatusColor::NORMAL,
        'primary' => StatusColor::PRIMARY,
        'success' => StatusColor::SUCCESS,
        'info' => StatusColor::INFO,
        'warning' => StatusColor::WARNING,
        'danger' => StatusColor::DANGER
    ];

    public static $statusesLabels = [
        'statuses.labels.normal' => StatusColor::NORMAL,
        'statuses.labels.primary' => StatusColor::PRIMARY,
        'statuses.labels.success' => StatusColor::SUCCESS,
        'statuses.labels.info' => StatusColor::INFO,
        'statuses.labels.warning' => StatusColor::WARNING,
        'statuses.labels.danger' => StatusColor::DANGER
    ];

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field={"name"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Steps\AbstractStep", inversedBy="statuses", cascade={"persist"})
     * @ORM\JoinColumn(name="step_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     * @CapcoAssert\IsCollectOrSelectionStep()
     */
    private $step;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Proposal", mappedBy="status", cascade={"persist"})
     */
    private $proposals;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->updatedAt = new \DateTime();
        $this->proposals = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getId() ? $this->getName() : 'New status';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function setStep(AbstractStep $step)
    {
        $this->step = $step;

        return $this;
    }

    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * Add proposal.
     *
     * @param Proposal $proposal
     */
    public function addProposal(Proposal $proposal)
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
        }

        return $this;
    }

    /**
     * Remove proposal.
     *
     * @param Proposal $proposal
     */
    public function removeProposal(Proposal $proposal)
    {
        $this->proposals->removeElement($proposal);
    }
}
