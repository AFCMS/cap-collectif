<?php

namespace Capco\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * SiteParameterRepository.
 */
class SiteColorRepository extends EntityRepository
{
    public function getValuesIfEnabled()
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from($this->getClassName(), 'p', 'p.keyname')
            ->select('p.value', 'p.keyname')
            ->andWhere('p.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->groupBy('p.keyname')
            ->getQuery()
            ->getResult();
    }
}
