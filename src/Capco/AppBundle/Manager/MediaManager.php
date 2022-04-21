<?php

namespace Capco\AppBundle\Manager;

use Sonata\MediaBundle\Entity\MediaManager as SonataMediaManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManager
{
    protected $mediaManager;

    public function __construct(SonataMediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    public function createFileFromUploadedFile(UploadedFile $file, string $context = 'default')
    {
        $media = $this->mediaManager->create();
        $media->setProviderName($this->resolveProviderName($file));
        $media->setBinaryContent($file);
        $media->setContext($context);
        $media->setEnabled(true);
        $this->mediaManager->save($media);

        return $media;
    }

    protected function resolveProviderName(UploadedFile $file): string
    {
        return preg_match('/^image\/[a-z]+/', $file->getClientMimeType())
            ? 'sonata.media.provider.image'
            : 'sonata.media.provider.file';
    }
}
