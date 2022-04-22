<?php
namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Traits\UuidTrait;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="user_following",
 *    uniqueConstraints={
 *      @UniqueConstraint(name="follower_unique_proposal",columns={"user_id", "proposal_id"}),
 *      @UniqueConstraint(name="follower_unique_opinion",columns={"user_id", "opinion_id"}),
 *    }
 * )
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\FollowerRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Follower
{
    use UuidTrait;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="followed_at",type="datetime", nullable=false)
     */
    protected $followedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User", inversedBy="followingContributions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Proposal", inversedBy="followers")
     * @ORM\JoinColumn(name="proposal_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $proposal;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Opinion", inversedBy="followers")
     * @ORM\JoinColumn(name="opinion_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $opinion;

    /**
     * @ORM\Column(name="notified_of", type="string", nullable=false)
     */
    protected $notifiedOf;

    public function getFollowedAt(): \DateTime
    {
        return $this->followedAt;
    }

    public function setFollowedAt(\DateTime $followedAt): self
    {
        $this->followedAt = $followedAt;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        $user->addFollowingContribution($this);

        return $this;
    }

    public function getProposal(): Proposal
    {
        return $this->proposal;
    }

    public function setProposal(Proposal $proposal): self
    {
        $this->proposal = $proposal;
        $proposal->addFollower($this);

        return $this;
    }

    /**
     * @ORM\PreRemove
     */
    public function deleteFollower()
    {
        $this->proposal->removeFollower($this);
    }

    public function getNotifiedOf(): ?string
    {
        return $this->notifiedOf;
    }

    public function setNotifiedOf(string $notifiedOf): self
    {
        $this->notifiedOf = $notifiedOf;

        return $this;
    }
}
