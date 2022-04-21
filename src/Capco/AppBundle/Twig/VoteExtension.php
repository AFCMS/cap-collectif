<?php

namespace Capco\AppBundle\Twig;

use Capco\AppBundle\Entity\AbstractVote as Vote;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Resolver\ProposalVotesResolver;
use Capco\AppBundle\Resolver\VoteResolver;

class VoteExtension extends \Twig_Extension
{
    protected $voteResolver;
    protected $proposalVotesResolver;

    public function __construct(VoteResolver $voteResolver, ProposalVotesResolver $proposalVotesResolver)
    {
        $this->voteResolver = $voteResolver;
        $this->proposalVotesResolver = $proposalVotesResolver;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'capco_vote';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('capco_vote_object_url', [$this, 'getRelatedObjectUrl']),
            new \Twig_SimpleFunction('capco_vote_object', [$this, 'getRelatedObject']),
            new \Twig_SimpleFunction('capco_vote_object_admin_url', [$this, 'getRelatedObjectAdminUrl']),
            new \Twig_SimpleFunction('capco_has_votable_step_not_future', [$this, 'hasVotableStepNotFuture']),
        ];
    }

    public function getRelatedObjectUrl(Vote $vote, $absolute = false)
    {
        return $this->voteResolver->getRelatedObjectUrl($vote, $absolute);
    }

    public function getRelatedObjectAdminUrl(Vote $vote, $absolute = false)
    {
        return $this->voteResolver->getRelatedObjectAdminUrl($vote, $absolute);
    }

    public function getRelatedObject(Vote $vote)
    {
        return $this->voteResolver->getRelatedObject($vote);
    }

    public function hasVotableStepNotFuture(Project $project)
    {
        return $this->proposalVotesResolver->hasVotableStepNotFuture($project);
    }
}
