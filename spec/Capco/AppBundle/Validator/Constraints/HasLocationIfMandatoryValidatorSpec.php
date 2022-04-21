<?php

namespace spec\Capco\AppBundle\Validator\Constraints;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Validator\Constraints\HasLocationIfMandatory;
use Capco\AppBundle\Validator\Constraints\HasLocationIfMandatoryValidator;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class HasLocationIfMandatoryValidatorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(HasLocationIfMandatoryValidator::class);
    }

    public function it_should_validate_if_proposal_has_location(
         ExecutionContextInterface $context,
         HasLocationIfMandatory $constraint,
         Proposal $proposal,
        ProposalForm $proposalForm
     ) {
        $proposalForm->getUsingAddress()->willReturn(true)->shouldBeCalled();
        $proposal->getLocation()->willReturn(Argument::type('string'))->shouldBeCalled();
        $proposal->getProposalForm()->willReturn($proposalForm)->shouldBeCalled();

        $context->buildViolation($constraint->message)->shouldNotBeCalled();
        $this->initialize($context);
        $this->validate($proposal, $constraint);
    }

    public function it_should_not_validate_if_proposal_has_no_location(
        ExecutionContextInterface $context,
        HasLocationIfMandatory $constraint,
        Proposal $proposal,
        ProposalForm $proposalForm,
        ConstraintViolationBuilderInterface $builder
    ) {
        $proposalForm->getUsingAddress()->willReturn(true)->shouldBeCalled();
        $proposal->getLocation()->willReturn(null)->shouldBeCalled();
        $proposal->getProposalForm()->willReturn($proposalForm)->shouldBeCalled();

        $builder->addViolation()->shouldBeCalled();
        $context->buildViolation($constraint->message)->willReturn($builder)->shouldBeCalled();
        $this->initialize($context);
        $this->validate($proposal, $constraint);
    }
}
