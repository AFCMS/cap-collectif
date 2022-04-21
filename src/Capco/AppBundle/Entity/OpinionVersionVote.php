<?php

namespace Capco\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\OpinionVersionVoteRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class OpinionVersionVote extends AbstractVote
{
    const VOTE_OK = 1;
    const VOTE_NOK = -1;
    const VOTE_MITIGE = 0;

    public static $voteTypes = [
        'ok' => self::VOTE_OK,
        'mitige' => self::VOTE_MITIGE,
        'nok' => self::VOTE_NOK,
    ];

    public static $voteTypesLabels = [
        self::VOTE_OK => 'opinion.show.vote.ok',
        self::VOTE_MITIGE => 'opinion.show.vote.mitige',
        self::VOTE_NOK => 'opinion.show.vote.nok',
    ];

    public static $voteTypesStyles = [
        self::VOTE_OK => [
            'color' => 'success',
            'icon' => 'hand-like-2-1',
            'icon_checked' => 'hand-like-2',
        ],
        self::VOTE_NOK => [
            'color' => 'danger',
            'icon' => 'hand-unlike-2-1',
            'icon_checked' => 'hand-unlike-2',
        ],
        self::VOTE_MITIGE => [
            'color' => 'warning',
            'icon' => 'hand-like-2-1 icon-rotate',
            'icon_checked' => 'hand-like-2 icon-rotate',
        ],
    ];

    /**
     * @Gedmo\Timestampable(on="update", field={"value"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(name="value", type="integer")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\OpinionVersion", inversedBy="votes", cascade={"persist"})
     * @ORM\JoinColumn(name="opinion_version_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $opinionVersion;

    public function __construct()
    {
        $this->updatedAt = new \Datetime();
    }

    public function getRelated()
    {
        return $this->opinionVersion;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getOpinionVersion()
    {
        return $this->opinionVersion;
    }

    public function setOpinionVersion(OpinionVersion $version = null): self
    {
        $this->opinionVersion = $version;

        return $this;
    }

    public function getRelatedEntity()
    {
        return $this->opinionVersion;
    }

    // ******************* Lifecycle ******************************

    /**
     * @ORM\PreRemove
     */
    public function deleteVote()
    {
        if ($this->opinionVersion !== null) {
            $this->opinionVersion->removeVote($this);
        }
    }
}
