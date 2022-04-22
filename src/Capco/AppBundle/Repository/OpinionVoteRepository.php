<?php
namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\OpinionVote;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\ConsultationStep;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class OpinionVoteRepository extends EntityRepository
{
    public function countByAuthorAndProject(User $author, Project $project): int
    {
        $qb = $this->getPublishedQueryBuilder()
            ->select('COUNT (DISTINCT v)')
            ->leftJoin('v.opinion', 'o')
            ->andWhere('o.step IN (:steps)')
            ->andWhere('o.published = 1')
            ->andWhere('v.user = :author')
            ->setParameter(
                'steps',
                array_map(function ($step) {
                    return $step;
                }, $project->getRealSteps())
            )
            ->setParameter('author', $author);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getByAuthorAndOpinion(User $author, Opinion $opinion): ?OpinionVote
    {
        $qb = $this->createQueryBuilder('v')
            ->andWhere('v.opinion = :opinion')
            ->andWhere('v.user = :author')
            ->setParameter('author', $author)
            ->setParameter('opinion', $opinion);
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function countByAuthorAndStep(User $author, ConsultationStep $step): int
    {
        $qb = $this->getPublishedQueryBuilder()
            ->select('COUNT (DISTINCT v)')
            ->leftJoin('v.opinion', 'o')
            ->andWhere('o.step = :step')
            ->andWhere('o.published = 1')
            ->andWhere('v.user = :author')
            ->setParameter('step', $step)
            ->setParameter('author', $author);
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getEnabledByConsultationStep(ConsultationStep $step)
    {
        $qb = $this->getPublishedQueryBuilder()
            ->addSelect('u', 'ut', 'o')
            ->leftJoin('v.user', 'u')
            ->leftJoin('u.userType', 'ut')
            ->leftJoin('v.opinion', 'o')
            ->andWhere('o.step = :step')
            ->andWhere('o.published = 1')
            ->setParameter('step', $step)
            ->orderBy('v.updatedAt', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getByContributionQB(Opinion $votable)
    {
        $qb = $this->getPublishedQueryBuilder();
        $qb->andWhere('v.opinion = :opinion')->setParameter('opinion', $votable->getId());
        return $qb;
    }

    public function getByContributionAndValueQB(Opinion $votable, int $value)
    {
        $qb = $this->getPublishedQueryBuilder();
        $qb
            ->andWhere('v.opinion = :opinion')
            ->setParameter('opinion', $votable->getId())
            ->andWhere('v.value = :value')
            ->setParameter('value', $value);
        return $qb;
    }

    public function countByContribution(Opinion $votable): int
    {
        $qb = $this->getByContributionQB($votable);
        $qb->select('COUNT(v.id)');
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countByContributionAndValue(Opinion $votable, int $value): int
    {
        $qb = $this->getByContributionAndValueQB($votable, $value);
        $qb->select('COUNT(v.id)');
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getByContributionAndValue(
        Opinion $votable,
        int $value,
        ?int $limit,
        ?int $first,
        string $field,
        string $direction
    ): Paginator {
        $qb = $this->getByContributionAndValueQB($votable, $value);

        if ('CREATED_AT' === $field) {
            $qb->addOrderBy('v.createdAt', $direction);
        }

        $qb->setFirstResult($first)->setMaxResults($limit);
        return new Paginator($qb);
    }

    public function getByContribution(
        Opinion $votable,
        ?int $limit,
        ?int $first,
        string $field,
        string $direction
    ): Paginator {
        $qb = $this->getByContributionQB($votable);

        if ('CREATED_AT' === $field) {
            $qb->addOrderBy('v.createdAt', $direction);
        }

        $qb->setFirstResult($first)->setMaxResults($limit);
        return new Paginator($qb);
    }

    public function getByOpinion(
        string $opinionId,
        bool $asArray = false,
        int $limit = -1,
        int $offset = 0
    ) {
        $qb = $this->getPublishedQueryBuilder();

        if ($asArray) {
            $qb->addSelect('u as author')->leftJoin('v.user', 'u');
        }

        $qb
            ->addSelect('o')
            ->leftJoin('v.opinion', 'o')
            ->andWhere('v.opinion = :opinion')
            ->setParameter('opinion', $opinionId)
            ->orderBy('v.updatedAt', 'ASC');
        if ($limit > 0) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        return $asArray ? $qb->getQuery()->getArrayResult() : $qb->getQuery()->getResult();
    }

    public function getVotesCountByOpinion(Opinion $opinion)
    {
        $qb = $this->createQueryBuilder('ov');

        $qb
            ->select('count(ov.id)')
            ->where('ov.opinion = :opinion')
            ->setParameter('opinion', $opinion);
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    protected function getPublishedQueryBuilder()
    {
        return $this->createQueryBuilder('v')->andWhere('v.published = true');
    }
}
