<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Traits\BlameableTrait;
use Capco\AppBundle\Traits\SluggableTitleTrait;
use Capco\AppBundle\Traits\TimestampableTrait;
use Capco\AppBundle\Traits\UuidTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="user_group")
 * @ORM\Entity()
 */
class Group
{
    use UuidTrait;
    use TimestampableTrait;
    use SluggableTitleTrait;
    use BlameableTrait;

    /**
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\UserGroup", mappedBy="group",  cascade={"persist", "remove"})
     */
    private $userGroups;

    public function __construct()
    {
        $this->userGroups = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function setUserGroups(ArrayCollection $userGroups): self
    {
        $this->userGroups = $userGroups;

        return $this;
    }

    public function addUserGroup(UserGroup $userGroup): self
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
        }

        return $this;
    }

    public function removeUserGroup(UserGroup $userGroup): self
    {
        $this->userGroups->removeElement($userGroup);

        return $this;
    }

    /**
     * Useful for sonata admin.
     */
    public function countUserGroups(): int
    {
        return $this->userGroups->count();
    }

    /**
     * Useful for sonata admin.
     */
    public function titleInfo(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
        ];
    }
}
