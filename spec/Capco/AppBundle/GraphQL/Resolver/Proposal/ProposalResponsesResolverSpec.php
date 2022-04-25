<?php

namespace spec\Capco\AppBundle\GraphQL\Resolver\Proposal;

use PhpSpec\ObjectBehavior;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalForm;
use Doctrine\Common\Collections\ArrayCollection;
use Capco\AppBundle\Entity\Questions\AbstractQuestion;
use Capco\AppBundle\Entity\Responses\AbstractResponse;
use Capco\AppBundle\Repository\AbstractQuestionRepository;
use Capco\AppBundle\Repository\AbstractResponseRepository;
use Capco\AppBundle\GraphQL\Resolver\Proposal\ProposalResponsesResolver;
use Capco\AppBundle\GraphQL\Resolver\Proposal\ProposalViewerIsAnEvaluerResolver;

class ProposalResponsesResolverSpec extends ObjectBehavior
{
    public function let(AbstractQuestionRepository $abstractQuestionRepository,
                        AbstractResponseRepository $abstractResponseRepository,
                        ProposalViewerIsAnEvaluerResolver $viewerIsAnEvaluer): void
    {
        $this->beConstructedWith($abstractQuestionRepository, $abstractResponseRepository, $viewerIsAnEvaluer);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProposalResponsesResolver::class);
    }

    public function it_should_return_all_responses_when_acl_are_disabled(
        AbstractQuestionRepository $abstractQuestionRepository,
        AbstractResponseRepository $abstractResponseRepository,
        ProposalViewerIsAnEvaluerResolver $viewerIsAnEvaluer,
        ProposalForm $form,
        Proposal $proposal,
        User $author,
        AbstractResponse $response,
        AbstractQuestion $question
    ): void
    {
        $viewer = null;
        $context = new \ArrayObject(['disable_acl' => true]);
        $question->isPrivate()->willReturn(true);
        $question->getId()->willReturn('question1');
        $response->getQuestion()->willReturn($question);
        $questions = new ArrayCollection([$question->getWrappedObject()]);
        $responses = new ArrayCollection([$response->getWrappedObject()]);
        $proposal->getForm()->willReturn($form);
        $abstractResponseRepository->getByProposal($proposal, true)->willReturn($responses->toArray());
        $abstractQuestionRepository->findByProposalForm($form)->willReturn($questions->toArray());
        $proposal->getAuthor()->willReturn($author);
        $viewerIsAnEvaluer->__invoke($proposal, $viewer)->willReturn(false);

        $this->__invoke($proposal, $viewer, $context)->shouldBeLike($responses->getIterator());
    }

    public function it_should_return_all_responses_when_viewer_is_author(
        AbstractQuestionRepository $abstractQuestionRepository,
        AbstractResponseRepository $abstractResponseRepository,
        ProposalViewerIsAnEvaluerResolver $viewerIsAnEvaluer,
        Proposal $proposal,
        ProposalForm $form,
        User $author,
        AbstractResponse $response,
        AbstractQuestion $question
    ): void
    {
        $viewer = $author;
        $context = new \ArrayObject(['disable_acl' => false]);
        $question->isPrivate()->willReturn(true);
        $question->getId()->willReturn('question1');
        $response->getQuestion()->willReturn($question);
        $questions = new ArrayCollection([$question->getWrappedObject()]);
        $responses = new ArrayCollection([$response->getWrappedObject()]);
        $proposal->getForm()->willReturn($form);
        $abstractResponseRepository->getByProposal($proposal, true)->willReturn($responses->toArray());
        $abstractQuestionRepository->findByProposalForm($form)->willReturn($questions->toArray());
        $proposal->getAuthor()->willReturn($author);
        $viewerIsAnEvaluer->__invoke($proposal, $viewer)->willReturn(false);
        $this->__invoke($proposal, $viewer, $context)->shouldBeLike($responses->getIterator());
    }

    public function it_should_not_return_private_responses_when_viewer_is_anonymous(
        AbstractQuestionRepository $abstractQuestionRepository,
        AbstractResponseRepository $abstractResponseRepository,
        ProposalViewerIsAnEvaluerResolver $viewerIsAnEvaluer,
        ProposalForm $form,
        Proposal $proposal,
        User $author,
        AbstractResponse $response,
        AbstractQuestion $question
    ): void
    {
        $viewer = null;
        $context = new \ArrayObject(['disable_acl' => false]);
        $question->isPrivate()->willReturn(true);
        $question->getId()->willReturn('question1');
        $response->getQuestion()->willReturn($question);
        $questions = new ArrayCollection([$question->getWrappedObject()]);
        $responses = new ArrayCollection([$response->getWrappedObject()]);
        $proposal->getForm()->willReturn($form);
        $abstractResponseRepository->getByProposal($proposal, true)->willReturn($responses->toArray());
        $abstractQuestionRepository->findByProposalForm($form)->willReturn($questions->toArray());
        $proposal->getAuthor()->willReturn($author);
        $viewerIsAnEvaluer->__invoke($proposal, $viewer)->willReturn(false);
        $this->__invoke($proposal, $viewer, $context)->shouldBeLike(
            (new ArrayCollection([]))->getIterator()
        );
    }

    public function it_should_not_return_private_responses_when_viewer_is_not_author(
        AbstractQuestionRepository $abstractQuestionRepository,
        AbstractResponseRepository $abstractResponseRepository,
        ProposalViewerIsAnEvaluerResolver $viewerIsAnEvaluer,
        ProposalForm $form,
        Proposal $proposal,
        User $author,
        User $viewer,
        AbstractResponse $response,
        AbstractQuestion $question
    ): void
    {
        $context = new \ArrayObject(['disable_acl' => false]);
        $question->isPrivate()->willReturn(true);
        $question->getId()->willReturn('question1');
        $response->getQuestion()->willReturn($question);
        $questions = new ArrayCollection([$question->getWrappedObject()]);
        $responses = new ArrayCollection([$response->getWrappedObject()]);
        $viewer->isAdmin()->willReturn(false);
        $proposal->getForm()->willReturn($form);
        $abstractResponseRepository->getByProposal($proposal, true)->willReturn($responses->toArray());
        $abstractQuestionRepository->findByProposalForm($form)->willReturn($questions->toArray());
        $proposal->getAuthor()->willReturn($author);
        $viewerIsAnEvaluer->__invoke($proposal, $viewer)->willReturn(false);
        $this->__invoke($proposal, $viewer, $context)->shouldBeLike(
            (new ArrayCollection([]))->getIterator()
        );
    }
}
