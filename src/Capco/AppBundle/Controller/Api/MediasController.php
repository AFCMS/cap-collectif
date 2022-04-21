<?php

namespace Capco\AppBundle\Controller\Api;

use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

class MediasController extends FOSRestController
{
    /**
     * @Security("has_role('ROLE_USER')")
     * @Post("/files")
     * @View(statusCode=201, serializerGroups={"Default"})
     */
    public function postMediaAction(Request $request)
    {
        $uploadedMedia = $request->files->get('file');
        $mediaManager = $this->get('capco.media.manager');
        $em = $this->get('doctrine.orm.entity_manager');

        if (!$uploadedMedia) {
            return;
        }

        $media = $mediaManager->createFileFromUploadedFile($uploadedMedia);

        return [
          'name' => $media->getName(),
          'id' => $media->getId(),
          'size' => $this->formatBytes($media->getSize()),
          'url' => '/media' . $this->get('sonata.media.twig.extension')->path($media, 'reference'),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['O', 'Ko', 'Mo', 'Go', 'To'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;

        return number_format($bytes / (1024 ** $power), 1) . ' ' . $units[$power];
    }
}
