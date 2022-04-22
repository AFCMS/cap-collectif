<?php

namespace Capco\UserBundle\Entity;

use Capco\AppBundle\Elasticsearch\IndexableInterface;
use Capco\AppBundle\Entity\Follower;
use Capco\AppBundle\Entity\Responses\AbstractResponse;
use Capco\AppBundle\Entity\Synthesis\SynthesisUserInterface;
use Capco\AppBundle\Entity\UserArchive;
use Capco\AppBundle\Entity\UserGroup;
use Capco\AppBundle\Entity\UserNotificationsConfiguration;
use Capco\MediaBundle\Entity\Media;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Util\Canonicalizer;
use Sonata\UserBundle\Entity\BaseUser;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as RealUserInterface;

class User extends BaseUser implements EncoderAwareInterface, SynthesisUserInterface, EquatableInterface, IndexableInterface
{
    const SORT_ORDER_CREATED_AT = 0;
    const SORT_ORDER_CONTRIBUTIONS_COUNT = 1;
    const GENDER_OTHER = 'o';

    public static $sortOrder = [
        'activity' => self::SORT_ORDER_CONTRIBUTIONS_COUNT,
        'date' => self::SORT_ORDER_CREATED_AT,
    ];
    public static $sortOrderLabels = [
        'activity' => 'user.index.sort.activity',
        'date' => 'user.index.sort.date',
    ];

    // Hack for ParticipantEdge
    public $registeredEvent;

    protected $samlId;
    protected $parisId;
    protected $id;

    protected $gender = null;

    /**
     * @var int
     */
    protected $facebook_id;

    /**
     * @var int
     */
    protected $facebook_access_token;

    /**
     * @var int
     */
    protected $facebookUrl;

    /**
     * @var int
     */
    protected $google_id;

    /**
     * @var int
     */
    protected $google_access_token;

    protected $linkedInUrl;

    /**
     * @var string
     */
    protected $twitter_id;

    /**
     * @var string
     */
    protected $twitter_access_token;

    /**
     * @var string
     */
    protected $twitterUrl;

    /**
     * @var
     */
    protected $media;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $address2;

    /**
     * @var int
     */
    protected $zipCode;

    /**
     * @var string
     */
    protected $neighborhood;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string
     */
    protected $encoder;

    protected $opinions;

    protected $opinionVersions;

    protected $comments;

    protected $arguments;

    protected $votes;

    protected $sources;

    protected $proposals;

    protected $replies;

    /**
     * @var int
     */
    protected $projectsCount = 0;

    /**
     * @var int
     */
    protected $sourcesCount = 0;

    /**
     * @var int
     */
    protected $argumentsCount = 0;

    /**
     * @var int
     */
    protected $proposalsCount = 0;

    /**
     * @var int
     */
    protected $repliesCount = 0;

    /**
     * @var int
     */
    protected $opinionsCount = 0;

    /**
     * @var int
     */
    protected $opinionVersionsCount = 0;

    // Comments

    /**
     * @var int
     */
    protected $postCommentsCount = 0;

    /**
     * @var int
     */
    protected $eventCommentsCount = 0;

    /**
     * @var int
     */
    protected $proposalCommentsCount = 0;

    // Votes

    /**
     * @var int
     */
    protected $argumentVotesCount = 0;

    /**
     * @var int
     */
    protected $commentVotesCount = 0;

    /**
     * @var int
     */
    protected $proposalVotesCount = 0;

    /**
     * @var int
     */
    protected $opinionVotesCount = 0;

    /**
     * @var int
     */
    protected $opinionVersionVotesCount = 0;

    /**
     * @var int
     */
    protected $sourceVotesCount = 0;

    protected $userType;

    protected $vip = false;

    protected $newEmailToConfirm = null;
    protected $newEmailConfirmationToken = null;

    protected $emailConfirmationSentAt = null;

    protected $deletedAccountAt = null;

    protected $smsConfirmationSentAt = null;
    protected $smsConfirmationCode = null;
    protected $phoneConfirmed = false;

    protected $alertExpirationSent = false;

    protected $followingProposals;

    protected $notificationsConfiguration;

    protected $archives;

    /**
     * @var string
     */
    private $slug;

    private $responses;

    private $profilePageIndexed = true;

    private $consentExternalCommunication = false;

    private $userGroups;

    public function __construct($encoder = null)
    {
        parent::__construct();

        $this->encoder = $encoder;
        $this->roles = ['ROLE_USER'];
        $this->opinions = new ArrayCollection();
        $this->opinionVersions = new ArrayCollection();
        $this->responses = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->arguments = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->sources = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->replies = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->followingProposals = new ArrayCollection();
        $this->notificationsConfiguration = new UserNotificationsConfiguration();
        $this->archives = new ArrayCollection();
    }

    public function setSamlAttributes(string $idp, array $attributes)
    {
        if ($this->getId()) {
            return;
        }

        if ('oda' === $idp) {
            $this->setUsername($attributes['oda_prenom'][0] . ' ' . $attributes['oda_nom'][0]);
        }
        if ('daher' === $idp) {
            $this->setUsername($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/upn'][0]);
            $this->setEmail($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/upn'][0]);
            $this->setEmailCanonical((new Canonicalizer())->canonicalize($attributes['http://schemas.xmlsoap.org/ws/2005/05/identity/claims/upn'][0]));
        }
        if ('afd-interne' === $idp) {
            $this->setUsername($attributes['cn'][0]);
            $this->setEmail($attributes['mail'][0]);
            $this->setEmailCanonical((new Canonicalizer())->canonicalize($attributes['mail'][0]));
        }

        if ('pole-emploi' === $idp) {
            $this->setUsername($attributes['cn'][0]);
            $this->setEmail($attributes['mail'][0]);
            $this->setEmailCanonical((new Canonicalizer())->canonicalize($attributes['mail'][0]));
        }
    }

    public function setSamlId(string $samlId): self
    {
        $this->samlId = $samlId;

        return $this;
    }

    public function clearLastLogin()
    {
        $this->lastLogin = null;
    }

    public function getSamlId()
    {
        return $this->samlId;
    }

    public function setParisId(string $parisId): self
    {
        $this->parisId = $parisId;

        return $this;
    }

    public function getParisId(): ?string
    {
        return $this->parisId;
    }

    // used as a lifecycleCallback
    public function sanitizePhoneNumber()
    {
        if ($this->phone) {
            $this->phone = '+' . preg_replace('/\D/', '', $this->phone);
        }
    }

    /*  http://symfony.com/doc/2.8/security/entity_provider.html#understanding-serialize-and-how-a-user-is-saved-in-the-session  */
    /*  We check the account is not deleted in case of the user has multiple accounts sessions and just deleted his account */
    public function isEqualTo(RealUserInterface $user)
    {
        return $user instanceof self && $this->id === $user->getId() && null === $user->getDeletedAccountAt();
    }

    public function addResponse(AbstractResponse $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setUser($this);
        }

        return $this;
    }

    public function removeResponse(AbstractResponse $response): self
    {
        $this->responses->removeElement($response);

        return $this;
    }

    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function setResponses(Collection $responses): self
    {
        $this->responses = $responses;
        foreach ($responses as $response) {
            $response->setUser($this);
        }

        return $this;
    }

    public function getNewEmailToConfirm()
    {
        return $this->newEmailToConfirm;
    }

    public function setNewEmailToConfirm(string $email = null): self
    {
        $this->newEmailToConfirm = $email;

        return $this;
    }

    public function getNewEmailConfirmationToken(): string
    {
        return $this->newEmailConfirmationToken;
    }

    public function setNewEmailConfirmationToken(string $token = null): self
    {
        $this->newEmailConfirmationToken = $token;

        return $this;
    }

    // for EncoderAwareInterface
    public function getEncoderName()
    {
        return $this->encoder;
    }

    // for serialization
    public function getEncoder()
    {
        return $this->encoder;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return int
     */
    public function getGoogleId()
    {
        return $this->google_id;
    }

    /**
     * @param int $google_id
     */
    public function setGoogleId($google_id)
    {
        $this->google_id = $google_id;
    }

    /**
     * @return int
     */
    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }

    /**
     * @param int $facebook_access_token
     */
    public function setFacebookAccessToken($facebook_access_token)
    {
        $this->facebook_access_token = $facebook_access_token;
    }

    /**
     * @return int
     */
    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    /**
     * @param int $facebook_id
     */
    public function setFacebookId($facebook_id)
    {
        $this->facebook_id = $facebook_id;
    }

    /**
     * @return int
     */
    public function getGoogleAccessToken()
    {
        return $this->google_access_token;
    }

    /**
     * @param int $google_access_token
     */
    public function setGoogleAccessToken($google_access_token)
    {
        $this->google_access_token = $google_access_token;
    }

    public function setTwitterId($twitter_id)
    {
        $this->twitter_id = $twitter_id;

        return $this;
    }

    public function getTwitterId()
    {
        return $this->twitter_id;
    }

    public function setTwitterAccessToken($twitter_access_token)
    {
        $this->twitter_access_token = $twitter_access_token;
    }

    public function getTwitterAccessToken()
    {
        return $this->twitter_access_token;
    }

    /**
     * @return int
     */
    public function getFacebookUrl()
    {
        return $this->facebookUrl;
    }

    /**
     * @param int $facebookUrl
     */
    public function setFacebookUrl($facebookUrl)
    {
        $this->facebookUrl = $facebookUrl;
    }

    public function getLinkedInUrl(): ?string
    {
        return $this->linkedInUrl;
    }

    public function setLinkedInUrl(?string $linkedInUrl): self
    {
        $this->linkedInUrl = $linkedInUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getTwitterUrl()
    {
        return $this->twitterUrl;
    }

    /**
     * @param string $twitterUrl
     */
    public function setTwitterUrl($twitterUrl)
    {
        $this->twitterUrl = $twitterUrl;
    }

    /**
     * Set media.
     *
     * @param Media $media
     *
     * @return User
     */
    public function setMedia(Media $media = null)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get media.
     *
     * @return \Capco\MediaBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return int
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function setZipCode(?int $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getNeighborhood()
    {
        return $this->neighborhood;
    }

    /**
     * @param string $neighborhood
     */
    public function setNeighborhood($neighborhood)
    {
        $this->neighborhood = $neighborhood;
    }

    public function getOpinions()
    {
        return $this->opinions;
    }

    public function getOpinionVersions()
    {
        return $this->opinionVersions;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getVotes()
    {
        return $this->votes;
    }

    public function getSources()
    {
        return $this->sources;
    }

    public function getProposals()
    {
        return $this->proposals;
    }

    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Gets the value of projectsCount.
     *
     * @return int
     */
    public function getProjectsCount()
    {
        return $this->projectsCount;
    }

    /**
     * Gets the value of sourcesCount.
     *
     * @return int
     */
    public function getSourcesCount()
    {
        return $this->sourcesCount;
    }

    /**
     * Gets the value of argumentsCount.
     *
     * @return int
     */
    public function getArgumentsCount()
    {
        return $this->argumentsCount;
    }

    /**
     * Gets the value of opinionsCount.
     *
     * @return int
     */
    public function getOpinionsCount()
    {
        return $this->opinionsCount;
    }

    /**
     * Sets the value of opinionsCount.
     *
     * @param int $opinionsCount the opinions count
     *
     * @return self
     */
    public function setOpinionsCount($opinionsCount)
    {
        $this->opinionsCount = $opinionsCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getOpinionVersionsCount()
    {
        return $this->opinionVersionsCount;
    }

    /**
     * @param int $opinionVersionsCount
     */
    public function setOpinionVersionsCount($opinionVersionsCount)
    {
        $this->opinionVersionsCount = $opinionVersionsCount;
    }

    /**
     * @return int
     */
    public function getProposalsCount()
    {
        return $this->proposalsCount;
    }

    /**
     * @param int $proposalsCount
     *
     * @return $this
     */
    public function setProposalsCount($proposalsCount)
    {
        $this->proposalsCount = $proposalsCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getRepliesCount()
    {
        return $this->repliesCount;
    }

    /**
     * @param int $repliesCount
     *
     * @return $this
     */
    public function setRepliesCount($repliesCount)
    {
        $this->repliesCount = $repliesCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getProposalVotesCount()
    {
        return $this->proposalVotesCount;
    }

    /**
     * @param int $proposalVotesCount
     *
     * @return $this
     */
    public function setProposalVotesCount($proposalVotesCount)
    {
        $this->proposalVotesCount = $proposalVotesCount;

        return $this;
    }

    /**
     * Sets the value of argumentsCount.
     *
     * @param int $argumentsCount the arguments count
     *
     * @return self
     */
    public function setArgumentsCount($argumentsCount)
    {
        $this->argumentsCount = $argumentsCount;

        return $this;
    }

    /**
     * Sets the value of projectsCount.
     *
     * @param int $projectsCount the sources count
     *
     * @return self
     */
    public function setProjectsCount($projectsCount)
    {
        $this->projectsCount = $projectsCount;

        return $this;
    }

    /**
     * Sets the value of sourcesCount.
     *
     * @param int $sourcesCount the sources count
     *
     * @return self
     */
    public function setSourcesCount($sourcesCount)
    {
        $this->sourcesCount = $sourcesCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getPostCommentsCount()
    {
        return $this->postCommentsCount;
    }

    /**
     * @param int $postCommentsCount
     */
    public function setPostCommentsCount($postCommentsCount)
    {
        $this->postCommentsCount = $postCommentsCount;
    }

    /**
     * @return int
     */
    public function getOpinionVotesCount()
    {
        return $this->opinionVotesCount;
    }

    /**
     * @param int $opinionVotesCount
     */
    public function setOpinionVotesCount($opinionVotesCount)
    {
        $this->opinionVotesCount = $opinionVotesCount;
    }

    /**
     * @return int
     */
    public function getOpinionVersionVotesCount()
    {
        return $this->opinionVersionVotesCount;
    }

    /**
     * @param int $opinionVersionVotesCount
     */
    public function setOpinionVersionVotesCount($opinionVersionVotesCount)
    {
        $this->opinionVersionVotesCount = $opinionVersionVotesCount;
    }

    /**
     * @return int
     */
    public function getSourceVotesCount()
    {
        return $this->sourceVotesCount;
    }

    /**
     * @param int $sourceVotesCount
     */
    public function setSourceVotesCount($sourceVotesCount)
    {
        $this->sourceVotesCount = $sourceVotesCount;
    }

    /**
     * @return int
     */
    public function getEventCommentsCount()
    {
        return $this->eventCommentsCount;
    }

    /**
     * @param int $eventCommentsCount
     */
    public function setEventCommentsCount($eventCommentsCount)
    {
        $this->eventCommentsCount = $eventCommentsCount;
    }

    /**
     * @return int
     */
    public function getProposalCommentsCount(): int
    {
        return $this->proposalCommentsCount;
    }

    /**
     * @param int $proposalCommentsCount
     */
    public function setProposalCommentsCount($proposalCommentsCount)
    {
        $this->proposalCommentsCount = $proposalCommentsCount;
    }

    /**
     * @return int
     */
    public function getCommentVotesCount()
    {
        return $this->commentVotesCount;
    }

    /**
     * @param int $commentVotesCount
     */
    public function setCommentVotesCount($commentVotesCount)
    {
        $this->commentVotesCount = $commentVotesCount;
    }

    /**
     * @return int
     */
    public function getArgumentVotesCount()
    {
        return $this->argumentVotesCount;
    }

    /**
     * @param int $argumentVotesCount
     */
    public function setArgumentVotesCount($argumentVotesCount)
    {
        $this->argumentVotesCount = $argumentVotesCount;
    }

    /**
     * Sets the value of votes.
     *
     * @param mixed $votes the votes
     *
     * @return self
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;

        return $this;
    }

    public function setProposals($proposals)
    {
        $this->proposals = $proposals;

        return $this;
    }

    public function setOpinions($opinions)
    {
        $this->opinions = $opinions;

        return $this;
    }

    public function setOpinionVersions($versions)
    {
        $this->opinionVersions = $versions;

        return $this;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    public function setSources($sources)
    {
        $this->sources = $sources;

        return $this;
    }

    public function setReplies($replies)
    {
        $this->replies = $replies;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserType()
    {
        return $this->userType;
    }

    public function setUserType(UserType $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function isVip()
    {
        return $this->vip;
    }

    /**
     * @param mixed $vip
     *
     * @return $this
     */
    public function setVip($vip)
    {
        $this->vip = $vip;

        return $this;
    }

    public function isAlertExpirationSent()
    {
        return $this->alertExpirationSent;
    }

    public function setAlertExpirationSent($alertExpirationSent)
    {
        $this->alertExpirationSent = $alertExpirationSent;

        return $this;
    }

    public function getEmailConfirmationSentAt()
    {
        return $this->emailConfirmationSentAt;
    }

    public function setEmailConfirmationSentAt(\DateTime $date = null)
    {
        $this->emailConfirmationSentAt = $date;

        return $this;
    }

    public function getDeletedAccountAt(): ?\DateTime
    {
        return $this->deletedAccountAt;
    }

    public function setDeletedAccountAt(\DateTime $date): self
    {
        $this->deletedAccountAt = $date;

        return $this;
    }

    public function getSmsConfirmationSentAt()
    {
        return $this->smsConfirmationSentAt;
    }

    public function setSmsConfirmationSentAt(\DateTime $date = null)
    {
        $this->smsConfirmationSentAt = $date;

        return $this;
    }

    public function getSmsConfirmationCode()
    {
        return $this->smsConfirmationCode;
    }

    public function setSmsConfirmationCode($code = null)
    {
        $this->smsConfirmationCode = $code;

        return $this;
    }

    public function isPhoneConfirmed()
    {
        return $this->phoneConfirmed;
    }

    public function setPhoneConfirmed($phoneConfirmed)
    {
        $this->phoneConfirmed = $phoneConfirmed;

        return $this;
    }

    public function setExpiresAt(\DateTime $date = null)
    {
        $this->expiresAt = $date;

        return $this;
    }

    // ************************* Custom methods *********************************

    public function isEmailConfirmed()
    {
        return null === $this->confirmationToken;
    }

    public function getFullname()
    {
        return sprintf(
            '%s %s',
            $this->getFirstname(),
            $this->getLastname()
        );
    }

    public static function getGenderList()
    {
        return [
            UserInterface::GENDER_FEMALE => 'gender.female',
            UserInterface::GENDER_MALE => 'gender.male',
            self::GENDER_OTHER => 'gender.other',
        ];
    }

    public function getContributions(string $requestedType = null)
    {
        switch ($requestedType) {
            case 'Opinions':
                return $this->getOpinions();
                break;
            case 'OpinionVersions':
                return $this->getOpinionVersions();
                break;
            case 'Votes':
                return $this->getVotes();
                break;
            case 'Comments':
                return $this->getComments();
                break;
            case 'Arguments':
                return $this->getArguments();
                break;
            case 'Sources':
                return $this->getSources();
                break;
            case 'Proposals':
                return $this->getProposals();
                break;
            case 'Replies':
                return $this->getReplies();
                break;
            default:
                $types = [
                    $this->getOpinions(),
                    $this->getOpinionVersions(),
                    $this->getVotes(),
                    $this->getComments(),
                    $this->getArguments(),
                    $this->getSources(),
                    $this->getProposals(),
                    $this->getReplies(),
                ];

                $contributions = [];
                foreach ($types as $type) {
                    foreach ($type as $contribution) {
                        $contributions[] = $contribution;
                    }
                }

                break;
        }

        return $contributions;
    }

    public function getContributionsCount()
    {
        return $this->sourcesCount + $this->argumentsCount + $this->opinionsCount + $this->opinionVersionsCount + $this->getCommentsCount() + $this->proposalsCount;
    }

    public function getVotesCount()
    {
        return $this->commentVotesCount + $this->opinionVotesCount + $this->opinionVersionVotesCount + $this->argumentVotesCount + $this->sourceVotesCount + $this->proposalVotesCount;
    }

    public function getCommentsCount()
    {
        return $this->postCommentsCount + $this->eventCommentsCount + $this->proposalCommentsCount;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    // ********************* Methods for synthesis tool **************************

    public function getUniqueIdentifier()
    {
        return $this->slug;
    }

    public function getDisplayName()
    {
        return $this->username ?: 'Utilisateur sans nom';
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('ROLE_SUPER_ADMIN');
    }

    /**
     * Tell if user has role admin or super admin.
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->hasRole('ROLE_ADMIN') || $this->hasRole('ROLE_SUPER_ADMIN');
    }

    public function isEvaluer(): bool
    {
        foreach ($this->userGroups as $userGroup) {
            if ($userGroup->getGroup()->isEvaluating()) {
                return true;
            }
        }

        return false;
    }

    public function isProfilePageIndexed(): bool
    {
        return !$this->profilePageIndexed;
    }

    public function setProfilePageIndexed(bool $profilePageIndexed = true)
    {
        $this->profilePageIndexed = !$profilePageIndexed;
    }

    public function getNotificationsConfiguration(): UserNotificationsConfiguration
    {
        return $this->notificationsConfiguration;
    }

    public function setNotificationsConfiguration(UserNotificationsConfiguration $notificationsConfiguration): self
    {
        $this->notificationsConfiguration = $notificationsConfiguration;

        return $this;
    }

    public function isConsentExternalCommunication(): bool
    {
        return $this->consentExternalCommunication;
    }

    public function setConsentExternalCommunication(bool $consentExternalCommunication): self
    {
        $this->consentExternalCommunication = $consentExternalCommunication;

        return $this;
    }

    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function setUserGroups(Collection $userGroups): self
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

    public function getFollowingProposals(): Collection
    {
        return $this->followingProposals;
    }

    public function addFollowingProposal(Follower $followingProposal): self
    {
        if (!$this->followingProposals->contains($followingProposal)) {
            $this->followingProposals->add($followingProposal);
        }

        return $this;
    }

    public function removeFollowingProposal(Follower $followingProposal): self
    {
        $this->followingProposals->removeElement($followingProposal);

        return $this;
    }

    public function setFollowingProposals(Collection $followingProposals): self
    {
        $this->followingProposals = $followingProposals;

        return $this;
    }

    public function isIndexable(): bool
    {
        return $this->isEnabled();
    }

    public static function getElasticsearchTypeName(): string
    {
        return 'user';
    }

    public static function getElasticsearchSerializationGroups(): array
    {
        return ['Elasticsearch'];
    }

    public function addArchive(UserArchive $archive): self
    {
        if (!$this->archives->contains($archive)) {
            $this->archives->add($archive);
        }

        $archive->setUser($this);

        return $this;
    }

    public function getArchives(): Collection
    {
        return $this->archives;
    }

    public function setArchives(Collection $archives): self
    {
        $this->archives = $archives;

        return $this;
    }

    public function removeArchive(UserArchive $archive): self
    {
        $this->archives->removeElement($archive);

        return $this;
    }
}
