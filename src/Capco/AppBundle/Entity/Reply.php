<?php

namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Entity\Responses\AbstractResponse;
use Capco\AppBundle\Traits\EnableTrait;
use Capco\AppBundle\Traits\TimestampableTrait;
use Capco\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Capco\AppBundle\Validator\Constraints as CapcoAssert;
use Capco\AppBundle\Traits\PrivatableTrait;
use Capco\AppBundle\Model\Contribution;
use Capco\AppBundle\Traits\ExpirableTrait;
use Capco\AppBundle\Traits\IdTrait;

/**
 * Reply.
 *
 * @ORM\Table(name="reply")
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\ReplyRepository")
 * @CapcoAssert\HasResponsesToRequiredQuestions(message="reply.missing_required_responses", formField="questionnaire")
 */
class Reply implements Contribution
{
    use IdTrait;
    use TimestampableTrait;
    use EnableTrait;
    use PrivatableTrait;
    use ExpirableTrait;

    /**
     * @var string
     *
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User", inversedBy="replies")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $author;

    /**
     * @var Questionnaire
     *
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Questionnaire", inversedBy="replies")
     * @ORM\JoinColumn(name="questionnaire_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $questionnaire;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Responses\AbstractResponse", mappedBy="reply", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $responses;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->updatedAt = new \Datetime();
        $this->responses = new ArrayCollection();
    }

    public function isIndexable()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     *
     * @return $this
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Questionnaire
     */
    public function getQuestionnaire()
    {
        return $this->questionnaire;
    }

    /**
     * @param Questionnaire $questionnaire
     *
     * @return $this
     */
    public function setQuestionnaire(Questionnaire $questionnaire)
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    public function addResponse(AbstractResponse $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses->add($response);
            $response->setReply($this);
        }

        return $this;
    }

    public function removeResponse(AbstractResponse $response): self
    {
        $this->responses->removeElement($response);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param ArrayCollection $responses
     *
     * @return $this
     */
    public function setResponses(ArrayCollection $responses)
    {
        $this->responses = $responses;
        foreach ($responses as $response) {
            $response->setReply($this);
        }

        return $this;
    }
}
