<?php

namespace Capco\AppBundle\Repository;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityRepository;

/**
 * IdeaCommentRepository.
 */
class IdeaCommentRepository extends EntityRepository
{
    public function getEnabledByIdea($idea, $offset = 0, $limit = 10, $filter = 'last')
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('aut', 'm', 'v', 'i', 'r', 'ans')
            ->leftJoin('c.Author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('c.votes', 'v')
            ->leftJoin('c.Reports', 'r')
            ->leftJoin('c.Idea', 'i')
            ->leftJoin('c.answers', 'ans', 'WITH', 'ans.isEnabled = :enabled AND ans.isTrashed = :notTrashed')
            ->andWhere('c.Idea = :idea')
            ->andWhere('c.parent is NULL')
            ->andWhere('c.isTrashed = :notTrashed')
            ->setParameter('enabled', true)
            ->setParameter('notTrashed', false)
            ->setParameter('idea', $idea)
            ->orderBy('c.pinned', 'DESC')
        ;

        if ($filter === 'old') {
            $qb->addOrderBy('c.updatedAt', 'ASC');
        }

        if ($filter === 'last') {
            $qb->addOrderBy('c.updatedAt', 'DESC');
        }

        if ($filter === 'popular') {
            $qb->addOrderBy('c.votesCount', 'DESC');
        }

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    public function countCommentsAndAnswersEnabledByIdea($idea)
    {
        $qb = $this->getIsEnabledQueryBuilder()
                   ->select('count(c.id)')
                   ->andWhere('c.Idea = :idea')
                   ->andWhere('c.isTrashed = false')
                   ->setParameter('idea', $idea)
                ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true);
    }
}
