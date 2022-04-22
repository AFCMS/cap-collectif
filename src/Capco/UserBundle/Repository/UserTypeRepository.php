<?php
namespace Capco\UserBundle\Repository;

use Capco\AppBundle\Entity\Steps\CollectStep;
use Doctrine\ORM\EntityRepository;

/**
 * UserTypeRepository.
 */
class UserTypeRepository extends EntityRepository
{
    public function findAllToArray()
    {
        $qb = $this->createQueryBuilder('ut')->select('ut.name as name, ut.id as id');
        return $qb->getQuery()->getArrayResult();
    }

    public function getUserTypesWithProposalsCountForStep(CollectStep $step, $limit = null)
    {
        $qb = $this->createQueryBuilder('ut')
            ->select('ut.name as name')
            ->addSelect(
                '(
                SELECT COUNT(p.id) as pCount
                FROM CapcoAppBundle:Proposal p
                LEFT JOIN p.proposalForm pf
                LEFT JOIN p.author pa
                LEFT JOIN pa.userType paut
                WHERE pf.step = :step
                AND p.enabled = true
                AND paut.id = ut.id
                AND p.isTrashed = false
            ) as value'
            )
            ->setParameter('step', $step)
            ->orderBy('value', 'DESC');
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function countAll()
    {
        $qb = $this->createQueryBuilder('ut')->select('COUNT(ut.id)');
        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
