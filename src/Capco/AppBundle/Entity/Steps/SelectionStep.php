<?php

namespace Capco\AppBundle\Entity\Steps;

use Capco\AppBundle\Traits\VoteThresholdTrait;
use Capco\AppBundle\Traits\VoteTypeTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Capco\AppBundle\Validator\Constraints as CapcoAssert;
use Capco\AppBundle\Entity\Selection;
use Capco\AppBundle\Model\IndexableInterface;

/**
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\SelectionStepRepository")
 * @CapcoAssert\HasOnlyOneSelectionPerProposal()
 */
class SelectionStep extends AbstractStep implements IndexableInterface
{
    use VoteThresholdTrait;
    use VoteTypeTrait;

    const VOTE_TYPE_DISABLED = 0;
    const VOTE_TYPE_SIMPLE = 1;
    const VOTE_TYPE_BUDGET = 2;

    public static $voteTypeLabels = [
        self::VOTE_TYPE_DISABLED => 'step.selection.vote_type.disabled',
        self::VOTE_TYPE_SIMPLE => 'step.selection.vote_type.simple',
        self::VOTE_TYPE_BUDGET => 'step.selection.vote_type.budget',
    ];

    public static $sort = ['old', 'last', 'votes', 'comments', 'random'];

    public static $sortLabels = [
        'comments' => 'step.sort.comments',
        'last' => 'step.sort.last',
        'old' => 'step.sort.old',
        'random' => 'step.sort.random',
        'votes' => 'step.sort.votes',
    ];

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Selection", mappedBy="selectionStep", cascade={"persist"}, orphanRemoval=true)
     */
    private $selections;

    /**
     * @ORM\Column(name="contributors_count", type="integer")
     */
    private $contributorsCount = 0;

    /**
     * @ORM\Column(name="proposals_hidden", type="boolean", nullable=false, options={"default" = false})
     */
    private $proposalsHidden = false;

    /**
     * @ORM\Column(name="allowing_progess_steps", type="boolean")
     */
    private $allowingProgressSteps = false;

    /**
     * @ORM\Column(name="default_sort", type="string", nullable=false)
     * @Assert\Choice(choices={"old","last","votes","comments","random"})
     */
    private $defaultSort = 'random';

    public function __construct()
    {
        parent::__construct();
        $this->selections = new ArrayCollection();
    }

    public function addSelection(Selection $selection)
    {
        if (!$this->selections->contains($selection)) {
            $this->selections[] = $selection;
            $selection->setSelectionStep($this);
        }

        return $this;
    }

    public function removeSelection(Selection $selection)
    {
        $this->selections->removeElement($selection);
    }

    public function getSelections()
    {
        return $this->selections;
    }

    /**
     * @return mixed
     */
    public function getContributorsCount()
    {
        return $this->contributorsCount;
    }

    /**
     * @param mixed $contributorsCount
     *
     * @return $this
     */
    public function setContributorsCount($contributorsCount)
    {
        $this->contributorsCount = $contributorsCount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultSort()
    {
        return $this->defaultSort;
    }

    /**
     * @param mixed $defaultSort
     *
     * @return $this
     */
    public function setDefaultSort($defaultSort)
    {
        $this->defaultSort = $defaultSort;

        return $this;
    }

    public function isAllowingProgressSteps(): bool
    {
        return $this->allowingProgressSteps;
    }

    public function setAllowingProgressSteps(bool $allowingProgressSteps): self
    {
        $this->allowingProgressSteps = $allowingProgressSteps;

        return $this;
    }

    public function getType()
    {
        return 'selection';
    }

    public function isSelectionStep(): bool
    {
        return true;
    }

    public function getProposalForm()
    {
        if (count($this->getSelections()) > 0) {
            return $this->getSelections()[0]->getProposal()->getProposalForm();
        }

        return;
    }

    public function getProposals()
    {
        $proposals = [];
        foreach ($this->selections as $selection) {
            $proposals[] = $selection->getProposal();
        }

        return $proposals;
    }

    public function getProposalsIds()
    {
        $ids = array_filter(array_map(function ($value) {
            return $value->getProposal() ? $value->getProposal()->getId() : null;
        }, $this->getSelections()->getValues()),
            function ($value) {
                return $value !== null;
            });

        return $ids;
    }

    public function isProposalsHidden() : bool
    {
        return $this->proposalsHidden;
    }

    public function setProposalsHidden(bool $proposalsHidden) : self
    {
        $this->proposalsHidden = $proposalsHidden;

        return $this;
    }

    public function canShowProposals() : bool
    {
        return !$this->isProposalsHidden() || $this->getStartAt() <= new \DateTime();
    }

    public function isIndexable(): bool
    {
        return $this->getIsEnabled();
    }
}
