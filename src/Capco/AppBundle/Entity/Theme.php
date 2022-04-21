<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Model\IndexableInterface;
use Capco\AppBundle\Traits\MetaDescriptionCustomCodeTrait;
use Capco\AppBundle\Traits\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="theme")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\ThemeRepository")
 */
class Theme implements IndexableInterface
{
    use UuidTrait, MetaDescriptionCustomCodeTrait;

    const STATUS_CLOSED = 0;
    const STATUS_OPENED = 1;
    const STATUS_FUTURE = 2;

    const FILTER_ALL = 'all';

    public static $statuses = [
        'closed' => self::STATUS_CLOSED,
        'opened' => self::STATUS_OPENED,
        'future' => self::STATUS_FUTURE,
    ];

    public static $statusesLabels = [
        self::STATUS_CLOSED => 'theme.show.status.closed',
        self::STATUS_OPENED => 'theme.show.status.opened',
        self::STATUS_FUTURE => 'theme.show.status.future',
    ];

    /**
     * @var string
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @var string
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @ORM\Column(length=255)
     */
    private $slug;

    /**
     * @var string
     * @ORM\Column(name="teaser", type="string", length=255, nullable=true)
     */
    private $teaser;

    /**
     * @var bool
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * @var int
     * @ORM\Column(name="position", type="integer")
     * @Assert\NotNull()
     */
    private $position;

    /**
     * @var int
     * @ORM\Column(name="status", type="integer", nullable=true)
     */
    private $status;

    /**
     * @var string
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    private $body;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="SET NULL", nullable=true)
     */
    private $Author;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field={"title", "teaser", "position", "status", "body", "Media"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Capco\AppBundle\Entity\Project", mappedBy="themes", cascade={"persist"})
     */
    private $projects;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Idea", mappedBy="theme", cascade={"persist", "remove"})
     */
    private $ideas;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Proposal", mappedBy="theme", cascade={"persist", "remove"})
     */
    private $proposals;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Capco\AppBundle\Entity\Event", mappedBy="themes", cascade={"persist"})
     */
    private $events;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="Capco\AppBundle\Entity\Post", mappedBy="themes", cascade={"persist"})
     */
    private $posts;

    /**
     * @var
     *
     * @ORM\OneToOne(targetEntity="Capco\MediaBundle\Entity\Media", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $Media;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
        $this->ideas = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->updatedAt = new \Datetime();
    }

    public function __toString()
    {
        return $this->getId() ? $this->getTitle() : 'New theme';
    }

    public function isIndexable()
    {
        return $this->getIsEnabled();
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
     * @return Theme
     */
    public function setTitle($title)
    {
        $this->title = $title;

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
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get teaser.
     *
     * @return string
     */
    public function getTeaser()
    {
        return $this->teaser;
    }

    /**
     * Set teaser.
     *
     * @param string $teaser
     *
     * @return Theme
     */
    public function setTeaser($teaser)
    {
        $this->teaser = $teaser;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->Author;
    }

    /**
     * @param mixed $Author
     */
    public function setAuthor($Author)
    {
        $this->Author = $Author;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @param Project $project
     *
     * @return Theme
     */
    public function addProject(Project $project)
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
        }

        return $this;
    }

    /**
     * @param Project $project
     *
     * @return $this
     */
    public function removeProject(Project $project)
    {
        $this->projects->removeElement($project);

        return $this;
    }

    /**
     * Get ideas.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIdeas()
    {
        return $this->ideas;
    }

    /**
     * Add idea.
     *
     * @param Idea $idea
     *
     * @return Theme
     */
    public function addIdea(Idea $idea)
    {
        if (!$this->ideas->contains($idea)) {
            $this->ideas[] = $idea;
        }

        return $this;
    }

    /**
     * Remove idea.
     *
     * @param Idea $idea
     */
    public function removeIdea(Idea $idea)
    {
        $this->ideas->removeElement($idea);
    }

    /**
     * Get proposals.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProposals()
    {
        return $this->proposals;
    }

    /**
     * Add proposal.
     *
     * @param Proposal $proposal
     *
     * @return Theme
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

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Event $event
     *
     * @return Theme
     */
    public function addEvent(Event $event)
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
     * @return mixed
     */
    public function getMedia()
    {
        return $this->Media;
    }

    /**
     * @param mixed $media
     */
    public function setMedia($media)
    {
        $this->Media = $media;
    }

    // ********************** custom methods ****************************

    public function getBodyExcerpt($nb = 100)
    {
        $excerpt = substr($this->body, 0, $nb);
        $excerpt = $excerpt . '...';

        return $excerpt;
    }

    public function getTeaserExcerpt($nb = 100)
    {
        $excerpt = substr($this->teaser, 0, $nb);
        $excerpt = $excerpt . '...';

        return $excerpt;
    }

    public function canContribute()
    {
        return $this->isEnabled;
    }

    public function canDisplay()
    {
        return $this->isEnabled;
    }

    public function countEnabledProjects()
    {
        $count = 0;
        foreach ($this->projects as $project) {
            if ($project->getIsEnabled()) {
                ++$count;
            }
        }

        return $count;
    }

    public function countEnabledIdeas()
    {
        $count = 0;
        foreach ($this->ideas as $idea) {
            if ($idea->getIsEnabled()) {
                ++$count;
            }
        }

        return $count;
    }

    public function countEnabledPosts(): int
    {
        return $this->posts->map(function (Post $post) {
            return $post->canDisplay() && $post->getIsPublished();
        })->count();
    }

    public function countEnabledEvents(): int
    {
        return $this->events->map(function (Event $event) {
            return $event->getIsEnabled();
        })->count();
    }

    public function isOpened()
    {
        return $this->status === self::$statuses['opened'];
    }

    public function isClosed()
    {
        return $this->status === self::$statuses['closed'];
    }

    public function isFuture()
    {
        return $this->status === self::$statuses['future'];
    }
}
