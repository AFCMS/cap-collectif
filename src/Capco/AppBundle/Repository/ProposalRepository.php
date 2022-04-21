<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Entity\Theme;
use Capco\AppBundle\Entity\Status;
use Capco\AppBundle\Entity\District;
use Capco\UserBundle\Entity\UserType;
use Capco\AppBundle\Entity\Project;
use Capco\UserBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;

class ProposalRepository extends EntityRepository
{
    public function getProposalsGroupedByCollectSteps(User $user, bool $onlyVisible = false): array
    {
        $qb = $this->getIsEnabledQueryBuilder()
        ->addSelect('district', 'status', 'theme', 'form', 'step')
        ->leftJoin('proposal.district', 'district')
        ->leftJoin('proposal.status', 'status')
        ->leftJoin('proposal.theme', 'theme')
        ->leftJoin('proposal.proposalForm', 'form')
        ->leftJoin('form.step', 'step')
        ->andWhere('proposal.author = :author')
        ->setParameter('author', $user)
    ;

        $results = $qb->getQuery()->getResult();

        if ($onlyVisible) {
            $results = array_filter($results, function ($proposal) {
                return $proposal->isVisible();
            });
        }

        $proposalsWithStep = [];
        foreach ($results as $result) {
            $collectStep = $result->getProposalForm()->getStep();
            if (array_key_exists($collectStep->getId(), $proposalsWithStep)) {
                array_push($proposalsWithStep[$collectStep->getId()]['proposals'], $result);
            } else {
                $proposalsWithStep[$collectStep->getId()] = [
              'step' => $collectStep,
              'proposals' => [$result],
          ];
            }
        }

        return $proposalsWithStep;
    }

    public function countFusionsByProposalForm(ProposalForm $form)
    {
        $qb = $this->getIsEnabledQueryBuilder()
              ->select('COUNT(proposal.id)')
              ->andWhere('proposal.proposalForm = :form')
              ->andWhere('SIZE(proposal.childConnections) > 0')
              ->setParameter('form', $form)
          ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countByUser(User $user): int
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('COUNT(proposal.id)')
            ->andWhere('proposal.author = :author')
            ->setParameter('author', $user)
        ;

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getPublishedBySelectionStep(
      SelectionStep $step,
      int $first = 0,
      int $offset = 100,
      string $order = 'last',
      Theme $theme = null,
      Status $status = null,
      District $district = null,
      UserType $type = null
    ) {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('author', 'amedia', 'theme', 'status', 'district')
            ->leftJoin('proposal.author', 'author')
            ->leftJoin('author.Media', 'amedia')
            ->leftJoin('proposal.theme', 'theme')
            ->leftJoin('proposal.district', 'district')
            ->leftJoin('proposal.status', 'status')
            ->leftJoin('proposal.selections', 'selections')
            ->leftJoin('selections.selectionStep', 'selectionStep')
            ->andWhere('proposal.isTrashed = :notTrashed')
            ->andWhere('selectionStep.id = :stepId')
            ->setParameter('notTrashed', false)
            ->setParameter('stepId', $step->getId())
        ;

        if ($theme) {
            $qb->andWhere('proposal.theme = :theme')
                ->setParameter('theme', $theme);
        }

        if ($status) {
            $qb->andWhere('proposal.status = :status')
                ->setParameter('status', $status);
        }

        if ($district) {
            $qb->andWhere('proposal.district = :district')
                ->setParameter('district', $district);
        }

        if ($type) {
            $qb->andWhere('author.userType = :type')
                ->setParameter('type', $type);
        }

        if ($order === 'old') {
            $qb->addOrderBy('proposal.createdAt', 'ASC');
        }

        if ($order === 'last') {
            $qb->addOrderBy('proposal.createdAt', 'DESC');
        }

        // Let's see what we do there
        // if ($order === 'popular') {
        //     $qb->addOrderBy('proposal.votesCount', 'DESC');
        // }

        if ($order === 'comments') {
            $qb->addOrderBy('proposal.commentsCount', 'DESC');
        }

        $qb
            ->setFirstResult($first)
            ->setMaxResults($offset)
        ;

        return new Paginator($qb);
    }

    public function countPublishedForForm(ProposalForm $form): int
    {
        $qb = $this
            ->getIsEnabledQueryBuilder()
            ->select('COUNT(proposal.id) as proposalsCount')
            ->andWhere('proposal.isTrashed = false')
            ->andWhere('proposal.proposalForm = :proposalForm')
            ->setParameter('proposalForm', $form)
        ;

        return intval($qb->getQuery()->getSingleScalarResult());
    }

    public function countPublishedForSelectionStep(SelectionStep $step): int
    {
        $qb = $this
            ->getIsEnabledQueryBuilder()
            ->select('COUNT(proposal.id) as proposalsCount')
            ->leftJoin('proposal.selections', 'selections')
            ->leftJoin('selections.selectionStep', 'selectionStep')
            ->andWhere('proposal.isTrashed = false')
            ->andWhere('selectionStep.id = :stepId')
            ->setParameter('stepId', $step->getId())
        ;

        return intval($qb->getQuery()->getSingleScalarResult());
    }

    protected function getIsEnabledQueryBuilder(string $alias = 'proposal'): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->andWhere($alias.'.enabled = true')
            ->andWhere($alias.'.expired = false')
          ;
    }

    public function getOne(string $slug)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('author', 'amedia', 'theme', 'status', 'district', 'responses', 'questions')
            ->leftJoin('proposal.author', 'author')
            ->leftJoin('author.Media', 'amedia')
            ->leftJoin('proposal.theme', 'theme')
            ->leftJoin('proposal.status', 'status')
            ->leftJoin('proposal.district', 'district')
            ->leftJoin('proposal.responses', 'responses')
            ->leftJoin('responses.question', 'questions')
            ->andWhere('proposal.slug = :slug')
            ->setParameter('slug', $slug)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getLast($limit = 1, $offset = 0)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('author', 'amedia', 'theme', 'status', 'district')
            ->leftJoin('proposal.author', 'author')
            ->leftJoin('author.Media', 'amedia')
            ->leftJoin('proposal.theme', 'theme')
            ->leftJoin('proposal.status', 'status')
            ->leftJoin('proposal.district', 'district')
            ->andWhere('proposal.isTrashed = false')
            ->orderBy('proposal.commentsCount', 'DESC')
            ->addOrderBy('proposal.createdAt', 'DESC')
            ->addGroupBy('proposal.id');

        if ($limit) {
            $qb->setMaxResults($limit);
            $qb->setFirstResult($offset);
        }

        return $qb
            ->getQuery()
            ->execute()
        ;
    }

    /**
     * Get last proposals.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return mixed
     */
    public function getLastByStep($limit, $offset, CollectStep $step)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('author', 'amedia', 'theme', 'status', 'district')
            ->leftJoin('proposal.author', 'author')
            ->leftJoin('author.Media', 'amedia')
            ->leftJoin('proposal.theme', 'theme')
            ->leftJoin('proposal.status', 'status')
            ->leftJoin('proposal.district', 'district')
            ->leftJoin('proposal.proposalForm', 'f')
            ->andWhere('f.step = :step')
            ->andWhere('proposal.isTrashed = :notTrashed')
            ->setParameter('notTrashed', false)
            ->setParameter('step', $step)
            ->orderBy('proposal.commentsCount', 'DESC')
            ->addOrderBy('proposal.createdAt', 'DESC')
            ->addGroupBy('proposal.id');

        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb
            ->getQuery()
            ->execute()
        ;
    }

    public function getTrashedOrUnpublishedByProject(Project $project)
    {
        $qb = $this->createQueryBuilder('p')
            ->addSelect('f', 's', 'aut', 'm', 'theme', 'status', 'district')
            ->leftJoin('p.author', 'aut')
            ->leftJoin('aut.Media', 'm')
            ->leftJoin('p.theme', 'theme')
            ->leftJoin('p.status', 'status')
            ->leftJoin('p.district', 'district')
            ->leftJoin('p.proposalForm', 'f')
            ->leftJoin('f.step', 's')
            ->leftJoin('s.projectAbstractStep', 'pas')
            ->andWhere('pas.project = :project')
            ->andWhere('p.isTrashed = :trashed OR p.enabled = :disabled')
            ->setParameter('project', $project)
            ->setParameter('trashed', true)
            ->setParameter('disabled', false)
            ->orderBy('p.trashedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getEnabledByProposalForm(ProposalForm $proposalForm, bool $asArray = false)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('author', 'ut', 'amedia', 'category', 'theme', 'status', 'district', 'responses', 'questions', 'selectionVotes', 'votesaut', 'votesautut', 'answer', 'answeraut')
            ->leftJoin('proposal.author', 'author')
            ->leftJoin('author.userType', 'ut')
            ->leftJoin('author.Media', 'amedia')
            ->leftJoin('proposal.theme', 'theme')
            ->leftJoin('proposal.district', 'district')
            ->leftJoin('proposal.category', 'category')
            ->leftJoin('proposal.status', 'status')
            ->leftJoin('proposal.responses', 'responses')
            ->leftJoin('responses.question', 'questions')
            ->leftJoin('proposal.selectionVotes', 'selectionVotes')
            ->leftJoin('selectionVotes.user', 'votesaut')
            ->leftJoin('votesaut.userType', 'votesautut')
            ->leftJoin('proposal.answer', 'answer')
            ->leftJoin('answer.author', 'answeraut')
            ->andWhere('proposal.proposalForm = :proposalForm')
            ->setParameter('proposalForm', $proposalForm)
        ;

        return $asArray ? $qb->getQuery()->getArrayResult() : $qb->getQuery()->getResult();
    }

    public function getProposalsWithCostsForStep(CollectStep $step, int $limit = null): array
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('proposal.title as name', 'proposal.estimation as value')
            ->leftJoin('proposal.proposalForm', 'proposalForm')
            ->andWhere('proposalForm.step = :step')
            ->setParameter('step', $step)
            ->orderBy('proposal.estimation', 'DESC')
        ;

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function getProposalsWithVotesCountForSelectionStep(SelectionStep $step, int $limit = null, int $themeId = null, int $districtId = null): array
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('proposal.title as name')
            ->addSelect('(
                SELECT COUNT(pv.id) as pvCount
                FROM CapcoAppBundle:ProposalSelectionVote pv
                LEFT JOIN pv.proposal as pvp
                LEFT JOIN pv.selectionStep ss
                WHERE ss.id = :stepId
                AND pvp.id = proposal.id
            ) as value')
            ->leftJoin('proposal.selections', 'selections')
            ->leftJoin('selections.selectionStep', 'selectionStep')
            ->andWhere('selectionStep.id = :stepId')
            ->setParameter('stepId', $step->getId())
        ;

        if ($themeId) {
            $qb
                ->leftJoin('proposal.theme', 't')
                ->andWhere('t.id = :themeId')
                ->setParameter('themeId', $themeId)
            ;
        }

        if ($districtId) {
            $qb
                ->leftJoin('proposal.district', 'd')
                ->andWhere('d.id = :districtId')
                ->setParameter('districtId', $districtId)
            ;
        }

        $qb->orderBy('value', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function getTotalCostForStep(CollectStep $step): int
    {
        $qb = $this->getIsEnabledQueryBuilder('p')
            ->select('SUM(p.estimation)')
            ->leftJoin('p.proposalForm', 'pf')
            ->andWhere('pf.step = :step')
            ->setParameter('step', $step)
        ;

        return intval($qb->getQuery()->getSingleScalarResult());
    }

    public function countForSelectionStep(SelectionStep $step, int $themeId = null, int $districtId = null): int
    {
        $qb = $this->getIsEnabledQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->leftJoin('p.selections', 'selections')
            ->leftJoin('selections.selectionStep', 'ss')
            ->andWhere('ss.id = :stepId')
            ->setParameter('stepId', $step->getId())
        ;

        if ($themeId) {
            $qb
                ->leftJoin('p.theme', 't')
                ->andWhere('t.id = :themeId')
                ->setParameter('themeId', $themeId)
            ;
        }

        if ($districtId) {
            $qb
                ->leftJoin('p.district', 'd')
                ->andWhere('d.id = :districtId')
                ->setParameter('districtId', $districtId)
            ;
        }

        return intval($qb->getQuery()->getSingleScalarResult());
    }
}
