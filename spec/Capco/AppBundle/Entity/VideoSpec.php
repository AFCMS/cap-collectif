<?php

namespace spec\Capco\AppBundle\Entity;

use PhpSpec\ObjectBehavior;

class VideoSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Capco\AppBundle\Entity\Video');
    }
}
