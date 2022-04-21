<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Model\HasAuthorInterface;
use Capco\AppBundle\Model\VoteContribution;
use Capco\AppBundle\Traits\ExpirableTrait;
use Capco\AppBundle\Traits\IdTrait;
use Capco\AppBundle\Traits\TimestampableTrait;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @ORM\Table(
 *   name="votes",
 *   indexes={},
 *   uniqueConstraints={
 *        @UniqueConstraint(
 *            name="opinion_vote_unique",
 *            columns={"voter_id", "opinion_id"}
 *        ),
 *        @UniqueConstraint(
 *            name="argument_vote_unique",
 *            columns={"voter_id", "argument_id"}
 *        ),
 *        @UniqueConstraint(
 *            name="opinion_version_vote_unique",
 *            columns={"voter_id", "opinion_version_id"}
 *        ),
 *        @UniqueConstraint(
 *            name="selection_step_vote_unique",
 *            columns={"voter_id", "proposal_id", "selection_step_id"}
 *        ),
 *        @UniqueConstraint(
 *            name="collect_step_vote_unique",
 *            columns={"voter_id", "proposal_id", "collect_step_id"}
 *        ),
 *        @UniqueConstraint(
 *            name="source_vote_unique",
 *            columns={"voter_id", "source_id"}
 *        ),
 *        @UniqueConstraint(
 *            name="comment_vote_unique",
 *            columns={"voter_id", "comment_id"}
 *        ),
 *        @UniqueConstraint(
 *            name="idea_vote_unique",
 *            columns={"voter_id", "idea_id"}
 *        )
 *    }
 * )
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\AbstractVoteRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name = "voteType", type = "string")
 * @ORM\DiscriminatorMap({
 *      "idea"              = "IdeaVote",
 *      "comment"           = "CommentVote",
 *      "opinion"           = "OpinionVote",
 *      "opinionVersion"    = "OpinionVersionVote",
 *      "argument"          = "ArgumentVote",
 *      "source"            = "SourceVote",
 *      "proposalSelection" = "ProposalSelectionVote",
 *      "proposalCollect"   = "ProposalCollectVote",
 * })
 */
abstract class AbstractVote implements VoteContribution, HasAuthorInterface
{
    use ExpirableTrait, TimestampableTrait, IdTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User", inversedBy="votes")
     * @ORM\JoinColumn(name="voter_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getKind(): string
    {
        return 'vote';
    }

    public function getRelated()
    {
        return null;
    }

    public function isIndexable(): bool
    {
        return !$this->isExpired();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->user;
    }

    public function hasUser(): bool
    {
        return (bool) $this->getUser();
    }

    abstract public function getRelatedEntity();
}
