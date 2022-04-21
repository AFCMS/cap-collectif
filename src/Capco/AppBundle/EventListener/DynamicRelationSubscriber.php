<?php

namespace Capco\AppBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;

class DynamicRelationSubscriber implements EventSubscriber
{
    private $traits;

    public function __construct(array $traits)
    {
        $this->traits = $traits;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
        ];
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $metadata = $eventArgs->getClassMetadata();
        $em = $eventArgs->getEntityManager();
        $namingStrategy = $em
            ->getConfiguration()
            ->getNamingStrategy()
        ;

        foreach ($this->traits as $trait => $params) {
            switch ($trait) {
                case 'selflinkable':
                    if (count(array_intersect(class_implements($metadata->getName()), $params['interfaces'])) > 0) {
                        $metadata->mapManyToMany([
                            'targetEntity' => $metadata->getName(),
                            'fieldName' => 'childConnections',
                            'cascade' => ['persist'],
                            'inversedBy' => 'parentConnections',
                            'joinTable' => [
                                'name' => $metadata->getTableName().'_relation',
                            ],
                        ]);
                        $metadata->mapManyToMany([
                            'targetEntity' => $metadata->getName(),
                            'fieldName' => 'parentConnections',
                            'cascade' => ['persist'],
                            'mappedBy' => 'childConnections',
                        ]);
                    }
                break;
                case 'votable':
                    if (count(array_intersect(class_implements($metadata->getName()), $params['interfaces'])) > 0) {
                        if (array_key_exists('votes', $metadata->getReflectionProperties())) {
                            break;
                        }

                        $fieldName = lcfirst(substr($metadata->getName(), strrpos($metadata->getName(), '\\') + 1));

                        $metadata->mapOneToMany([
                            'targetEntity' => $metadata->getName().'Vote',
                            'fieldName' => 'votes',
                            'cascade' => ['persist', 'remove'],
                            'orphanRemoval' => true,
                            'mappedBy' => $fieldName,
                        ]);
                    }
                break;
            }
        }
    }
}
