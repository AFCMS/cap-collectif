<?php

namespace Capco\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * SiteParameter.
 *
 * @ORM\Table(name="site_parameter")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\SiteParameterRepository")
 */
class SiteParameter
{
    const TYPE_SIMPLE_TEXT = 0;
    const TYPE_RICH_TEXT = 1;
    const TYPE_INTEGER = 2;
    const TYPE_JS = 3;
    const TYPE_EMAIL = 4;
    const TYPE_INTERN_URL = 5;
    const TYPE_URL = 6;
    const TYPE_TEL_NB = 7;
    const TYPE_BOOLEAN = 8;

    public static $types = [
        'simple_text' => self::TYPE_SIMPLE_TEXT,
        'rich_text' => self::TYPE_RICH_TEXT,
        'integer' => self::TYPE_INTEGER,
        'javascript' => self::TYPE_JS,
        'email' => self::TYPE_EMAIL,
        'intern_url' => self::TYPE_INTERN_URL,
        'url' => self::TYPE_URL,
        'tel' => self::TYPE_TEL_NB,
        'boolean' => self::TYPE_BOOLEAN,
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="keyname", type="string", length=255)
     */
    private $keyname;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text")
     */
    private $value;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field={"value"})
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
     * @var int
     * @ORM\Column(name="position", type="integer")
     */
    private $position = 0;

    /**
     * @var int
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    public function __toString()
    {
        if ($this->id) {
            return $this->getTitle();
        } else {
            return 'New parameter';
        }
    }

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->updatedAt = new \Datetime();
        $this->type = self::TYPE_SIMPLE_TEXT;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set keyname.
     *
     * @param string $keyname
     *
     * @return SiteParameter
     */
    public function setKeyname($keyname)
    {
        $this->keyname = $keyname;

        return $this;
    }

    /**
     * Get keyname.
     *
     * @return string
     */
    public function getKeyname()
    {
        return $this->keyname;
    }

    /**
     * Set title.
     *
     * @param string $title
     *
     * @return SiteParameter
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
     * Set value.
     *
     * @param string $value
     *
     * @return SiteParameter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return SiteParameter
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Set isEnabled.
     *
     * @param bool $isEnabled
     *
     * @return Consultation
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
