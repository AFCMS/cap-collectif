<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Traits\IdTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Page.
 *
 * @ORM\Table(name="page")
 * @ORM\Entity
 */
class Page
{
    use IdTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @Gedmo\Slug(fields={"title"}, updatable=false)
     * @ORM\Column(length=255)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="change", field={"title", "body", "Author", "media"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_enabled", type="boolean")
     */
    private $isEnabled = true;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\MenuItem", mappedBy="Page", cascade={"persist", "remove"})
     */
    private $MenuItems;

    /**
     * @var
     *
     * @ORM\OneToOne(targetEntity="Capco\MediaBundle\Entity\Media", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $media;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->MenuItems = new ArrayCollection();
        $this->updatedAt = new \Datetime();
    }

    public function __toString()
    {
        return $this->getId() ? $this->getTitle() : 'New page';
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return Page
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
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
     * Set slug.
     *
     * @param string $slug
     *
     * @return Page
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set body.
     *
     * @param string $body
     *
     * @return Page
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set isEnabled.
     *
     * @param bool $isEnabled
     *
     * @return Page
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = $isEnabled;

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
     * @return mixed
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param mixed $media
     */
    public function setMedia($media)
    {
        $this->media = $media;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Page
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return Page
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return MenuItem
     */
    public function getMenuItems()
    {
        return $this->MenuItems;
    }

    /**
     * @param $menuItems
     */
    public function setMenuItems($menuItems)
    {
        $this->MenuItems = $menuItems;
    }

    public function addMenuItem($menuItem)
    {
        if (!$this->MenuItems->contains($menuItem)) {
            $this->MenuItems->add($menuItem);
        }
    }

    public function removeMenuItem($menuItem)
    {
        $this->MenuItems->removeElement($menuItem);
    }

    public function getExcerpt($nb = 100)
    {
        $excerpt = substr($this->body, 0, $nb);
        $excerpt = $excerpt . '...';

        return $excerpt;
    }
}
