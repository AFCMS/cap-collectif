<?php

namespace Capco\AppBundle\Normalizer;

use Capco\AppBundle\Entity\Synthesis\SynthesisElement;
use Capco\AppBundle\Manager\LogManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;

class SynthesisElementNormalizer implements
    NormalizerInterface,
    SerializerAwareInterface,
    CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;
    private $router;
    private ObjectNormalizer $normalizer;
    private $logManager;

    public function __construct(
        UrlGeneratorInterface $router,
        ObjectNormalizer $normalizer,
        LogManager $logManager
    ) {
        $this->router = $router;
        $this->normalizer = $normalizer;
        $this->logManager = $logManager;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $groups =
            isset($context['groups']) && \is_array($context['groups']) ? $context['groups'] : [];

        if (\in_array('LogDetails', $groups)) {
            $serializedLogs = $this->serializer->serialize(
                $this->logManager->getLogEntries($object),
                'json',
                ['groups' => ['LogDetails']]
            );
            $data['logs'] = $serializedLogs ? json_decode($serializedLogs) : [];
        }

        $data['_links']['self']['href'] = $this->router->generate(
            'get_synthesis_element',
            [
                'synthesis_id' => $object->getSynthesis()->getId(),
                'element_id' => $object->getId(),
            ],
            true
        );
        $data['_links']['history']['href'] = $this->router->generate(
            'get_synthesis_element_history',
            [
                'synthesis_id' => $object->getSynthesis()->getId(),
                'element_id' => $object->getId(),
            ],
            true
        );

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof SynthesisElement;
    }
}
