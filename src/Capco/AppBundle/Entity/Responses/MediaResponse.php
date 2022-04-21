<?php

namespace Capco\AppBundle\Entity\Responses;

use Capco\MediaBundle\Entity\Media;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class MediaResponse.
 *
 * @ORM\Entity()
 */
class MediaResponse extends AbstractResponse
{
    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="Capco\MediaBundle\Entity\Media", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="responses_medias",
     *      joinColumns={@ORM\JoinColumn(name="response_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="media_id", referencedColumnName="id", unique=false)}
     * )
     */
    protected $medias;

    public function __construct()
    {
        parent::__construct();
        $this->medias = new ArrayCollection();
    }

    public function getMedias() : Collection
    {
        return $this->medias;
    }

    public function addMedia(Media $media) : self
    {
        if (!$this->medias->contains($media)) {
            $this->medias->add($media);
        }

        return $this;
    }

    public function setMedias(ArrayCollection $medias)
    {
        $this->medias = $medias;
    }

    public function getType() : string
    {
        return 'media';
    }
}
