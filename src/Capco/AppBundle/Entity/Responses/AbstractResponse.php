<?php

namespace Capco\AppBundle\Entity\Responses;

use Capco\AppBundle\Elasticsearch\IndexableInterface;
use Capco\AppBundle\Entity\AbstractReply;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalAnalysis;
use Capco\AppBundle\Entity\ProposalEvaluation;
use Capco\AppBundle\Entity\Questions\AbstractQuestion;
use Capco\AppBundle\Entity\Reply;
use Capco\AppBundle\Entity\ReplyAnonymous;
use Capco\AppBundle\Traits\TimestampableTrait;
use Capco\AppBundle\Traits\UuidTrait;
use Capco\AppBundle\Validator\Constraints as CapcoAssert;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *   name="response",
 *   uniqueConstraints={
 *        @ORM\UniqueConstraint(
 *            name="proposal_response_unique",
 *            columns={"proposal_id", "question_id"}
 *        ),
 *        @ORM\UniqueConstraint(
 *            name="evaluation_response_unique",
 *            columns={"evaluation_id", "question_id"}
 *        )
 *    }
 * )
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\AbstractResponseRepository")
 * @ORM\HasLifecycleCallbacks()
 * @CapcoAssert\HasRequiredNumberOfChoices()
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name = "response_type", type = "string")
 * @ORM\DiscriminatorMap({
 *      "value"  = "ValueResponse",
 *      "media"  = "MediaResponse",
 * })
 */
abstract class AbstractResponse implements IndexableInterface
{
    use TimestampableTrait;
    use UuidTrait;

    const TYPE_FIELD_NAME = '_type';

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Proposal", inversedBy="responses", cascade={"persist"})
     * @ORM\JoinColumn(name="proposal_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $proposal;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User", inversedBy="responses", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Reply", inversedBy="responses", cascade={"persist"})
     * @ORM\JoinColumn(name="reply_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $reply;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\ReplyAnonymous", inversedBy="responses", cascade={"persist"})
     * @ORM\JoinColumn(name="reply_anonymous_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $replyAnonymous;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\ProposalEvaluation", inversedBy="responses", cascade={"persist"})
     * @ORM\JoinColumn(name="evaluation_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $proposalEvaluation;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\ProposalAnalysis", inversedBy="responses", cascade={"persist"})
     * @ORM\JoinColumn(name="analysis_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    private $analysis;

    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(
     *   targetEntity="Capco\AppBundle\Entity\Questions\AbstractQuestion",
     *   inversedBy="responses"
     * )
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    private $question;

    abstract public function getType();

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal = null): self
    {
        $this->proposal = $proposal;

        return $this;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * I don't know why it's nullable, we should investigate this.
     */
    public function getQuestion(): ?AbstractQuestion
    {
        return $this->question;
    }

    public function getPosition()
    {
        return $this->question->getPosition();
    }

    public function setQuestion(AbstractQuestion $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getReplyOrReplyAnonymous(): ?AbstractReply
    {
        if ($this->replyAnonymous) {
            return $this->replyAnonymous;
        }

        return $this->reply;
    }

    public function getReply(): ?Reply
    {
        return $this->reply;
    }

    public function setReply(?Reply $reply = null): self
    {
        $this->reply = $reply;

        return $this;
    }

    public function getReplyAnonymous(): ?ReplyAnonymous
    {
        return $this->replyAnonymous;
    }

    public function setReplyAnonymous(?ReplyAnonymous $replyAnonymous = null): self
    {
        $this->replyAnonymous = $replyAnonymous;

        return $this;
    }

    public function getProposalEvaluation()
    {
        return $this->proposalEvaluation;
    }

    public function setProposalEvaluation(?ProposalEvaluation $proposalEvaluation = null): self
    {
        $this->proposalEvaluation = $proposalEvaluation;

        return $this;
    }

    public function getAnalysis(): ?ProposalAnalysis
    {
        return $this->analysis;
    }

    public function setAnalysis(?ProposalAnalysis $analysis = null): self
    {
        $this->analysis = $analysis;

        return $this;
    }

    /**
     * @ORM\PostUpdate()
     */
    public function updateProposalTimestamp()
    {
        if ($this->getUpdatedAt() && $this->getProposal()) {
            $this->getProposal()->setUpdatedAt(new \DateTime());
        }
    }

    public static function getElasticsearchPriority(): int
    {
        return 16;
    }

    public function isIndexable(): bool
    {
        if ($this->reply) {
            return !$this->reply->isDraft();
        }

        return true;
    }

    public static function getElasticsearchTypeName(): string
    {
        return 'response';
    }

    public static function getElasticsearchSerializationGroups(): array
    {
        return [
            'ElasticsearchResponse',
            'ElasticsearchResponseNestedReply',
            'ElasticsearchResponseNestedQuestion',
        ];
    }
}
