<?php
namespace Capco\AppBundle\Entity;

use Capco\AppBundle\Utils\Map;
use Doctrine\ORM\Mapping as ORM;
use Capco\UserBundle\Entity\User;
use Capco\MediaBundle\Entity\Media;
use Capco\AppBundle\Traits\UuidTrait;
use Capco\AppBundle\Model\Publishable;
use Gedmo\Mapping\Annotation as Gedmo;
use Capco\AppBundle\Model\Contribution;
use Capco\AppBundle\Traits\EnableTrait;
use Capco\AppBundle\Traits\DraftableTrait;
use Capco\AppBundle\Traits\ExpirableTrait;
use Capco\AppBundle\Traits\ReferenceTrait;
use Capco\AppBundle\Traits\TrashableTrait;
use Capco\AppBundle\Traits\SoftDeleteTrait;
use Doctrine\Common\Collections\Collection;
use Capco\AppBundle\Traits\CommentableTrait;
use Capco\AppBundle\Traits\HasResponsesTrait;
use Capco\AppBundle\Traits\SelfLinkableTrait;
use Capco\AppBundle\Traits\PublishableTrait;
use Capco\AppBundle\Traits\SummarizableTrait;
use Capco\AppBundle\Traits\TimestampableTrait;
use Capco\AppBundle\Model\CommentableInterface;
use Capco\AppBundle\Traits\SluggableTitleTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Capco\AppBundle\Traits\NullableTextableTrait;
use Capco\AppBundle\Entity\Interfaces\SoftDeleteable;
use Capco\AppBundle\Entity\Responses\AbstractResponse;
use Symfony\Component\Validator\Constraints as Assert;
use Capco\AppBundle\Validator\Constraints as CapcoAssert;
use Capco\AppBundle\Entity\Interfaces\SelfLinkableInterface;
use Capco\AppBundle\Entity\Steps\CollectStep as StepsCollectStep;
use Capco\AppBundle\Entity\Interfaces\Trashable;

/**
 * @ORM\Table(name="proposal", uniqueConstraints={
 *    @ORM\UniqueConstraint(columns={ "proposal_form_id", "reference"}),
 * })
 * @ORM\Entity(repositoryClass="Capco\AppBundle\Repository\ProposalRepository")
 * @ORM\HasLifecycleCallbacks()
 * @CapcoAssert\HasResponsesToRequiredQuestions(message="proposal.missing_required_responses", formField="proposalForm")
 * @CapcoAssert\HasDistrictIfMandatory()
 * @CapcoAssert\HasThemeIfMandatory()
 * @CapcoAssert\HasCategoryIfMandatory()
 * @CapcoAssert\HasOnlyOneSelectionPerStep()
 * @CapcoAssert\HasAddressIfMandatory()
 */
class Proposal
    implements
        Publishable,
        Contribution,
        Trashable,
        CommentableInterface,
        SelfLinkableInterface,
        SoftDeleteable
{
    use UuidTrait;
    use ReferenceTrait;
    use CommentableTrait;
    use TimestampableTrait;
    use EnableTrait;
    use TrashableTrait;
    use SluggableTitleTrait;
    use ExpirableTrait;
    use SelfLinkableTrait;
    use SoftDeleteTrait;
    use NullableTextableTrait;
    use SummarizableTrait;
    use DraftableTrait;
    use HasResponsesTrait;
    use PublishableTrait;

    const STATE_DRAFT = 'draft';
    const STATE_ENABLED = 'published';
    const STATE_TRASHED = 'trashed';
    const STATE_HIDDEN_CONTENT = 'hidden_content';
    const STATE_DISABlED = 'unpublished';
    const STATE_DELETED = 'deleted';
    const STATE_EXPIRED = 'expired';

    public static $ratings = [1, 2, 3, 4, 5];

    /**
     * @Gedmo\Timestampable(on="change", field={"title", "body"})
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User", inversedBy="proposals")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $author;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="update_author_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $updateAuthor;

    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\ProposalForm", inversedBy="proposals")
     * @ORM\JoinColumn(name="proposal_form_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $proposalForm;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Reporting", mappedBy="proposal", cascade={"persist"}, orphanRemoval=true)
     */
    protected $reports;

    /**
     * @ORM\ManyToMany(targetEntity="Capco\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinTable(name="user_favorite_proposal")
     */
    protected $likers;

    /**
     * @ORM\ManyToMany(targetEntity="Capco\AppBundle\Entity\Group", inversedBy="evaluating", cascade={"persist"})
     * @ORM\JoinTable(name="user_evaluatin_proposal")
     */
    protected $evaluers;

    /**
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(name="rating", type="integer", nullable=true)
     */
    private $rating;

    /**
     * @ORM\Column(name="annotation", type="text", nullable=true)
     */
    private $annotation;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Theme", inversedBy="proposals", cascade={"persist"})
     * @ORM\JoinColumn(name="theme_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $theme = null;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\District", inversedBy="proposals", cascade={"persist"})
     * @ORM\JoinColumn(name="district_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $district = null;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\Status", cascade={"persist"}, inversedBy="proposals")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $status = null;

    /**
     * @ORM\ManyToOne(targetEntity="Capco\AppBundle\Entity\ProposalCategory", cascade={"persist"}, inversedBy="proposals")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $category = null;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\ProposalComment", mappedBy="proposal", cascade={"persist"})
     */
    private $comments;

    /**
     * @ORM\OneToMany(
     *  targetEntity="Capco\AppBundle\Entity\Responses\AbstractResponse",
     *  mappedBy="proposal",
     *  cascade={"persist"}
     * )
     */
    private $responses;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Selection", mappedBy="proposal", cascade={"persist"}, orphanRemoval=true)
     */
    private $selections;

    /**
     * @ORM\Column(name="estimation", type="float", nullable=true)
     */
    private $estimation = null;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\ProgressStep", mappedBy="proposal", cascade={"persist","remove"},  orphanRemoval=true)
     */
    private $progressSteps;

    /**
     * @ORM\OneToOne(targetEntity="Capco\MediaBundle\Entity\Media", fetch="LAZY", cascade={"persist"})
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     * @Assert\Valid()
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\ProposalSelectionVote", mappedBy="proposal", cascade={"persist"})
     */
    private $selectionVotes;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\ProposalCollectVote", mappedBy="proposal", cascade={"persist"})
     */
    private $collectVotes;

    /** TODO: To remove for a real dynamic evaluation */

    /**
     * @ORM\Column(name="service_pilote", type="text", nullable=true)
     */
    private $servicePilote;

    /**
     * @ORM\Column(name="domaniality", type="text", nullable=true)
     */
    private $domaniality;

    /**
     * @ORM\Column(name="compatibility", type="text", nullable=true)
     */
    private $compatibility;

    /**
     * @ORM\Column(name="environmental_impact", type="text", nullable=true)
     */
    private $environmentalImpact;

    /**
     * @ORM\Column(name="dimension", type="text", nullable=true)
     */
    private $dimension;

    /**
     * @ORM\Column(name="functioning_impact", type="text", nullable=true)
     */
    private $functioningImpact;

    /**
     * @ORM\Column(name="evaluation", type="text", nullable=true)
     */
    private $evaluation;

    /**
     * @ORM\Column(name="delay", type="text", nullable=true)
     */
    private $delay;

    /**
     * @ORM\Column(name="proposed_answer", type="text", nullable=true)
     */
    private $proposedAnswer;

    /**
     * @ORM\OneToOne(targetEntity="Capco\AppBundle\Entity\ProposalEvaluation", mappedBy="proposal", cascade={"persist"})
     */
    private $proposalEvaluation;

    /**
     * @ORM\OneToMany(targetEntity="Capco\AppBundle\Entity\Follower", mappedBy="proposal", cascade={"persist"})
     */
    private $followers;

    public function __construct()
    {
        $this->selectionVotes = new ArrayCollection();
        $this->collectVotes = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->responses = new ArrayCollection();
        $this->commentsCount = 0;
        $this->updatedAt = new \Datetime();
        $this->selections = new ArrayCollection();
        $this->likers = new ArrayCollection();
        $this->evaluers = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->progressSteps = new ArrayCollection();
        $this->childConnections = new ArrayCollection();
        $this->parentConnections = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getId() ? $this->getTitle() : 'New proposal';
    }

    public function getKind(): string
    {
        return 'proposal';
    }

    public function getRelated()
    {
        return null;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRating(int $rating = null)
    {
        $this->rating = $rating;

        return $this;
    }

    public function getAnnotation()
    {
        return $this->annotation;
    }

    public function setAnnotation(string $annotation = null): self
    {
        $this->annotation = $annotation;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(Status $status = null): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(ProposalCategory $category = null): self
    {
        $this->category = $category;

        return $this;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setTheme(Theme $theme = null): self
    {
        $this->theme = $theme;
        if ($theme) {
            $theme->addProposal($this);
        }

        return $this;
    }

    public function getDistrict()
    {
        return $this->district;
    }

    public function setDistrict(District $district = null, bool $add = true): self
    {
        $this->district = $district;
        if ($district && $add) {
            $district->addProposal($this);
        }

        return $this;
    }

    /**
     * @return null|User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getUpdateAuthor()
    {
        return $this->updateAuthor;
    }

    public function setUpdateAuthor(User $updateAuthor = null): self
    {
        $this->updateAuthor = $updateAuthor;

        return $this;
    }

    public function getForm(): ProposalForm
    {
        return $this->proposalForm;
    }

    public function getProposalForm(): ProposalForm
    {
        return $this->proposalForm;
    }

    public function setProposalForm(ProposalForm $proposalForm): self
    {
        $this->proposalForm = $proposalForm;

        return $this;
    }

    public function getStep(): ?StepsCollectStep
    {
        return $this->proposalForm ? $this->proposalForm->getStep() : null;
    }

    public function getResponsesQuestions(): Collection
    {
        return $this->proposalForm->getRealQuestions();
    }

    public function setResponseOn(AbstractResponse $response): self
    {
        $response->setProposal($this);

        return $this;
    }

    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Reporting $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
        }

        return $this;
    }

    public function removeReport(Reporting $report): self
    {
        $this->reports->removeElement($report);

        return $this;
    }

    public function addSelection(Selection $selection): self
    {
        if (!$this->selections->contains($selection)) {
            $this->selections[] = $selection;
            $selection->setProposal($this);
        }

        return $this;
    }

    public function removeSelection(Selection $selection): self
    {
        $this->selections->removeElement($selection);

        return $this;
    }

    public function getSelections(): Collection
    {
        return $this->selections;
    }

    public function getClassName(): string
    {
        return 'Proposal';
    }

    public function canDisplay($user = null): bool
    {
        return $this->enabled && !$this->isTrashed && $this->getStep()->canDisplay($user);
    }

    public function isPrivate(): bool
    {
        return $this->getProposalForm() && $this->getProposalForm()->getStep()
            ? $this->getProposalForm()
                ->getStep()
                ->isPrivate()
            : false;
    }

    public function isSelected(): bool
    {
        return !$this->getSelections()->isEmpty();
    }

    public function isVisible(): bool
    {
        return !$this->isPrivate() || $this->isSelected();
    }

    public function canContribute(): bool
    {
        return (
            ($this->enabled || $this->isDraft()) &&
            !$this->isTrashed() &&
            $this->getStep() &&
            $this->getStep()->canContribute()
        );
    }

    public function canComment(): bool
    {
        return (
            $this->enabled &&
            !$this->isTrashed() &&
            $this->proposalForm &&
            $this->proposalForm->isCommentable() &&
            $this->getIsCommentable()
        );
    }

    public function userHasReport(User $user): bool
    {
        foreach ($this->reports as $report) {
            if ($report->getReporter() === $user) {
                return true;
            }
        }

        return false;
    }

    public function getEstimation()
    {
        return $this->estimation;
    }

    public function setEstimation(float $estimation = null): self
    {
        $this->estimation = $estimation;

        return $this;
    }

    public function getLikers(): Collection
    {
        return $this->likers;
    }

    public function addLiker(User $liker): self
    {
        if (!$this->likers->contains($liker)) {
            $this->likers[] = $liker;
        }

        return $this;
    }

    public function removeLiker(User $liker): self
    {
        $this->likers->removeElement($liker);

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->enabled && !$this->isTrashed();
    }

    public function getSelectionSteps(): array
    {
        $steps = [];
        foreach ($this->selections as $selection) {
            $steps[] = $selection->getSelectionStep();
        }

        return $steps;
    }

    public function getProject()
    {
        if (
            $this->getProposalForm() &&
            $this->getProposalForm()->getStep() &&
            $this->getProposalForm()
                ->getStep()
                ->getProject()
        ) {
            return $this->getProposalForm()
                ->getStep()
                ->getProject();
        }
    }

    public function getProjectId()
    {
        if (
            $this->getProposalForm() &&
            $this->getProposalForm()->getStep() &&
            $this->getProposalForm()
                ->getStep()
                ->getProject()
        ) {
            return $this->getProposalForm()
                ->getStep()
                ->getProjectId();
        }
    }

    public function getSelectionStepsIds(): array
    {
        $ids = array_filter(
            array_map(function ($value) {
                return $value->getSelectionStep() ? $value->getSelectionStep()->getId() : null;
            }, $this->getSelections()->getValues()),
            function ($value) {
                return null !== $value;
            }
        );

        return $ids;
    }

    public function getProgressSteps(): Collection
    {
        return $this->progressSteps;
    }

    public function setProgressSteps(Collection $progressSteps): self
    {
        $this->progressSteps = $progressSteps;

        return $this;
    }

    public function getSelectionVotes(): Collection
    {
        return $this->selectionVotes;
    }

    public function setSelectionVotes(Collection $votes)
    {
        $this->selectionVotes = $votes;

        return $this;
    }

    public function getCollectVotes(): Collection
    {
        return $this->collectVotes;
    }

    public function setCollectVotes(Collection $collectVotes): self
    {
        $this->collectVotes = $collectVotes;

        return $this;
    }

    public function addSelectionVote(ProposalSelectionVote $selectionVote): self
    {
        if (!$this->selectionVotes->contains($selectionVote)) {
            $this->selectionVotes->add($selectionVote);
        }

        return $this;
    }

    public function removeSelectionVote(ProposalSelectionVote $vote): self
    {
        if ($this->selectionVotes->contains($vote)) {
            $this->selectionVotes->removeElement($vote);
        }

        return $this;
    }

    public function addCollectVote(ProposalCollectVote $vote): self
    {
        if (!$this->collectVotes->contains($vote)) {
            $this->collectVotes->add($vote);
        }

        return $this;
    }

    public function addProgressStep(ProgressStep $progressStep): self
    {
        if (!$this->progressSteps->contains($progressStep)) {
            $this->progressSteps->add($progressStep);
            $progressStep->setProposal($this);
        }

        return $this;
    }

    public function removeCollectVote(ProposalCollectVote $vote): self
    {
        if ($this->collectVotes->contains($vote)) {
            $this->collectVotes->removeElement($vote);
        }

        return $this;
    }

    public function removeProgressStep(ProgressStep $progressStep): self
    {
        if ($this->progressSteps->contains($progressStep)) {
            $this->progressSteps->removeElement($progressStep);
        }

        return $this;
    }

    public function canHaveProgessSteps(): bool
    {
        if (!$this->getProposalForm()) {
            // for sonata
            return false;
        }

        return $this->getProposalForm()
            ->getStep()
            ->getProject()
            ->getSteps()
            ->exists(function ($key, $step) {
                return (
                    $step->getStep()->isSelectionStep() &&
                    $step->getStep()->isAllowingProgressSteps()
                );
            });
    }

    /**
     * @return Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param mixed $media
     */
    public function setMedia(Media $media = null)
    {
        $this->media = $media;
    }

    /** TODO: to remove for a real evaluation. */

    /**
     * @return null|string
     */
    public function getServicePilote()
    {
        return $this->servicePilote;
    }

    public function setServicePilote(string $servicePilote = null): self
    {
        $this->servicePilote = $servicePilote;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDomaniality()
    {
        return $this->domaniality;
    }

    public function setDomaniality(string $domaniality): self
    {
        $this->domaniality = $domaniality;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCompatibility()
    {
        return $this->compatibility;
    }

    public function setCompatibility(string $compatibility = null): self
    {
        $this->compatibility = $compatibility;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEnvironmentalImpact()
    {
        return $this->environmentalImpact;
    }

    public function setEnvironmentalImpact(string $environmentalImpact = null): self
    {
        $this->environmentalImpact = $environmentalImpact;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    public function setDimension(string $dimension = null): self
    {
        $this->dimension = $dimension;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFunctioningImpact()
    {
        return $this->functioningImpact;
    }

    public function setFunctioningImpact(string $functioningImpact = null): self
    {
        $this->functioningImpact = $functioningImpact;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEvaluation()
    {
        return $this->evaluation;
    }

    public function setEvaluation(string $evaluation = null): self
    {
        $this->evaluation = $evaluation;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDelay()
    {
        return $this->delay;
    }

    public function setDelay(string $delay = null)
    {
        $this->delay = $delay;
    }

    /**
     * @return null|string
     */
    public function getProposedAnswer()
    {
        return $this->proposedAnswer;
    }

    public function setProposedAnswer(string $proposedAnswer = null): self
    {
        $this->proposedAnswer = $proposedAnswer;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress($address = null)
    {
        $this->address = $address;

        return $this;
    }

    public function getFiledAddress(): string
    {
        if (!$this->getAddress()) {
            return '';
        }

        return Map::decodeAddressFromJson($this->getAddress());
    }

    public function getFullReference(): string
    {
        return $this->getProposalForm()->getReference() . '-' . $this->getReference();
    }

    /**
     * Useful for sonata admin.
     */
    public function updatedInfo(): array
    {
        return ['date' => $this->getUpdatedAt(), 'user' => $this->getUpdateAuthor()];
    }

    /**
     * Useful for sonata admin.
     */
    public function titleInfo(): array
    {
        return [
            'id' => $this->getId(),
            'title' => $this->getTitle(),
            'summary' => $this->getSummary(),
        ];
    }

    /**
     * Useful for sonata admin.
     */
    public function lastStatus()
    {
        /** @var Selection[] $projectSteps */
        $selections = $this->getSelections()->toArray();

        usort($selections, function ($step, $nextStep) {
            return $nextStep->getStep()->getPosition() <=> $step->getStep()->getPosition();
        });

        $findStatus = null;
        $loop = 0;

        while (null === $findStatus && $loop < \count($selections)) {
            $selection = $selections[$loop];

            if (null !== $selection->getStatus()) {
                $findStatus = $selection->getStatus();
            }

            ++$loop;
        }

        if (null !== $findStatus) {
            return $findStatus;
        }

        return $this->getStatus();
    }

    public function state(): string
    {
        if (null !== $this->getDeletedAt()) {
            return self::STATE_DELETED;
        }

        if ($this->isTrashed()) {
            if ($this->getTrashedStatus() === Trashable::STATUS_VISIBLE) {
                return self::STATE_TRASHED;
            }
            return self::STATE_HIDDEN_CONTENT;
        }

        if (!$this->isEnabled() && !$this->isDraft()) {
            return self::STATE_DISABlED;
        }

        if ($this->isExpired()) {
            return self::STATE_EXPIRED;
        }

        if ($this->isDraft()) {
            return self::STATE_DRAFT;
        }

        if ($this->isEnabled()) {
            return self::STATE_ENABLED;
        }

        throw new \Exception(
            sprintf('no current state was found for this proposition %s', $this->getId())
        );
    }

    public function getProposalEvaluation()
    {
        return $this->proposalEvaluation;
    }

    public function setProposalEvaluation(ProposalEvaluation $proposalEvaluation): self
    {
        $this->proposalEvaluation = $proposalEvaluation;

        return $this;
    }

    public function getEvaluers(): Collection
    {
        return $this->evaluers;
    }

    public function setEvaluers(Collection $evaluers): self
    {
        $this->evaluers = $evaluers;

        return $this;
    }

    public function addEvaluer(Group $group): self
    {
        if (!$this->evaluers->contains($group)) {
            $this->evaluers->add($group);
        }

        return $this;
    }

    public function removeEvaluer(Group $group): self
    {
        $this->evaluers->removeElement($group);

        return $this;
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function addFollower(Follower $follower): self
    {
        if (!$this->followers->contains($follower)) {
            $this->followers[] = $follower;
        }

        return $this;
    }

    public function removeFollower(Follower $follower): self
    {
        $this->followers->removeElement($follower);

        return $this;
    }

    public function setFollowers(Collection $followers): self
    {
        $this->followers = $followers;

        return $this;
    }

    public function isIndexable(): bool
    {
        return (
            $this->enabled &&
            !$this->expired &&
            !$this->isDraft() &&
            !$this->isDeleted() &&
            $this->getProject()->isIndexable()
        );
    }

    public static function getElasticsearchTypeName(): string
    {
        return 'proposal';
    }

    public static function getElasticsearchSerializationGroups(): array
    {
        return ['Elasticsearch'];
    }

    public function hasFollowers(): bool
    {
        return \count($this->followers) > 0;
    }

    public function countFollowers(): int
    {
        return \count($this->followers);
    }

    public function getVisibility(): int
    {
        return $this->getProject()->getVisibility();
    }
}
