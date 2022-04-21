<?php

namespace Capco\AppBundle\Entity\Steps;

use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Entity\Status;
use Capco\AppBundle\Traits\VoteTypeTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Capco\AppBundle\Model\IndexableInterface;

/**
 * @ORM\Table(name="collect_step")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\CollectStepRepository")
 */
class CollectStep extends AbstractStep implements IndexableInterface
{
    use VoteTypeTrait;

    public static $sort = ['old', 'last', 'votes', 'comments', 'random'];

    public static $sortLabels = [
        'comments' => 'step.sort.comments',
        'last' => 'step.sort.last',
        'old' => 'step.sort.old',
        'random' => 'step.sort.random',
        'votes' => 'step.sort.votes',
    ];

    /**
     * @ORM\OneToOne(targetEntity="Capco\AppBundle\Entity\ProposalForm", mappedBy="step", cascade={"persist", "remove"})
     */
    private $proposalForm = null;

    /**
     * @ORM\Column(name="proposals_count", type="integer", nullable=false)
     */
    private $proposalsCount = 0;

    /**
     * @ORM\Column(name="contributors_count", type="integer", nullable=false)
     */
    private $contributorsCount = 0;

    /**
     * @ORM\OneToOne(targetEntity="Capco\AppBundle\Entity\Status", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="default_status_id", nullable=true)
     */
    private $defaultStatus = null;

    /**
     * @ORM\Column(name="private", type="boolean", nullable=false)
     */
    private $private = false;

    /**
     * @ORM\Column(name="default_sort", type="string", nullable=false)
     * @Assert\Choice(choices={"old","last","votes","comments","random"})
     */
    private $defaultSort = 'random';

    public function getProposalsCount(): int
    {
        return $this->proposalsCount ?? 0;
    }

    public function setProposalsCount(int $proposalsCount): self
    {
        $this->proposalsCount = $proposalsCount;

        return $this;
    }

    public function getContributorsCount(): int
    {
        return $this->contributorsCount ?? 0;
    }

    public function setContributorsCount(int $contributorsCount): self
    {
        $this->contributorsCount = $contributorsCount;

        return $this;
    }

    public function getDefaultStatus()
    {
        return $this->defaultStatus;
    }

    public function setDefaultStatus(Status $defaultStatus = null): self
    {
        $this->defaultStatus = $defaultStatus;

        return $this;
    }

    public function getProposalForm()
    {
        return $this->proposalForm;
    }

    public function setProposalForm(ProposalForm $proposalForm = null): self
    {
        if ($proposalForm) {
            $proposalForm->setStep($this);
        }
        $this->proposalForm = $proposalForm;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getDefaultSort(): string
    {
        return $this->defaultSort;
    }

    public function setDefaultSort(string $defaultSort): self
    {
        $this->defaultSort = $defaultSort;

        return $this;
    }

    // **************************** Custom methods *******************************

    public function getType()
    {
        return 'collect';
    }

    public function isCollectStep(): bool
    {
        return true;
    }

    public function isIndexable(): bool
    {
        return $this->getIsEnabled();
    }
}
