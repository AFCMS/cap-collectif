<?php

namespace Capco\AppBundle\Model;

interface Argumentable
{
    public function getArguments();

    public function canContribute($user = null);

    public function getOpinionType();

    public function getStep();
}
