<?php
namespace Capco\AppBundle\Entity;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\ProposalCommentRepository")
 */
class ProposalComment extends Comment
{
    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Proposal", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="proposal_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     */
    private $proposal;

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(Proposal $proposal, bool $add = true): self
    {
        $this->proposal = $proposal;
        if ($add) {
            $proposal->addComment($this);
        }

        return $this;
    }

    // ************************ Overriden methods *********************************

    public function isIndexable(): bool
    {
        try {
            return (
                $this->isPublished() &&
                !$this->getRelatedObject()->isDeleted() &&
                $this->getProposal()
                    ->getProject()
                    ->isIndexable()
            );
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    public function getKind(): string
    {
        return 'proposalComment';
    }

    public function getRelatedObject(): ?Proposal
    {
        return $this->proposal;
    }

    public function setRelatedObject($object)
    {
        return $this->setProposal($object);
    }
}
