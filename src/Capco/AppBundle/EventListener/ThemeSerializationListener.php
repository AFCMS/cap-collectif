<?php

namespace Capco\AppBundle\EventListener;

use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use Symfony\Component\Routing\RouterInterface;

class ThemeSerializationListener implements EventSubscriberInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => 'serializer.post_serialize',
                'class' => 'Capco\AppBundle\Entity\Theme',
                'method' => 'onPostTheme',
            ],
        ];
    }

    public function onPostTheme(ObjectEvent $event)
    {
        $theme = $event->getObject();

        $event->getVisitor()->addData(
            '_links', [
                'show' => $this->router->generate('app_theme_show', [
                    'slug' => $theme->getSlug(),
                ], true),
            ]
        );
    }
}
