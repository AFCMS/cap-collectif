<?php

namespace Capco\AppBundle\Publishable;

use Capco\AppBundle\Entity\Interfaces\DraftableInterface;
use Capco\AppBundle\Model\Publishable;
use Capco\UserBundle\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class DoctrineListener implements EventSubscriber
{
    public function getSubscribedEvents(): array
    {
        return ['prePersist'];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Publishable) {
            self::setPublishedStatus($entity);
        }
    }

    public static function setPublishedStatus(Publishable $entity)
    {
        /** @var User $author */
        $author = $entity->getAuthor();
        if (!$author || $author->isEmailConfirmed()) {
            if ($entity instanceof DraftableInterface && $entity->isDraft()) {
                return;
            }
            $entity->setPublishedAt(new \DateTime());
        }
    }
}
