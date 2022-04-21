<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Traits\AnswerableTrait;
use Capco\AppBundle\Traits\ValidableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Capco\AppBundle\Traits\TrashableTrait;
use Capco\AppBundle\Traits\EnableTrait;
use Capco\AppBundle\Traits\SluggableTitleTrait;
use Capco\AppBundle\Traits\TimestampableTrait;
use Capco\AppBundle\Traits\VotableOkNokMitigeTrait;
use Capco\AppBundle\Traits\DiffableTrait;
use Capco\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Capco\AppBundle\Model\HasDiffInterface;
use Capco\AppBundle\Entity\Interfaces\OpinionContributionInterface;
use Capco\AppBundle\Traits\ExpirableTrait;
use Capco\AppBundle\Traits\UuidTrait;
use Capco\AppBundle\Entity\Opinion;

/**
 * @ORM\Table(name="opinion_version")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\OpinionVersionRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class OpinionVersion implements OpinionContributionInterface, HasDiffInterface
{
    use UuidTrait;
    use TrashableTrait;
    use EnableTrait;
    use SluggableTitleTrait;
    use TimestampableTrait;
    use VotableOkNokMitigeTrait;
    use ValidableTrait;
    use AnswerableTrait;
    use DiffableTrait;
    use ExpirableTrait;

    public function getKind(): string {
        return 'version';
    }

    public function getRelated(): Opinion {
        return $this->parent;
    }

    /**
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank()
     */
    protected $body;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User", inversedBy="opinionVersions")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $author;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Argument", mappedBy="opinionVersion", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $arguments;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Source", mappedBy="opinionVersion", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $sources;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Opinion", inversedBy="versions", cascade={"persist"})
     * @ORM\JoinColumn(name="opinion_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $parent;

    /**
     * @ORM\Column(name="sources_count", type="integer")
     */
    protected $sourcesCount = 0;

    /**
     * @ORM\Column(name="arguments_count", type="integer")
     */
    protected $argumentsCount = 0;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Reporting", mappedBy="opinionVersion", cascade={"persist", "remove"})
     */
    protected $reports;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field={"title", "body", "comment"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\Column(name="ranking", type="integer", nullable=true)
     */
    protected $ranking = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
        $this->arguments = new ArrayCollection();
        $this->sources = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->reports = new ArrayCollection();
    }

    public function isIndexable()
    {
        return $this->isEnabled() && !$this->isExpired();
    }

    public function getReports()
    {
        return $this->reports;
    }

    public function addReport(Reporting $report)
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
        }

        return $this;
    }

    public function removeReport(Reporting $report)
    {
        $this->reports->removeElement($report);

        return $this;
    }

    public function __toString()
    {
        return $this->getId() ? $this->getTitle() : 'New opinion version';
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    public function setParent(Opinion $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param mixed $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param $argument
     *
     * @return $this
     */
    public function addArgument(Argument $argument)
    {
        if (!$this->arguments->contains($argument)) {
            $this->arguments->add($argument);
        }

        return $this;
    }

    /**
     * @param Argument $argument
     *
     * @return $this
     */
    public function removeArgument(Argument $argument)
    {
        $this->arguments->removeElement($argument);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSources()
    {
        return $this->sources;
    }

    /**
     * @param mixed $sources
     */
    public function setSources($sources)
    {
        $this->sources = $sources;
    }

    /**
     * @param $source
     *
     * @return $this
     */
    public function addSource($source)
    {
        if (!$this->sources->contains($source)) {
            $this->sources->add($source);
        }

        return $this;
    }

    /**
     * @param $source
     *
     * @return $this
     */
    public function removeSource($source)
    {
        $this->sources->removeElement($source);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourcesCount()
    {
        return $this->sourcesCount;
    }

    /**
     * @param mixed $sourcesCount
     */
    public function setSourcesCount($sourcesCount)
    {
        $this->sourcesCount = $sourcesCount;
    }

    /**
     * @return mixed
     */
    public function getArgumentsCount()
    {
        return $this->argumentsCount;
    }

    /**
     * @param mixed $argumentsCount
     */
    public function setArgumentsCount($argumentsCount)
    {
        $this->argumentsCount = $argumentsCount;
    }

    public function incrementSourcesCount()
    {
        ++$this->sourcesCount;

        return $this;
    }

    public function decrementSourcesCount()
    {
        --$this->sourcesCount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRanking()
    {
        return $this->ranking;
    }

    /**
     * @param mixed $ranking
     */
    public function setRanking($ranking)
    {
        $this->ranking = $ranking;
    }

    // ******************************* Custom methods **************************************

    public function userHasReport(User $user)
    {
        foreach ($this->reports as $report) {
            if ($report->getReporter() == $user) {
                return true;
            }
        }

        return false;
    }

    public function getArgumentForCount()
    {
        $i = 0;
        foreach ($this->arguments as $argument) {
            if ($argument->getType() === Argument::TYPE_FOR) {
                ++$i;
            }
        }

        return $i;
    }

    public function getArgumentAgainstCount()
    {
        $i = 0;
        foreach ($this->arguments as $argument) {
            if ($argument->getType() === Argument::TYPE_AGAINST) {
                ++$i;
            }
        }

        return $i;
    }

    public function getArgumentsCountByType($type)
    {
        if ($type === 'yes') {
            return $this->getArgumentForCount();
        }
        if ($type === 'no') {
            return $this->getArgumentAgainstCount();
        }

        return 0;
    }

    public function getOpinionType()
    {
        if ($this->parent) {
            return $this->parent->getOpinionType();
        }

        return;
    }

    public function getCommentSystem()
    {
        if ($this->parent) {
            return $this->parent->getCommentSystem();
        }

        return;
    }

    /**
     * @return bool
     */
    public function canDisplay()
    {
        return $this->enabled && $this->getParent()->canDisplay();
    }

    /**
     * @return bool
     */
    public function canContribute()
    {
        return $this->enabled && !$this->isTrashed && $this->getParent()->canContribute();
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->enabled && !$this->isTrashed && $this->parent->isPublished();
    }

    public function increaseArgumentsCount()
    {
        ++$this->argumentsCount;

        return $this;
    }

    public function decreaseArgumentsCount()
    {
        --$this->argumentsCount;

        return $this;
    }
}
