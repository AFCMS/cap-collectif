<?php
namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\ProjectVisibilityMode;
use Capco\AppBundle\Entity\Theme;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * EventRepository.
 */
class EventRepository extends EntityRepository
{
    /**
     * Get events depending on theme, project and search term, ordered by startAt criteria.
     *
     * @param null|mixed $archived
     * @param null|mixed $themeSlug
     * @param null|mixed $projectSlug
     * @param null|mixed $term
     * @param null|mixed $limit
     * @param null|mixed $offset
     */
    public function getSearchResults(
        $archived = null,
        $themeSlug = null,
        $projectSlug = null,
        $term = null,
        $limit = null,
        $offset = null
    ) {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('a', 'm', 't', 'c')
            ->leftJoin('e.Author', 'a')
            ->leftJoin('a.media', 'm')
            ->leftJoin('e.themes', 't', 'WITH', 't.isEnabled = :enabled')
            ->leftJoin('e.projects', 'c', 'WITH', 'c.visibility = :visibility')
            ->setParameter('enabled', true)
            ->setParameter('visibility', ProjectVisibilityMode::VISIBILITY_PUBLIC)
            ->orderBy('e.startAt', 'ASC');
        if (null !== $archived) {
            $qb = $this->whereIsArchived($archived, $qb);
        }

        if ($themeSlug && Theme::FILTER_ALL !== $themeSlug) {
            $qb->andWhere('t.slug = :theme')->setParameter('theme', $themeSlug);
        }

        if (null !== $projectSlug && Project::FILTER_ALL !== $projectSlug) {
            $qb->andWhere('c.slug = :project')->setParameter('project', $projectSlug);
        }

        if ($term) {
            $qb->andWhere('e.title LIKE :term')->setParameter('term', '%' . $term . '%');
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return new Paginator($qb, $fetchJoin = true);
    }

    public function countAllByUser(User $user): int
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->select('count(DISTINCT e)')
            ->andWhere('e.Author = :user')
            ->setParameter('user', $user);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findAllByUser(User $user): array
    {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere('e.Author = :user')->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    /**
     * Count events depending on theme, project and search term.
     *
     * @param $archived
     * @param null $themeSlug
     * @param null $projectSlug
     * @param null $term
     *
     * @return array
     */
    public function countSearchResults(
        $archived = null,
        $themeSlug = null,
        $projectSlug = null,
        $term = null
    ) {
        $qb = $this->getIsEnabledQueryBuilder()->select('COUNT(e.id)');
        if (null !== $archived) {
            $qb = $this->whereIsArchived($archived, $qb);
        }

        if (null !== $themeSlug && Theme::FILTER_ALL !== $themeSlug) {
            $qb
                ->innerJoin('e.themes', 't', 'WITH', 't.isEnabled = :tEnabled')
                ->andWhere('t.slug = :theme')
                ->setParameter('tEnabled', true)
                ->setParameter('theme', $themeSlug);
        }

        if (null !== $projectSlug && Project::FILTER_ALL !== $projectSlug) {
            $qb
                ->innerJoin('e.projects', 'c', 'WITH', 'c.visibility = :visibility')
                ->andWhere('c.slug = :project')
                ->setParameter('visibility', ProjectVisibilityMode::VISIBILITY_PUBLIC)
                ->setParameter('project', $projectSlug);
        }

        if (null !== $term) {
            $qb->andWhere('e.title LIKE :term')->setParameter('term', '%' . $term . '%');
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get one event by slug.
     *
     * @param $slug
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return mixed
     */
    public function getOne($slug)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('a', 't', 'media', 'registration', 'c')
            ->leftJoin('e.Author', 'a')
            ->leftJoin('e.media', 'media')
            ->leftJoin('e.themes', 't', 'WITH', 't.isEnabled = :tEnabled')
            ->leftJoin('e.projects', 'c', 'WITH', 'c.visibility = :visibility')
            ->leftJoin('e.registrations', 'registration', 'WITH', 'registration.confirmed = true')
            ->andWhere('e.slug = :slug')
            ->setParameter('tEnabled', true)
            ->setParameter('visibility', ProjectVisibilityMode::VISIBILITY_PUBLIC)
            ->setParameter('slug', $slug)
            ->orderBy('e.startAt', 'ASC')
            ->addOrderBy('registration.updatedAt', 'DESC');
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Get last future events.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return mixed
     */
    public function getLast($limit = 1, $offset = 0)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('a', 't', 'media', 'c')
            ->leftJoin('e.Author', 'a')
            ->leftJoin('e.themes', 't')
            ->leftJoin('e.projects', 'c')
            ->leftJoin('e.media', 'media')
            ->orderBy('e.startAt', 'ASC');

        $qb = $this->whereIsFuture($qb);

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return new Paginator($qb, $fetchJoin = true);
    }

    /**
     * Get Events by theme.
     *
     * @param theme
     * @param mixed $theme
     *
     * @return mixed
     */
    public function getByTheme($theme)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('a', 'media', 't', 'c')
            ->leftJoin('e.themes', 't')
            ->leftJoin('e.projects', 'c')
            ->leftJoin('e.Author', 'a')
            ->leftJoin('e.media', 'media')
            ->andWhere('t.id = :theme')
            ->setParameter('theme', $theme)
            ->orderBy('e.startAt', 'ASC');

        return $qb->getQuery()->execute();
    }

    protected function getIsEnabledQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.isEnabled = :isEnabled')
            ->setParameter('isEnabled', true);
    }

    protected function whereIsFuture(QueryBuilder $qb, string $alias = 'e'): QueryBuilder
    {
        return $qb
            ->andWhere(
                '(' .
                    $alias .
                    '.endAt IS NULL AND :now <= ' .
                    $alias .
                    '.startAt) OR (' .
                    $alias .
                    '.endAt IS NOT NULL AND :now < ' .
                    $alias .
                    '.endAt)'
            )
            ->setParameter('now', new \DateTime());
    }

    protected function whereIsArchived(
        bool $archived,
        QueryBuilder $qb,
        string $alias = 'e'
    ): QueryBuilder {
        if ($archived) {
            return $qb
                ->andWhere(
                    '(' .
                        $alias .
                        '.endAt IS NOT NULL AND :now > ' .
                        $alias .
                        '.endAt) OR (' .
                        $alias .
                        '.endAt IS NULL AND DATE(:now) > DATE(' .
                        $alias .
                        '.startAt))'
                )
                ->setParameter('now', new \DateTime())
                ->orderBy($alias . '.startAt', 'DESC');
        }

        return $qb
            ->andWhere(
                '(' .
                    $alias .
                    '.endAt IS NULL AND DATE(:now) <= DATE(' .
                    $alias .
                    '.startAt)) OR (' .
                    $alias .
                    '.endAt IS NOT NULL AND :now < ' .
                    $alias .
                    '.endAt)'
            )
            ->setParameter('now', new \DateTime());
    }
}
