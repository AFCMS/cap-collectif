<?php

namespace Capco\AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DidNotAlreadyVote extends Constraint
{
    public $message = 'global.already_voted';
    public $repositoryPath = '';
    public $objectPath = '';

    public function validatedBy()
    {
        return 'already_vote.validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
