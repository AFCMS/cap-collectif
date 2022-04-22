<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Elasticsearch\IndexableInterface;
use Capco\AppBundle\Entity\Interfaces\VotableInterface;
use Capco\AppBundle\Entity\Steps\AbstractStep;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\ProjectAbstractStep;
use Capco\AppBundle\Traits\MetaDescriptionCustomCodeTrait;
use Capco\AppBundle\Traits\UuidTrait;
use Capco\AppBundle\Validator\Constraints as CapcoAssert;
use Capco\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Project.
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\ProjectRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Project implements IndexableInterface
{
    use UuidTrait, MetaDescriptionCustomCodeTrait;

    const FILTER_ALL = 'all';

    const SORT_ORDER_PUBLISHED_AT = 0;
    const SORT_ORDER_CONTRIBUTIONS_COUNT = 1;

    const OPENING_STATUS_FUTURE = 0;
    const OPENING_STATUS_OPENED = 1;
    const OPENING_STATUS_ENDED = 2;

    const OPINION_TERM_OPINION = 0;
    const OPINION_TERM_ARTICLE = 1;

    public const VISIBILITY_ME = 0;
    public const VISIBILITY_ADMIN = 1;
    public const VISIBILITY_PUBLIC = 2;

    public const VISIBILITY = [
        'myself' => self::VISIBILITY_ME,
        'private' => self::VISIBILITY_ADMIN,
        'public' => self::VISIBILITY_PUBLIC,
    ];

    public static $sortOrder = [
        'date' => self::SORT_ORDER_PUBLISHED_AT,
        'popularity' => self::SORT_ORDER_CONTRIBUTIONS_COUNT,
    ];

    public static $sortOrderLabels = [
        'date' => 'project.sort.published_at',
        'popularity' => 'project.sort.contributions_nb',
    ];

    public static $openingStatuses = [
        'future' => self::OPENING_STATUS_FUTURE,
        'opened' => self::OPENING_STATUS_OPENED,
        'ended' => self::OPENING_STATUS_ENDED,
    ];

    public static $opinionTermsLabels = [
         'project.opinion_term.opinion' => self::OPINION_TERM_OPINION,
         'project.opinion_term.article' => self::OPINION_TERM_ARTICLE,
    ];

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(name="external_link", type="text", length=255, nullable=true)
     */
    private $externalLink;

    /**
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @ORM\Column(length=255)
     */
    private $slug;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="exportable", type="boolean")
     */
    private $exportable = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="published_at", type="datetime", nullable=false)
     * @Assert\NotNull()
     * @Assert\DateTime()
     */
    private $publishedAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field={"title", "Author", "themes", "steps", "media"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $Author;

    /**
     * @var
     * @ORM\ManyToMany(targetEntity="Capco\AppBundle\Entity\Theme", inversedBy="projects", cascade={"persist"})
     * @ORM\JoinTable(name="theme_project")
     */
    private $themes;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Steps\ProjectAbstractStep", mappedBy="project",  cascade={"persist", "remove"}, orphanRemoval = true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @CapcoAssert\HasOnlyOneSelectionStepAllowingProgressSteps()
     */
    private $steps;

    /**
     * @var
     *
     * @ORM\ManyToOne(targetEntity="Capco\MediaBundle\Entity\Media", cascade={"persist"})
     * @ORM\JoinColumn(name="cover_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $Cover;

    /**
     * @ORM\Column(name="video", type="string", nullable = true)
     */
    private $video = null;

    /**
     * @var
     * @ORM\ManyToMany(targetEntity="Capco\AppBundle\Entity\Event", mappedBy="projects", cascade={"persist"})
     */
    private $events;

    /**
     * @var
     * @ORM\ManyToMany(targetEntity="Capco\AppBundle\Entity\Post", mappedBy="projects", cascade={"persist"})
     */
    private $posts;

    /**
     * @var int
     *
     * @ORM\Column(name="participants_count", type="integer")
     */
    private $participantsCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="contributions_count", type="integer")
     */
    private $contributionsCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="votes_count", type="integer")
     */
    private $votesCount = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="opinions_ranking_threshold", type="integer", nullable=true)
     */
    private $opinionsRankingThreshold = null;

    /**
     * @var int
     *
     * @ORM\Column(name="versions_ranking_threshold", type="integer", nullable=true)
     */
    private $versionsRankingThreshold = null;

    /**
     * @ORM\Column(name="include_author_in_ranking", type="boolean")
     */
    private $includeAuthorInRanking = false;

    /**
     * @ORM\Column(name="opinion_term", type="integer", nullable=false)
     */
    private $opinionTerm = self::OPINION_TERM_OPINION;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\ProjectType", inversedBy="projects")
     * @ORM\JoinColumn(name="project_type_id", referencedColumnName="id", nullable=true)
     */
    private $projectType;

    /**
     * @ORM\Column(name="visibility", type="integer", nullable=false)
     */
    private $visibility = self::VISIBILITY_ME;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->themes = new ArrayCollection();
        $this->steps = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->updatedAt = new \Datetime();
        $this->publishedAt = new \DateTime();
    }

    public function __toString()
    {
        return $this->getId() ? $this->getTitle() : 'New project';
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Project
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getExternalLink()
    {
        return $this->externalLink;
    }

    public function setExternalLink(string $link = null): self
    {
        $this->externalLink = $link;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get isEnabled.
     *
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Set isEnabled.
     *
     * @param bool $isEnabled
     *
     * @return Project
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExportable()
    {
        return $this->exportable;
    }

    /**
     * @param bool $exportable
     */
    public function setExportable($exportable)
    {
        $this->exportable = $exportable;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $value): self
    {
        $this->createdAt = $value;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $value): self
    {
        $this->updatedAt = $value;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime $publishedAt
     *
     * @return $this
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor()
    {
        return $this->Author;
    }

    /**
     * @param $Author
     *
     * @return $this
     */
    public function setAuthor($Author)
    {
        $this->Author = $Author;

        return $this;
    }

    /**
     * Get themes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getThemes()
    {
        return $this->themes;
    }

    /**
     * Add theme.
     *
     * @param \Capco\AppBundle\Entity\Theme $theme
     *
     * @return Project
     */
    public function addTheme(Theme $theme)
    {
        if (!$this->themes->contains($theme)) {
            $this->themes->add($theme);
        }
        $theme->addProject($this);

        return $this;
    }

    /**
     * Remove theme.
     *
     * @param \Capco\AppBundle\Entity\Theme $theme
     *
     * @return $this
     */
    public function removeTheme(Theme $theme)
    {
        $this->themes->removeElement($theme);
        $theme->removeProject($this);

        return $this;
    }

    public function getRealSteps(): array
    {
        $steps = [];
        foreach ($this->steps as $qaq) {
            if ($qaq->getStep()) {
                $steps[] = $qaq->getStep();
            }
        }

        return $steps;
    }

    /**
     * Get steps.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * Reset steps.
     *
     * @return $this
     */
    public function resetSteps()
    {
        $this->steps = new ArrayCollection();

        return $this;
    }

    /**
     * Add step.
     *
     * @param ProjectAbstractStep $step
     *
     * @return Project
     */
    public function addStep(ProjectAbstractStep $step)
    {
        $step->setProject($this);
        $this->steps[] = $step;

        return $this;
    }

    /**
     * Remove step.
     *
     * @param ProjectAbstractStep $step
     *
     * @return $this
     */
    public function removeStep(ProjectAbstractStep $step)
    {
        $this->steps->removeElement($step);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCover()
    {
        return $this->Cover;
    }

    /**
     * @param mixed $cover
     */
    public function setCover($cover)
    {
        $this->Cover = $cover;

        return $this;
    }

    /**
     * @return string
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param string $video
     */
    public function setVideo($video)
    {
        $this->video = $video;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    public function addEvent(Event $event): self
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
        }

        return $this;
    }

    /**
     * @param Event $event
     *
     * @return $this
     */
    public function removeEvent(Event $event)
    {
        $this->events->removeElement($event);

        return $this;
    }

    /**
     * Get Posts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Add Post.
     *
     * @param Post $post
     *
     * @return $this
     */
    public function addPost(Post $post)
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
        }

        return $this;
    }

    /**
     * Remove post.
     *
     * @param Post $post
     *
     * @return $this
     */
    public function removePost(Post $post)
    {
        $this->posts->removeElement($post);

        return $this;
    }

    /**
     * @return int
     */
    public function getParticipantsCount()
    {
        return $this->participantsCount;
    }

    /**
     * @param int $participantsCount
     */
    public function setParticipantsCount($participantsCount)
    {
        $this->participantsCount = $participantsCount;
    }

    /**
     * @return int
     */
    public function getContributionsCount()
    {
        return $this->contributionsCount;
    }

    /**
     * @param int $contributionsCount
     */
    public function setContributionsCount($contributionsCount)
    {
        $this->contributionsCount = $contributionsCount;
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
     */
    public function setVotesCount($votesCount)
    {
        $this->votesCount = $votesCount;
    }

    /**
     * @return int
     */
    public function getOpinionsRankingThreshold()
    {
        return $this->opinionsRankingThreshold;
    }

    /**
     * @param int $opinionsRankingThreshold
     */
    public function setOpinionsRankingThreshold($opinionsRankingThreshold)
    {
        $this->opinionsRankingThreshold = $opinionsRankingThreshold;
    }

    /**
     * @return int
     */
    public function getVersionsRankingThreshold()
    {
        return $this->versionsRankingThreshold;
    }

    /**
     * @param int $versionsRankingThreshold
     */
    public function setVersionsRankingThreshold($versionsRankingThreshold)
    {
        $this->versionsRankingThreshold = $versionsRankingThreshold;
    }

    /**
     * @return int
     */
    public function getIncludeAuthorInRanking()
    {
        return $this->includeAuthorInRanking;
    }

    /**
     * @param int $includeAuthorInRanking
     */
    public function setIncludeAuthorInRanking($includeAuthorInRanking)
    {
        $this->includeAuthorInRanking = $includeAuthorInRanking;
    }

    /**
     * @return array
     */
    public function getOpinionTerm()
    {
        return $this->opinionTerm;
    }

    /**
     * @param array $opinionTerm
     */
    public function setOpinionTerm($opinionTerm)
    {
        $this->opinionTerm = $opinionTerm;
    }

    // ******************** Custom methods ******************************

    /**
     * @return bool
     */
    public function canDisplay()
    {
        return $this->isEnabled;
    }

    /**
     * @return bool
     */
    public function canContribute()
    {
        return $this->isEnabled;
    }

    /**
     * @return int
     */
    public function getTotalOpinionsCount()
    {
        $count = 0;
        foreach ($this->steps as $step) {
            if ($step->getStep()->isConsultationStep()) {
                $count += ($step->getStep()->getOpinionCount() + $step->getStep()->getTrashedOpinionCount());
            }
        }

        return $count;
    }

    /**
     * @return int
     */
    public function getTotalProposalsCount()
    {
        $count = 0;
        foreach ($this->steps as $step) {
            if ($step->getStep()->isCollectStep()) {
                $count += $step->getStep()->getProposalsCount();
            }
        }

        return $count;
    }

    /**
     * @return int
     */
    public function getTotalVersionsCount()
    {
        $count = 0;
        foreach ($this->steps as $step) {
            if ($step->getStep()->isConsultationStep()) {
                $count += ($step->getStep()->getOpinionVersionsCount() + $step->getStep()->getTrashedOpinionVersionsCount());
            }
        }

        return $count;
    }

    /**
     * @return int
     */
    public function getTotalArgumentsCount()
    {
        $count = 0;
        foreach ($this->steps as $step) {
            if ($step->getStep()->isConsultationStep()) {
                $count += ($step->getStep()->getArgumentCount() + $step->getStep()->getTrashedArgumentCount());
            }
        }

        return $count;
    }

    public function getTotalSourcesCount()
    {
        $count = 0;
        foreach ($this->steps as $step) {
            if ($step->getStep()->isConsultationStep()) {
                $count += ($step->getStep()->getSourcesCount() + $step->getStep()->getTrashedSourceCount());
            }
        }

        return $count;
    }

    /**
     * @return int
     */
    public function getTotalRepliesCount()
    {
        $count = 0;
        foreach ($this->steps as $step) {
            if ($step->getStep()->isQuestionnaireStep()) {
                $count += ($step->getStep()->getRepliesCount());
            }
        }

        return $count;
    }

    public function getFirstStep(): ?AbstractStep
    {
        $first = null;
        if (!empty($this->steps)) {
            $first = $this->steps[0];
            foreach ($this->steps as $step) {
                if ($step->getPosition() < $first->getPosition()) {
                    $first = $step;
                }
            }
        }

        return null !== $first ? $first->getStep() : null;
    }

    public function getStartAt(): ?\DateTime
    {
        return $this->getCurrentStep() ? $this->getCurrentStep()->getStartAt() : null;
    }

    public function getCurrentStep()
    {
        foreach ($this->steps as $step) {
            if ($step->getStep()->isOpen()) {
                return $step->getStep();
            }
        }
        foreach ($this->steps as $step) {
            if ($step->getStep()->isFuture()) {
                return $step->getStep();
            }
        }
        $reversedSteps = array_reverse($this->steps->toArray());
        foreach ($reversedSteps as $step) {
            if ($step->getStep()->isClosed()) {
                return $step->getStep();
            }
        }
    }

    public function isClosed(): bool
    {
        return $this->getCurrentStep() ? $this->getCurrentStep()->isClosed() : true;
    }

    public function getConsultationStepOpen()
    {
        foreach ($this->steps as $step) {
            if ($step->getStep()->isConsultationStep() && $step->getStep()->isOpen()) {
                return $step->getStep();
            }
        }
    }

    public function getFirstCollectStep(): ?CollectStep
    {
        foreach ($this->steps as $step) {
            if ($step->getStep()->isCollectStep()) {
                return $step->getStep();
            }
        }
    }

    public function getExportableSteps()
    {
        $steps = [];

        foreach ($this->steps as $pas) {
            $step = $pas->getStep();
            if ($step->isConsultationStep() || $step->isCollectStep() || $step->isSelectionStep() || $step->isQuestionnaireStep()) {
                $steps[] = $pas;
            }
        }

        return $steps;
    }

    public function hasParticipativeStep(): bool
    {
        foreach ($this->steps as $pas) {
            $step = $pas->getStep();
            if ($step->isParticipative()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return null|ProjectType
     */
    public function getProjectType()
    {
        return $this->projectType;
    }

    public function setProjectType(ProjectType $projectType = null): self
    {
        $this->projectType = $projectType;

        return $this;
    }

    // ************************** Lifecycle **************************************

    /**
     * @ORM\PreRemove
     */
    public function deleteProject()
    {
        if ($this->themes->count() > 0) {
            foreach ($this->themes as $theme) {
                $theme->removeProject($this);
            }
        }
    }

    public function isIndexable(): bool
    {
        return $this->getIsEnabled();
    }

    public static function getElasticsearchTypeName(): string
    {
        return 'project';
    }

    public static function getElasticsearchSerializationGroups(): array
    {
        return ['Elasticsearch'];
    }

    public function hasVotableStep(): bool
    {
        foreach ($this->steps as $step) {
            if ($step->getStep() instanceof VotableInterface) {
                return true;
            }
        }

        return false;
    }

    public function getVisibility(): int
    {
        return $this->visibility;
    }

    public function setVisibility(int $visibility): self
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function isPublic(): bool
    {
        return self::VISIBILITY_PUBLIC === $this->getVisibility();
    }
}
