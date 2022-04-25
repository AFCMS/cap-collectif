<?php

declare(strict_types=1);

namespace Capco\AppBundle\DataFixtures\Processor;

use Capco\MediaBundle\Entity\Media;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Sonata\ClassificationBundle\Model\ContextInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;
use Liip\ImagineBundle\Service\FilterService;

/**
 * This processor generate our medias with liip, to display in development
 * images on the first page load and not the second.
 */
class MediaProcessor implements ProcessorInterface
{
    // TODO: Please investigate why this is slow since SF4.
    const ENABLE_PROCESSOR = true;
    private array $referenceMap = [];
    private EntityManagerInterface $em;
    private FilterService $filterService;
    private string $projectDir;

    public function __construct(
        EntityManagerInterface $em,
        FilterService $filterService,
        string $projectDir
    ) {
        $this->em = $em;
        $this->filterService = $filterService;
        $this->projectDir = $projectDir;
    }

    public function preProcess(string $id, $object): void
    {
        if ($object instanceof Media) {
            $this->referenceMap[$id] = $object->getProviderReference();

            $object->setContext(ContextInterface::DEFAULT_CONTEXT);

            // This will reset the providerReference
            $object->setBinaryContent(
                $this->projectDir . '/fixtures/files/' . $object->getBinaryContent()
            );

            $object->setEnabled(true);
            $object->setProviderName($this->resolveProviderName($object));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postProcess(string $id, $object): void
    {
        if (!self::ENABLE_PROCESSOR) {
            return;
        }
        if ($object instanceof Media) {
            $newProviderReference = $this->referenceMap[$id];
            if (
                !empty($newProviderReference) &&
                $newProviderReference !== $object->getProviderReference()
            ) {
                Process::fromShellCommandline(
                    'mv /var/www/public/media/default/0001/01/' .
                        $object->getProviderReference() .
                        ' /var/www/public/media/default/0001/01/' .
                        $newProviderReference
                )->mustRun();

                // Restore providerReference in fixtures
                $object->setProviderReference($newProviderReference);

                // Let's generate cache for all medias in all formats, to avoid "/resolve" in first URL generation
                $imgFormats = ['default_logo', 'default_avatar', 'default_project'];
                $notNeededExtensions = ['pdf', 'svg', 'csv'];
                foreach ($imgFormats as $format) {
                    $extension = pathinfo($newProviderReference)['extension'];
                    if (!\in_array($extension, $notNeededExtensions, true)) {
                        // Will generate cache file
                        $this->filterService->getUrlOfFilteredImage(
                            'default/0001/01/' . $newProviderReference,
                            $format
                        );
                    }
                }

                // Flush new provider reference
                $this->em->flush();
            }
        }
    }

    protected function resolveProviderName(Media $media): string
    {
        return \in_array(
            pathinfo($media->getBinaryContent(), \PATHINFO_EXTENSION),
            ['png', 'jpeg', 'jpg', 'bmp', 'gif', 'tiff'],
            true
        )
            ? 'sonata.media.provider.image'
            : 'sonata.media.provider.file';
    }
}
