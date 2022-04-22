<?php
namespace Capco\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\Category;
use Capco\AppBundle\Model\Sourceable;
use Capco\AppBundle\Traits\UuidTrait;
use Gedmo\Mapping\Annotation as Gedmo;
use Capco\AppBundle\Model\Contribution;
use Capco\AppBundle\Traits\TextableTrait;
use Capco\AppBundle\Entity\OpinionVersion;
use Capco\AppBundle\Traits\ExpirableTrait;
use Capco\AppBundle\Traits\VotableOkTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Capco\AppBundle\Model\IsPublishableInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Capco\AppBundle\Entity\Interfaces\VotableInterface;
use Capco\AppBundle\Entity\Interfaces\TrashableInterface;

/**
 * @ORM\Table(name="source")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\SourceRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Source implements Contribution, TrashableInterface, VotableInterface, IsPublishableInterface
{
    use UuidTrait;

    use VotableOkTrait;
    use ExpirableTrait;
    use TextableTrait;

    const TYPE_FOR = 1;
    const LINK = 0;
    const FILE = 1;

    public static $TypesLabels = [
        self::LINK => 'source.type.link',
        self::FILE => 'source.type.file',
    ];

    /**
     * @ORM\Column(name="title", type="string", length=100)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @ORM\Column(length=255)
     */
    private $slug;

    /**
     * @ORM\Column(name="link", type="text", length=255, nullable=true)
     * @Assert\NotBlank(groups={"link"})
     * @Assert\Url(groups={"link"})
     */
    private $link;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="change", field={"title", "link", "body", "author", "opinion", "category", "media", "type"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User", inversedBy="sources")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Opinion", inversedBy="sources", cascade={"persist"})
     * @ORM\JoinColumn(name="opinion_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    private $opinion;

    // ONE OF opinion or opinionVersion : should be in separate classes TODO
    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\OpinionVersion", inversedBy="sources", cascade={"persist"})
     * @ORM\JoinColumn(name="opinion_version_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $opinionVersion;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Category", inversedBy="sources", cascade={"persist"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    private $category;

    /**
     * @ORM\OneToOne(targetEntity="Capco\MediaBundle\Entity\Media", fetch="LAZY", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true)
     * @Assert\NotBlank(groups={"file"})
     * @Assert\Valid()
     */
    private $media;

    /**
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Reporting", mappedBy="Source", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $reports;

    /**
     * @ORM\Column(name="is_trashed", type="boolean")
     */
    private $isTrashed = false;

    /**
     * @Gedmo\Timestampable(on="change", field={"isTrashed"})
     * @ORM\Column(name="trashed_at", type="datetime", nullable=true)
     */
    private $trashedAt = null;

    /**
     * @ORM\Column(name="trashed_reason", type="text", nullable=true)
     */
    private $trashedReason = null;

    public function __construct()
    {
        $this->type = self::LINK;
        $this->reports = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->updatedAt = new \DateTime();
    }

    public function __toString()
    {
        if ($this->id) {
            return $this->getTitle();
        }

        return 'New source';
    }

    public function getKind(): string
    {
        return 'source';
    }

    public function getRelated()
    {
        return $this->getParent();
    }

    public function getStep()
    {
        return $this->getRelated()->getStep();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getIsEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): self
    {
        $this->isEnabled = $isEnabled;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getOpinion(): ?Opinion
    {
        return $this->opinion;
    }

    public function setOpinion(?Opinion $opinion): self
    {
        $this->opinion = $opinion;
        $this->opinion->addSource($this);

        return $this;
    }

    public function getOpinionVersion(): ?OpinionVersion
    {
        return $this->opinionVersion;
    }

    public function setOpinionVersion(?OpinionVersion $opinionVersion): self
    {
        $this->opinionVersion = $opinionVersion;
        $this->opinionVersion->addSource($this);

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;
        $this->category->addSource($this);

        return $this;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getReports()
    {
        return $this->reports;
    }

    public function addReport(Reporting $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
        }

        return $this;
    }

    public function removeReport(Reporting $report): self
    {
        $this->reports->removeElement($report);

        return $this;
    }

    public function getIsTrashed(): bool
    {
        return $this->isTrashed;
    }

    public function isTrashed(): bool
    {
        return $this->isTrashed;
    }

    public function setIsTrashed(bool $isTrashed): self
    {
        if ($isTrashed !== $this->isTrashed) {
            if (false === $this->isTrashed) {
                $this->trashedReason = null;
                $this->trashedAt = null;
            }
        }
        $this->isTrashed = $isTrashed;

        return $this;
    }

    public function getTrashedAt(): ?\DateTime
    {
        return $this->trashedAt;
    }

    public function setTrashedAt(?\DateTime $trashedAt): self
    {
        $this->trashedAt = $trashedAt;

        return $this;
    }

    public function getTrashedReason(): ?string
    {
        return $this->trashedReason;
    }

    public function setTrashedReason(?string $trashedReason): self
    {
        $this->trashedReason = $trashedReason;

        return $this;
    }

    // *************************** custom methods *******************************

    public function getLinkedOpinion(): Opinion
    {
        if ($this->opinion) {
            return $this->opinion;
        }

        return $this->opinionVersion->getParent();
    }

    public function getParent(): Sourceable
    {
        if ($this->opinionVersion) {
            return $this->opinionVersion;
        }

        return $this->opinion;
    }

    public function userHasReport(User $user = null): bool
    {
        if (null !== $user) {
            foreach ($this->reports as $report) {
                if ($report->getReporter() === $user) {
                    return true;
                }
            }
        }

        return false;
    }

    public function canDisplay(): bool
    {
        return $this->isEnabled && $this->getParent()->canDisplay();
    }

    public function canContribute(): bool
    {
        return $this->isEnabled && !$this->isTrashed && $this->getParent()->canContribute();
    }

    public function isPublished(): bool
    {
        return $this->isEnabled && !$this->isTrashed && $this->getParent()->isPublished();
    }

    // ******************** Lifecycle ************************************

    /**
     * @ORM\PreRemove
     */
    public function deleteSource()
    {
        if (null !== $this->category) {
            $this->category->removeSource($this);
        }
        if (null !== $this->opinion) {
            $this->opinion->removeSource($this);
        }
        if (null !== $this->opinionVersion) {
            $this->opinionVersion->removeSource($this);
        }
    }

    public function isIndexable(): bool
    {
        return $this->getIsEnabled() && !$this->isExpired();
    }

    public static function getElasticsearchTypeName(): string
    {
        return 'source';
    }

    public static function getElasticsearchSerializationGroups(): array
    {
        return ['Elasticsearch'];
    }
}
