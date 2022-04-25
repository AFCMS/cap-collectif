<?php

namespace spec\Capco\AppBundle\Following;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Following\ActivitiesResolver;
use Capco\AppBundle\GraphQL\DataLoader\Proposal\ProposalCurrentVotableStepDataLoader;
use Capco\AppBundle\Model\UserActivity;
use Capco\AppBundle\Repository\FollowerRepository;
use Capco\AppBundle\Repository\OfficialResponseRepository;
use Capco\AppBundle\Repository\PostRepository;
use Capco\AppBundle\Repository\ProjectRepository;
use Capco\AppBundle\Repository\ProposalFormRepository;
use Capco\AppBundle\Repository\ProposalRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Monolog\Logger;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Routing\Router;

class ProposalActivitiesResolverSpec extends ObjectBehavior
{
    public function let(
        FollowerRepository $followerRepository,
        ProposalRepository $proposalRepository,
        PostRepository $postRepository,
        ProposalFormRepository $proposalFormRepository,
        ProjectRepository $projectRepository,
        OfficialResponseRepository $officialResponseRepository,
        Logger $logger,
        Router $router,
        ProposalCurrentVotableStepDataLoader $proposalCurrentVotableStepDataLoader
    ) {
        $this->beConstructedWith(
            $followerRepository,
            $proposalRepository,
            $postRepository,
            $proposalFormRepository,
            $projectRepository,
            $officialResponseRepository,
            $logger,
            $router,
            $proposalCurrentVotableStepDataLoader
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ActivitiesResolver::class);
    }

    public function it_can_resolver_yesterday_activities(
        ProposalForm $proposalForm,
        Proposal $proposal,
        FollowerRepository $followerRepository,
        ProposalRepository $proposalRepository,
        PostRepository $postRepository,
        ProposalFormRepository $proposalFormRepository,
        ProjectRepository $projectRepository,
        OfficialResponseRepository $officialResponseRepository,
        Logger $logger,
        Router $router,
        ProposalCurrentVotableStepDataLoader $proposalCurrentVotableStepDataLoader
    ) {
        $proposalFormRepository->findAll()->willReturn([]);

        $this->beConstructedWith(
            $followerRepository,
            $proposalRepository,
            $postRepository,
            $proposalFormRepository,
            $projectRepository,
            $officialResponseRepository,
            $logger,
            $router,
            $proposalCurrentVotableStepDataLoader
        );

        $this->getActivitiesByRelativeTime()->shouldBe([]);

        $proposalForm->getProposals()->willReturn(new ArrayCollection([]));
        $proposalFormRepository->findAll()->willReturn([$proposalForm]);
        $this->getActivitiesByRelativeTime()->shouldBe([]);
    }

    //
    //    function it_can_resolver_yesterday_activities_2(ProposalForm $proposalForm,Proposal $proposal, FollowerRepository $followerRepository, ProposalRepository $proposalRepository, ProposalFormRepository $proposalFormRepository, Logger $logger, Router $router)
    //    {
    //        $proposalRepository->countProposalCommentsCreatedBetween()->willReturn(0);
    //        $proposal->getStep()->willReturn(null);
    //        $proposal->getId()->willReturn('proposal1');
    //        $proposalForm->getProposals()->willReturn(new ArrayCollection([$proposal]));
    //        $proposalFormRepository->findAll()->willReturn([
    //            $proposalForm
    //        ]);
    //        $this->beConstructedWith($followerRepository, $proposalRepository, $proposalFormRepository, $logger, $router);
    //
    //        $this->getYesterdayActivities()->shouldBe([]);
    //    }

    public function it_should_matching_user_without_project_activities(UserActivity $userActivity)
    {
        $userActivity->setUserProposals(Argument::type('array'))->willReturn($userActivity);
        $userActivity->hasUserProject()->willReturn(false);
        $userActivity->hasProposal()->willReturn(false);
        $userActivity->getUserProposals()->willReturn([]);
        $this->getMatchingActivitiesByUserId(
            ['user1' => $userActivity],
            ['proposal1' => []]
        )->shouldReturn([]);
    }

    public function it_should_not_matching_activities_with_empty_parameters()
    {
        $this->getMatchingActivitiesByUserId([], [])->shouldReturn([]);
    }

    public function it_matching_activities_by_user(UserActivity $userActivity)
    {
        $userProposalsComplete = [
            'proposal1' => [
                'projectId' => 'project1',
                'comments' => 10,
                'votes' => 5,
                'lastStep' => 'Selection',
            ],
            'proposal2' => [
                'projectId' => 'project2',
                'comments' => 10,
                'votes' => 5,
                'lastStep' => false,
            ],
        ];
        $userProposalsUncomplete = [
            'proposal1' => [
                'projectId' => '',
                'comments' => 0,
                'votes' => 0,
                'lastStep' => false,
            ],
        ];
        $userProposalsEmpty = [];

        $userProject = [
            'proposals' => [$userProposalsComplete],
            'projectTitle' => 'Project title',
            'projectType' => 'project type',
        ];

        $userActivity->setUserProposals(Argument::type('array'))->willReturn($userActivity);
        $userActivity->hasUserProject()->willReturn(true);
        $userActivity->hasProposal()->willReturn(true);
        $userActivity->getNotifiedOf()->willReturn('1');
        $userActivity->getUserProjects()->willReturn($userProject);
        $userActivity->removeUserProject(Argument::type('string'))->willReturn($userActivity);

        $userActivity->getUserProposals()->willReturn($userProposalsComplete);
        $this->getMatchingActivitiesByUserId(
            ['user1' => $userActivity],
            ['proposal1' => ['countActivities' => 1, 'projectId' => 'project1']]
        )->shouldReturn(['user1' => $userActivity]);

        $userActivity->getUserProposals()->willReturn($userProposalsUncomplete);
        $this->getMatchingActivitiesByUserId(
            ['user1' => $userActivity],
            ['proposal1' => ['countActivities' => 1, 'projectId' => 'project1']]
        )->shouldReturn(['user1' => $userActivity]);

        $userActivity->getUserProposals()->willReturn($userProposalsEmpty);
        $this->getMatchingActivitiesByUserId(
            ['user1' => $userActivity],
            ['proposal1' => ['countActivities' => 1, 'projectId' => 'project1']]
        )->shouldReturn(['user1' => $userActivity]);
    }

    public function it_matching_activities_by_user_without_proposal_in_user_project(
        UserActivity $userActivity
    ) {
        $userProposalsComplete = [
            'proposal1' => [
                'projectId' => 'project1',
                'comments' => 10,
                'votes' => 5,
                'lastStep' => 'Selection',
            ],
            'proposal2' => [
                'projectId' => 'project2',
                'comments' => 10,
                'votes' => 5,
                'lastStep' => false,
            ],
        ];

        $userProject = [
            'proposals' => [],
            'projectTitle' => 'Project title',
            'projectType' => 'project type',
        ];
        $userActivity->setUserProposals(Argument::type('array'))->willReturn($userActivity);
        $userActivity->hasUserProject()->willReturn(true);
        $userActivity->hasProposal()->willReturn(true);
        $userActivity->getNotifiedOf()->willReturn('1');
        $userActivity->getUserProjects()->willReturn($userProject);
        $userActivity->removeUserProject(Argument::type('string'))->willReturn($userActivity);

        $userActivity->getUserProposals()->willReturn($userProposalsComplete);
        $this->getMatchingActivitiesByUserId(
            ['user1' => $userActivity],
            ['proposal1' => ['countActivities' => 1, 'projectId' => 'project1']]
        )->shouldReturn(['user1' => $userActivity]);
    }
}
