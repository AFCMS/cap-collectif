<?php

namespace Capco\AppBundle\Normalizer;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Helper\ProjectHelper;
use Capco\AppBundle\Repository\EventRepository;
use Capco\AppBundle\Resolver\ContributionResolver;
use Capco\AppBundle\Resolver\StepResolver;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class ProjectNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;
    private $router;
    private $normalizer;
    private $helper;
    private $mediaExtension;
    private $stepResolver;
    private $contributionResolver;
    private $eventRepository;

    public function __construct(
        UrlGeneratorInterface $router,
        ObjectNormalizer $normalizer,
        ProjectHelper $helper,
        MediaExtension $mediaExtension,
        StepResolver $stepResolver,
        ContributionResolver $contributionResolver,
        EventRepository $eventRepository
    ) {
        $this->router = $router;
        $this->normalizer = $normalizer;
        $this->helper = $helper;
        $this->mediaExtension = $mediaExtension;
        $this->stepResolver = $stepResolver;
        $this->contributionResolver = $contributionResolver;
        $this->eventRepository = $eventRepository;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $groups =
            isset($context['groups']) && \is_array($context['groups']) ? $context['groups'] : [];
        /** @var Project $object */
        $data = $this->normalizer->normalize($object, $format, $context);

        if (\in_array('Elasticsearch', $groups, true)) {
            $data['projectStatus'] = $object->getCurrentStepStatus();
            $data['contributionsCount'] = $this->contributionResolver->countProjectContributions(
                $object
            );
            $data['eventCount'] = $this->eventRepository->countByProject($object->getId());

            return $data;
        }

        $links = [
            'show' => $this->stepResolver->getFirstStepLinkForProject($object),
            'external' => $object->getExternalLink(),
        ];

        if (\in_array('ProjectAdmin', $groups, true)) {
            $links['admin'] = $this->router->generate(
                'admin_capco_app_project_edit',
                ['id' => $object->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $abstractSteps = $this->helper->getAbstractSteps($object);
        $steps = [];
        foreach ($abstractSteps as $abstractStep) {
            $newContext = $context;
            $newContext['groups'] = ['Steps'];
            $steps[] = $this->normalizer->normalize($abstractStep, 'json', $newContext);
        }

        $data['_links'] = $links;
        if ($steps) {
            $data['steps'] = $steps;
        }
        if (\in_array('Projects', $groups, true) && $object->getCover()) {
            try {
                $data['cover'] = [
                    'url' => $this->mediaExtension->path($object->getCover(), 'project'),
                ];
            } catch (RouteNotFoundException $e) {
                // Avoid some SonataMedia problems
            }
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Project;
    }
}
