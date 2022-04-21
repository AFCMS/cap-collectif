<?php

namespace Capco\AppBundle\Synthesis\Handler;

use Capco\AppBundle\Entity\Synthesis\SynthesisUserInterface;
use Doctrine\ORM\EntityManager;
use Capco\AppBundle\Manager\LogManager;
use Capco\AppBundle\Entity\Synthesis\Synthesis;
use Capco\AppBundle\Entity\Synthesis\SynthesisElement;
use Capco\AppBundle\Entity\Synthesis\SynthesisDivision;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SynthesisElementHandler
{
    protected static $types = ['all', 'archived', 'new', 'unpublished', 'published', 'notIgnored'];

    protected $em;
    protected $logManager;

    public function __construct(EntityManager $em, LogManager $logManager)
    {
        $this->em = $em;
        $this->logManager = $logManager;
    }

    public function getElementsFromSynthesisByType($synthesis, $type = null, $term = null, $offset = 0, $limit = null)
    {
        if ($type === null || !in_array($type, self::$types)) {
            throw new NotFoundHttpException();
        }

        $paginator = $this
            ->em
            ->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')
            ->getWith($synthesis, $type, $term, $offset, $limit)
        ;

        $elements = [];
        foreach ($paginator as $element) {
            $elements[] = $element;
        }

        return [
            'elements' => $elements,
            'count' => count($paginator),
        ];
    }

    public function getElementsTreeFromSynthesisByType($synthesis, $type = null, $parentId = null, $depth = null)
    {
        if ($type === null || !in_array($type, self::$types)) {
            throw new NotFoundHttpException();
        }

        $repo = $this->em
            ->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')
        ;

        $tree = $repo->getFormattedTree($synthesis, $type, $parentId, $depth);

        return $tree;
    }

    public function countElementsFromSynthesisByType($synthesis, $type = null): int
    {
        if ($type === null || !in_array($type, self::$types)) {
            throw new NotFoundHttpException();
        }

        return intval($this->em->getRepository('CapcoAppBundle:Synthesis\SynthesisElement')->countWith($synthesis, $type));
    }

    public function createElementInSynthesis(SynthesisElement $element, Synthesis $synthesis, SynthesisUserInterface $user = null)
    {
        $element->setSynthesis($synthesis);
        $element->setDisplayType('folder');
        $element->setAuthor($user);

        $this->em->persist($element);
        $this->em->flush();

        return $element;
    }

    public function updateElementInSynthesis(SynthesisElement $element, Synthesis $synthesis)
    {
        // If we're adding a division to the element, we need to do some stuff
        if ($element->getDivision()) {
            $division = $this->updateDivisionFromElementInSynthesis($element->getDivision(), $element, $synthesis);
        }

        // If we're ignoring the element, all childrens must be ignored
        if (!$element->isPublished()) {
            $element = $this->ignoreElementChildren($element);
        }

        $this->em->persist($element);
        $this->em->flush();

        return $element;
    }

    public function updateDivisionFromElementInSynthesis(SynthesisDivision $division, SynthesisElement $element, Synthesis $synthesis)
    {
        foreach ($division->getElements() as $el) {
            $el->setSynthesis($synthesis);
            $el->setAuthor($element->getAuthor());
            $el->setLink($element->getLink());
            $el->setDisplayType('contribution');

            $el->setLinkedDataClass($element->getLinkedDataClass());
            $el->setLinkedDataId($element->getLinkedDataId());
            $el->setLinkedDataCreation($element->getLinkedDataCreation());
            $el->setLinkedDataLastUpdate($element->getLinkedDataLastUpdate());
            $this->em->persist($el);
        }

        $this->em->persist($division);

        return $division;
    }

    public function ignoreElementChildren(SynthesisElement $element): SynthesisElement
    {
        foreach ($element->getChildren() as $child) {
            $child->setPublished(false);
            $child->setArchived(true);
            $this->ignoreElementChildren($child);
        }

        return $element;
    }

    public function getLogsForElement(SynthesisElement $element)
    {
        return $this->logManager->getLogEntries($element);
    }
}
