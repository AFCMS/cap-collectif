<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Steps\CollectStep;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * ThemeRepository.
 */
class ThemeRepository extends EntityRepository
{
    /**
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getLast($limit = 1, $offset = 0)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('c', 'i')
            ->leftJoin('t.projects', 'c')
            ->leftJoin('t.ideas', 'i')
            ->addOrderBy('t.position', 'ASC')
            ->addOrderBy('t.updatedAt', 'DESC')
        ;

        $query = $qb->getQuery();

        if ($limit) {
            $query->setMaxResults($limit);
        }

        if ($offset) {
            $query->setFirstResult($offset);
        }

        return new Paginator($query);
    }

    /**
     * @param int  $nbByPage
     * @param int  $page
     * @param null $term
     *
     * @return Paginator
     */
    public function getSearchResultsWithProjectsAndIdeas($nbByPage = 8, $page = 1, $term = null)
    {
        if ((int) $page < 1) {
            throw new \InvalidArgumentException(sprintf(
                'The argument "page" cannot be lower than 1 (current value: "%s")',
                $page
            ));
        }

        $qb = $this->getIsEnabledQueryBuilder();
        $qb->addSelect('c, i')
            ->leftJoin('t.projects', 'c')
            ->leftJoin('t.ideas', 'i')
            ->addOrderBy('t.position', 'ASC')
        ;

        $qb
            ->addOrderBy('t.position', 'ASC')
            ->addOrderBy('t.updatedAt', 'DESC')
        ;

        if ($term !== null) {
            $qb->andWhere('t.title LIKE :term')
                ->setParameter('term', '%'.$term.'%')
            ;
        }

        $query = $qb->getQuery();

        if ($nbByPage > 0) {
            $query->setFirstResult(($page - 1) * $nbByPage)
                ->setMaxResults($nbByPage);
        }

        return new Paginator($query);
    }

    /**
     * @param $slug
     *
     * @return mixed
     */
    public function getOneBySlug($slug)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('a', 'am', 'm', 'p', 'i', 'post', 'e')
            ->leftJoin('t.Author', 'a')
            ->leftJoin('a.Media', 'am')
            ->leftJoin('t.Media', 'm')
            ->leftJoin('t.projects', 'p', 'WITH', 'p.isEnabled = :enabled')
            ->leftJoin('t.ideas', 'i', 'WITH', 'i.isEnabled = :enabled')
            ->leftJoin('t.posts', 'post', 'WITH', 'post.isPublished = :enabled')
            ->leftJoin('t.events', 'e', 'WITH', 'e.isEnabled = :enabled')
            ->andWhere('t.slug = :slug')
            ->setParameter('enabled', true)
            ->setParameter('slug', $slug)
            ->orderBy('t.updatedAt', 'DESC')
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getThemesWithProposalsCountForStep(CollectStep $step, $limit = null)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('t.title as name')
            ->addSelect('(
                SELECT COUNT(p.id) as pCount
                FROM CapcoAppBundle:Proposal p
                LEFT JOIN p.proposalForm pf
                LEFT JOIN p.theme pt
                WHERE pf.step = :step
                AND p.enabled = true
                AND pt.id = t.id
                AND p.isTrashed = false
            ) as value')
            ->setParameter('step', $step)
            ->orderBy('value', 'DESC')
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function countAll()
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('COUNT(t.id)')
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.isEnabled = :enabled')
            ->setParameter('enabled', true);
    }
}
