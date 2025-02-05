<?php

namespace Capco\AppBundle\Repository\Organization;

use Capco\AppBundle\Entity\Organization\OrganizationTranslation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrganizationTranslation|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganizationTranslation|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganizationTranslation[]    findAll()
 * @method OrganizationTranslation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationTranslationRepository extends EntityRepository
{
}
