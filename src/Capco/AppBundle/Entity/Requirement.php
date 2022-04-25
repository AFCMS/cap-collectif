<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Traits\PositionableTrait;
use Capco\AppBundle\Traits\UuidTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="requirement")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\RequirementRepository")
 */
class Requirement
{
    use UuidTrait;
    use PositionableTrait;

    public const CHECKBOX = 'CHECKBOX';
    public const FIRSTNAME = 'FIRSTNAME';
    public const LASTNAME = 'LASTNAME';
    public const PHONE = 'PHONE';
    public const DATE_OF_BIRTH = 'DATE_OF_BIRTH';
    public const POSTAL_ADDRESS = 'POSTAL_ADDRESS';

    public static $requirementsLabels = [
        self::CHECKBOX => 'check-box',
        self::FIRSTNAME => 'form.label_firstname',
        self::LASTNAME => 'group.title',
        self::PHONE => 'mobile-phone',
        self::DATE_OF_BIRTH => 'form.label_date_of_birth',
        self::POSTAL_ADDRESS => 'admin.fields.event.address',
    ];

    /**
     * @ORM\Column(name="type", type="string")
     * @Assert\NotNull()
     */
    private $type = self::CHECKBOX;

    /**
     * @ORM\Column(name="label", type="string", nullable=true)
     */
    private $label;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Steps\AbstractStep", inversedBy="requirements")
     * @ORM\JoinColumn(name="step_id", referencedColumnName="id", nullable=false)
     */
    private $step;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStep(): AbstractStep
    {
        return $this->step;
    }

    public function setStep(AbstractStep $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label = null): self
    {
        $this->label = $label;

        return $this;
    }
}
