<?php

namespace Capco\AppBundle\Sluggable;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Gedmo\Mapping\Event\Adapter\ORM as BaseAdapterORM;
use Doctrine\ORM\Query;
use Gedmo\Sluggable\Mapping\Event\SluggableAdapter;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Capco\AppBundle\Entity\Proposal;

/**
 * Doctrine event adapter for ORM adapted
 * for sluggable behavior.
 */
class CapcoORMSluggableAdapter extends BaseAdapterORM implements SluggableAdapter
{
    /**
     * {@inheritdoc}
     *
     * This is overriden by @spyl94
     * We want to avoid queries using "like" and use "equal" when option unique is used.
     */
    public function getSimilarSlugs($object, $meta, array $config, $slug)
    {
        $em = $this->getObjectManager();
        $wrapped = AbstractWrapper::wrap($object, $em);
        $qb = $em->createQueryBuilder();

        if ($object instanceof Proposal) {
            $whereClause = $qb->expr()->eq('rec.' . $config['slug'], ':slug');
            $qb->setParameter('slug', $slug);
        } else {
            $whereClause = $qb->expr()->like('rec.' . $config['slug'], ':slug');
            $qb->setParameter('slug', $slug . '%');
        }

        $qb->select('rec.' . $config['slug'])
            ->from($config['useObjectClass'], 'rec')
            ->where($whereClause);

        // use the unique_base to restrict the uniqueness check
        if ($config['unique'] && isset($config['unique_base'])) {
            $ubase = $wrapped->getPropertyValue($config['unique_base']);
            if (
                \array_key_exists(
                    $config['unique_base'],
                    $wrapped->getMetadata()->getAssociationMappings()
                )
            ) {
                $mapping = $wrapped->getMetadata()->getAssociationMapping($config['unique_base']);
            } else {
                $mapping = false;
            }
            if (($ubase || 0 === $ubase) && !$mapping) {
                $qb->andWhere('rec.' . $config['unique_base'] . ' = :unique_base');
                $qb->setParameter(':unique_base', $ubase);
            } elseif (
                $ubase &&
                $mapping &&
                \in_array($mapping['type'], [
                    ClassMetadataInfo::ONE_TO_ONE,
                    ClassMetadataInfo::MANY_TO_ONE,
                ])
            ) {
                $mappedAlias = 'mapped_' . $config['unique_base'];
                $wrappedUbase = AbstractWrapper::wrap($ubase, $em);
                $qb->innerJoin('rec.' . $config['unique_base'], $mappedAlias);
                foreach (array_keys($mapping['targetToSourceKeyColumns']) as $i => $mappedKey) {
                    $mappedProp = $wrappedUbase->getMetadata()->fieldNames[$mappedKey];
                    $qb->andWhere($qb->expr()->eq($mappedAlias . '.' . $mappedProp, ':assoc' . $i));
                    $qb->setParameter(':assoc' . $i, $wrappedUbase->getPropertyValue($mappedProp));
                }
            } else {
                $qb->andWhere($qb->expr()->isNull('rec.' . $config['unique_base']));
            }
        }

        // include identifiers
        foreach ((array) $wrapped->getIdentifier(false) as $id => $value) {
            if (!$meta->isIdentifier($config['slug'])) {
                $namedId = str_replace('.', '_', $id);
                $qb->andWhere($qb->expr()->neq('rec.' . $id, ':' . $namedId));
                $qb->setParameter($namedId, $value, $meta->getTypeOfField($namedId));
            }
        }
        $q = $qb->getQuery();
        $q->setHydrationMode(Query::HYDRATE_ARRAY);

        return $q->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function replaceRelative($object, array $config, $target, $replacement)
    {
        $em = $this->getObjectManager();
        $qb = $em->createQueryBuilder();
        $qb->update($config['useObjectClass'], 'rec')
            ->set(
                'rec.' . $config['slug'],
                $qb
                    ->expr()
                    ->concat(
                        $qb->expr()->literal($replacement),
                        $qb->expr()->substring('rec.' . $config['slug'], mb_strlen($target))
                    )
            )
            ->where(
                $qb->expr()->like('rec.' . $config['slug'], $qb->expr()->literal($target . '%'))
            );
        // update in memory
        $q = $qb->getQuery();

        return $q->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function replaceInverseRelative($object, array $config, $target, $replacement)
    {
        $em = $this->getObjectManager();
        $qb = $em->createQueryBuilder();
        $qb->update($config['useObjectClass'], 'rec')
            ->set(
                'rec.' . $config['slug'],
                $qb
                    ->expr()
                    ->concat(
                        $qb->expr()->literal($target),
                        $qb
                            ->expr()
                            ->substring('rec.' . $config['slug'], mb_strlen($replacement) + 1)
                    )
            )
            ->where(
                $qb
                    ->expr()
                    ->like('rec.' . $config['slug'], $qb->expr()->literal($replacement . '%'))
            );
        $q = $qb->getQuery();

        return $q->execute();
    }
}
