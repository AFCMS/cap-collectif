<?php

namespace spec\Capco\AppBundle\Synthesis\Handler;

use Capco\AppBundle\Entity\Synthesis\Synthesis;
use Capco\AppBundle\Entity\Synthesis\SynthesisDivision;
use Capco\AppBundle\Entity\Synthesis\SynthesisElement;
use Capco\AppBundle\Repository\Synthesis\SynthesisElementRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use PhpSpec\ObjectBehavior;
use Capco\AppBundle\Manager\LogManager;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

class SynthesisElementHandlerSpec extends ObjectBehavior
{
    function let(EntityManager $em, LogManager $logManager)
    {
        $this->beConstructedWith($em, $logManager);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Capco\AppBundle\Synthesis\Handler\SynthesisElementHandler');
    }

    function it_can_get_all_elements_from_synthesis(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Paginator $paginator, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);
        $offset = 0;
        $limit = null;

        $paginator->getIterator()->willReturn(new \ArrayIterator());
        $paginator->count()->willReturn(17);

        $type = 'all';
        $term = null;
        $synthesisElementRepo->getWith($synthesis, $type, $term, $offset, $limit)->willReturn($paginator)->shouldBeCalled();
        $this->getElementsFromSynthesisByType($synthesis, $type)->shouldBeArray();
    }

    function it_can_get_new_elements_from_synthesis(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Paginator $paginator, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);
        $offset = 0;
        $limit = null;

        $paginator->getIterator()->willReturn(new \ArrayIterator());
        $paginator->count()->willReturn(17);

        $type = 'new';
        $term = null;
        $synthesisElementRepo->getWith($synthesis, $type, $term, $offset, $limit)->willReturn($paginator)->shouldBeCalled();
        $this->getElementsFromSynthesisByType($synthesis, $type)->shouldBeArray();
    }

    function it_can_get_unpublished_elements_from_synthesis(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Paginator $paginator, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);
        $offset = 0;
        $limit = null;

        $paginator->getIterator()->willReturn(new \ArrayIterator());
        $paginator->count()->willReturn(17);

        $type = 'unpublished';
        $term = null;
        $synthesisElementRepo->getWith($synthesis, $type, $term, $offset, $limit)->willReturn($paginator)->shouldBeCalled();
        $this->getElementsFromSynthesisByType($synthesis, $type)->shouldBeArray();
    }

    function it_can_get_published_elements_from_synthesis(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Paginator $paginator, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);
        $offset = 0;
        $limit = null;

        $paginator->getIterator()->willReturn(new \ArrayIterator());
        $paginator->count()->willReturn(17);

        $type = 'published';
        $term = null;
        $synthesisElementRepo->getWith($synthesis, $type, $term, $offset, $limit)->willReturn($paginator)->shouldBeCalled();
        $this->getElementsFromSynthesisByType($synthesis, $type)->shouldBeArray();
    }

    function it_can_get_archived_elements_from_synthesis(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Paginator $paginator, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);
        $offset = 0;
        $limit = null;

        $paginator->getIterator()->willReturn(new \ArrayIterator());
        $paginator->count()->willReturn(17);

        $type = 'archived';
        $term = null;
        $synthesisElementRepo->getWith($synthesis, $type, $term, $offset, $limit)->willReturn($paginator)->shouldBeCalled();
        $this->getElementsFromSynthesisByType($synthesis, $type)->shouldBeArray();
    }

    function it_can_get_elements_published_tree_from_synthesis(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Paginator $paginator, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);

        $parentId = null;
        $depth = 3;
        $type = 'published';

        $synthesisElementRepo->getFormattedTree($synthesis, $type, $parentId, $depth)->shouldBeCalled()->willReturn(array());
        $this->getElementsTreeFromSynthesisByType($synthesis, $type, $parentId, $depth)->shouldBeArray();
    }

    function it_can_get_all_elements_tree_from_synthesis(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Paginator $paginator, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);

        $parentId = null;
        $depth = 3;
        $type = 'all';

        $synthesisElementRepo->getFormattedTree($synthesis, $type, $parentId, $depth)->willReturn(array())->shouldBeCalled();
        $this->getElementsTreeFromSynthesisByType($synthesis, $type, $parentId, $depth)->shouldBeArray();
    }

    function it_can_count_all_elements_from_synthesis_by_type(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);

        $type = 'all';
        $synthesisElementRepo->countWith($synthesis, $type)->willReturn(2)->shouldBeCalled();
        $this->countElementsFromSynthesisByType($synthesis, $type)->shouldBeInteger();
    }

    function it_can_count_new_elements_from_synthesis_by_type(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);

        $type = 'new';
        $synthesisElementRepo->countWith($synthesis, $type)->willReturn(1)->shouldBeCalled();
        $this->countElementsFromSynthesisByType($synthesis, $type)->shouldBeInteger();
    }

    function it_can_count_unpublished_elements_from_synthesis_by_type(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);

        $type = 'unpublished';
        $synthesisElementRepo->countWith($synthesis, $type)->willReturn(0)->shouldBeCalled();
        $this->countElementsFromSynthesisByType($synthesis, $type)->shouldBeInteger();
    }

    function it_can_count_published_elements_from_synthesis_by_type(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);

        $type = 'published';
        $synthesisElementRepo->countWith($synthesis, $type)->willReturn(0)->shouldBeCalled();
        $this->countElementsFromSynthesisByType($synthesis, $type)->shouldBeInteger();
    }

    function it_can_count_archived_elements_from_synthesis_by_type(EntityManager $em, LogManager $logManager, SynthesisElementRepository $synthesisElementRepo, Synthesis $synthesis)
    {
        $em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->willReturn($synthesisElementRepo)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);

        $type = 'archived';
        $synthesisElementRepo->countWith($synthesis, $type)->willReturn(5)->shouldBeCalled();
        $this->countElementsFromSynthesisByType($synthesis, $type)->shouldBeInteger();
    }

    function it_can_create_element_in_synthesis(EntityManager $em, LogManager $logManager, SynthesisElement $element, Synthesis $synthesis)
    {
        $element->setSynthesis($synthesis)->shouldBeCalled();
        $element->setDisplayType('folder')->shouldBeCalled();
        $element->setAuthor(null)->shouldBeCalled();
        $em->persist($element)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->beConstructedWith($em, $logManager);
        $this->createElementInSynthesis($element, $synthesis)->shouldReturnAnInstanceOf('Capco\AppBundle\Entity\Synthesis\SynthesisElement');
    }

    function it_can_update_element_in_synthesis(EntityManager $em, LogManager $logManager, Synthesis $synthesis)
    {
        $element = new SynthesisElement();
        $element->setPublished(true);
        $em->persist($element)->shouldBeCalled();
        $em->flush()->shouldBeCalled();

        $this->beConstructedWith($em, $logManager);
        $this->updateElementInSynthesis($element, $synthesis)->shouldReturnAnInstanceOf('Capco\AppBundle\Entity\Synthesis\SynthesisElement');
    }

    function it_can_update_a_division_from_element_in_synthesis(EntityManager $em, LogManager $logManager, SynthesisDivision $division, SynthesisElement $element, Synthesis $synthesis)
    {
        $elements = new ArrayCollection([$element]);

        $division->getElements()->willReturn($elements)->shouldBeCalled();
        $em->persist($element)->shouldBeCalled();

        $em->persist($division)->shouldBeCalled();

        $this->beConstructedWith($em, $logManager);
        $this->updateDivisionFromElementInSynthesis($division, $element, $synthesis)->shouldReturnAnInstanceOf('Capco\AppBundle\Entity\Synthesis\SynthesisDivision');
    }

    function it_can_get_logs_for_element(EntityManager $em, LogManager $logManager, SynthesisElement $element, ArrayCollection $logs)
    {
        $logManager->getLogEntries($element)->willReturn($logs)->shouldBeCalled();
        $this->beConstructedWith($em, $logManager);
        $this->getLogsForElement($element)->shouldReturnAnInstanceOf('Doctrine\Common\Collections\ArrayCollection');
    }
}
