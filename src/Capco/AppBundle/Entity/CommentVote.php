<?php

namespace Capco\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\CommentVoteRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class CommentVote extends AbstractVote
{
    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Comment", inversedBy="votes", cascade={"persist"})
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $comment;

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    public function setComment(Comment $comment): self
    {
        if (null !== $comment) {
            $this->comment = $comment;
            $comment->addVote($this);
        }

        return $this;
    }

    public function getRelated()
    {
        return $this->comment;
    }

    public function getKind(): string
    {
        return 'commentVote';
    }

    // *************************** Lifecycle **********************************

    /**
     * @ORM\PreRemove
     */
    public function deleteVote()
    {
        if (null !== $this->comment) {
            $this->comment->removeVote($this);
        }
    }
}
