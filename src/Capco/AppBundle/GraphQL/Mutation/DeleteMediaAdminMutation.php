<?php

namespace Capco\AppBundle\GraphQL\Mutation;

use Capco\AppBundle\Manager\MediaManager;
use Capco\MediaBundle\Entity\Media;
use Overblog\GraphQLBundle\Definition\Argument;
use Capco\MediaBundle\Repository\MediaRepository;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class DeleteMediaAdminMutation implements MutationInterface
{
    protected LoggerInterface $logger;
    protected EntityManagerInterface $em;
    protected FormFactoryInterface $formFactory;
    protected MediaRepository $mediaRepository;
    protected MediaManager $mediaManager;

    public function __construct(
        LoggerInterface $logger,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        MediaRepository $mediaRepository,
        MediaManager $mediaManager
    ) {
        $this->logger = $logger;
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->mediaRepository = $mediaRepository;
        $this->mediaManager = $mediaManager;
    }

    public function __invoke(Argument $input): array
    {
        /**
         * @var array
         */
        $mediaIds = $input->offsetGet('ids');
        $deleteMediaIds = [];

        foreach ($mediaIds as $mediaId) {
            $deleteMediaId = $this->findAndDeleteMedia($mediaId);
            if (null == $deleteMediaId) {
                return [
                    'deletedMediaIds' => [],
                    'userErrors' => ['Media with id ' . $mediaId . ' not found!'],
                ];
            }
            array_push($deleteMediaIds, $deleteMediaId);
        }
        $this->em->flush();

        return ['deletedMediaIds' => $deleteMediaIds, 'userErrors' => []];
    }

    public function findAndDeleteMedia(string $mediaId): ?string
    {
        /**
         * @var Media
         */
        $media = $this->mediaRepository->find($mediaId);

        if (!$media) {
            return null;
        }

        $this->em->remove($media);

        return $mediaId;
    }
}
