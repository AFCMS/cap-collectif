<?php

namespace Capco\AppBundle\Repository;

use Capco\AppBundle\Entity\District;
use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\ProposalForm;
use Capco\AppBundle\Entity\Status;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\AppBundle\Entity\Theme;
use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Entity\UserType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

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
            ->setParameter('author', $user);

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
                $proposalsWithStep[$collectStep->getId()]['proposals'][] = $result;
            } else {
                $proposalsWithStep[$collectStep->getId()] = [
                    'step' => $collectStep,
                    'proposals' => [$result],
                ];
            }
        }

        return $proposalsWithStep;
    }

    public function countProposalsByFormAndEvaluer(ProposalForm $form, User $user): int
    {
        return $this->qbProposalsByFormAndEvaluer($form, $user)
                  ->select('COUNT(proposal.id)')
                  ->getQuery()
                  ->getSingleScalarResult()
        ;
    }

    public function isViewerAnEvaluer(Proposal $proposal, User $user): bool
    {
        return $this->createQueryBuilder('proposal')
                ->select('COUNT(proposal.id)')
                ->leftJoin('proposal.evaluers', 'group')
                ->leftJoin('group.userGroups', 'userGroup')
                ->andWhere('proposal.id = :id')
                ->andWhere('userGroup.user = :user')
                ->setParameter('id', $proposal->getId())
                ->setParameter('user', $user)
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    public function isViewerAnEvaluerOfAProposalOnForm(ProposalForm $form, User $user): bool
    {
        return $this->createQueryBuilder('proposal')
              ->select('COUNT(proposal.id)')
              ->leftJoin('proposal.evaluers', 'group')
              ->leftJoin('group.userGroups', 'userGroup')
              ->andWhere('proposal.proposalForm = :form')
              ->andWhere('userGroup.user = :user')
              ->setParameter('form', $form)
              ->setParameter('user', $user)
              ->getQuery()
              ->getSingleScalarResult() > 0;
    }

    public function getProposalsByFormAndEvaluer(ProposalForm $form, User $user, int $first, int $offset, string $field, string $direction = 'DESC'): Paginator
    {
        $qb = $this
            ->qbProposalsByFormAndEvaluer($form, $user)
            ->setFirstResult($first)
            ->setMaxResults($offset);

        if ('CREATED_AT' === $field) {
            $qb->addOrderBy('proposal.createdAt', $direction);
        }

        return new Paginator($qb);
    }

    public function countFusionsByProposalForm(ProposalForm $form)
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('COUNT(proposal.id)')
            ->andWhere('proposal.proposalForm = :form')
            ->andWhere('SIZE(proposal.childConnections) > 0')
            ->setParameter('form', $form);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countByUser(User $user): int
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('COUNT(proposal.id)')
            ->andWhere('proposal.author = :author')
            ->setParameter('author', $user);

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
            ->andWhere('selectionStep.id = :stepId')
            ->setParameter('stepId', $step->getId());

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

        if ('old' === $order) {
            $qb->addOrderBy('proposal.createdAt', 'ASC');
        }

        if ('last' === $order) {
            $qb->addOrderBy('proposal.createdAt', 'DESC');
        }

        // Let's see what we do there
        // if ($order === 'popular') {
        //     $qb->addOrderBy('proposal.votesCount', 'DESC');
        // }

        if ('comments' === $order) {
            $qb->addOrderBy('proposal.commentsCount', 'DESC');
        }

        $qb
            ->setFirstResult($first)
            ->setMaxResults($offset);

        return new Paginator($qb);
    }

    public function countPublishedForForm(ProposalForm $form): int
    {
        $qb = $this
            ->getIsEnabledQueryBuilder()
            ->select('COUNT(proposal.id) as proposalsCount')
            ->andWhere('proposal.proposalForm = :proposalForm')
            ->setParameter('proposalForm', $form);

        return (int) $qb->getQuery()->getSingleScalarResult();
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
            ->setParameter('slug', $slug);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getLast(int $limit = null, int $offset = null)
    {
        $limit = $limit ?? 1;
        $offset = $offset ?? 0;
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
            ->execute();
    }

    /**
     * Get last proposals.
     *
     * @param mixed $limit
     * @param mixed $offset
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
            ->setParameter('step', $step)
            ->orderBy('proposal.commentsCount', 'DESC')
            ->addOrderBy('proposal.createdAt', 'DESC')
            ->addGroupBy('proposal.id');

        $qb->setMaxResults($limit);
        $qb->setFirstResult($offset);

        return $qb
            ->getQuery()
            ->execute();
    }

    public function countByAuthorAndProject(User $author, Project $project): int
    {
        $qb = $this->getIsEnabledQueryBuilder()
          ->select('COUNT(DISTINCT proposal)')
          ->leftJoin('proposal.proposalForm', 'form')
          ->andWhere('form.step IN (:steps)')
          ->setParameter('steps', array_map(function ($step) {
              return $step;
          }, $project->getRealSteps()))
          ->andWhere('proposal.author = :author')
          ->setParameter('author', $author)
        ;

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function countByAuthorAndStep(User $author, CollectStep $step): int
    {
        $qb = $this->getIsEnabledQueryBuilder()
          ->select('COUNT(DISTINCT proposal)')
          ->leftJoin('proposal.proposalForm', 'f')
          ->andWhere('proposal.author = :author')
          ->andWhere('f.step =:step')
          ->setParameter('step', $step)
          ->setParameter('author', $author)
      ;

        return $qb->getQuery()->getSingleScalarResult();
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
            ->andWhere('p.isTrashed = true')
            ->setParameter('project', $project)
            ->orderBy('p.trashedAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function getByProposalForm(ProposalForm $proposalForm, bool $asArray = false)
    {
        $qb = $this->createQueryBuilder('proposal')
            ->addSelect('author', 'ut', 'amedia', 'category', 'theme', 'status', 'district', 'responses', 'questions', 'selectionVotes', 'votesaut', 'votesautut', 'proposalEvaluation')
            ->leftJoin('proposal.author', 'author')
            ->leftJoin('author.userType', 'ut')
            ->leftJoin('author.Media', 'amedia')
            ->leftJoin('proposal.theme', 'theme')
            ->leftJoin('proposal.district', 'district')
            ->leftJoin('proposal.category', 'category')
            ->leftJoin('proposal.status', 'status')
            ->leftJoin('proposal.responses', 'responses')
            ->leftJoin('responses.question', 'questions')
            ->leftJoin('proposal.proposalEvaluation', 'proposalEvaluation')
            ->leftJoin('proposal.selectionVotes', 'selectionVotes')
            ->leftJoin('selectionVotes.user', 'votesaut')
            ->leftJoin('votesaut.userType', 'votesautut')
            ->andWhere('proposal.proposalForm = :proposalForm')
            ->setParameter('proposalForm', $proposalForm);

        return $asArray ? $qb->getQuery()->getArrayResult() : $qb->getQuery()->getResult();
    }

    public function getProposalMarkersForCollectStep(CollectStep $step): array
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('author')
            ->leftJoin('proposal.proposalForm', 'proposalForm')
            ->leftJoin('proposal.author', 'author')
            ->andWhere('proposalForm.step = :step')
            ->setParameter('step', $step);
        $qb = $this->getWithFilledAddressQueryBuilder($qb);

        return $qb->getQuery()->getArrayResult();
    }

    public function getProposalMarkersForSelectionStep(SelectionStep $step): array
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('author')
            ->leftJoin('proposal.selections', 'selections')
            ->leftJoin('proposal.author', 'author')
            ->andWhere('selections.selectionStep = :step')
            ->setParameter('step', $step);
        $qb = $this->getWithFilledAddressQueryBuilder($qb);

        return $qb->getQuery()->getArrayResult();
    }

    public function getProposalsWithCostsForStep(CollectStep $step, int $limit = null): array
    {
        $qb = $this->getIsEnabledQueryBuilder()
            ->select('proposal.title as name', 'proposal.estimation as value')
            ->leftJoin('proposal.proposalForm', 'proposalForm')
            ->andWhere('proposalForm.step = :step')
            ->setParameter('step', $step)
            ->orderBy('proposal.estimation', 'DESC');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function getProposalsWithVotesCountForSelectionStep(SelectionStep $step, $limit = null, $themeId = null, $districtId = null, $categoryId = null): array
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
            ->setParameter('stepId', $step->getId());

        if ($themeId) {
            $qb
                ->leftJoin('proposal.theme', 't')
                ->andWhere('t.id = :themeId')
                ->setParameter('themeId', $themeId);
        }

        if ($districtId) {
            $qb
                ->leftJoin('proposal.district', 'd')
                ->andWhere('d.id = :districtId')
                ->setParameter('districtId', $districtId);
        }

        if ($categoryId) {
            $qb
                ->andWhere('proposal.category = :categoryId')
                ->setParameter('categoryId', $categoryId);
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
            ->setParameter('step', $step);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function countForSelectionStep(SelectionStep $step, $themeId = null, $districtId = null, $categoryId = null): int
    {
        $qb = $this->getIsEnabledQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->leftJoin('p.selections', 'selections')
            ->leftJoin('selections.selectionStep', 'ss')
            ->andWhere('ss.id = :stepId')
            ->setParameter('stepId', $step->getId());

        if ($themeId) {
            $qb
                ->leftJoin('p.theme', 't')
                ->andWhere('t.id = :themeId')
                ->setParameter('themeId', $themeId);
        }

        if ($districtId) {
            $qb
                ->leftJoin('p.district', 'd')
                ->andWhere('d.id = :districtId')
                ->setParameter('districtId', $districtId);
        }

        if ($categoryId) {
            $qb
                ->leftJoin('p.category', 'category')
                ->andWhere('category.id = :categoryId')
                ->setParameter('categoryId', $categoryId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function getWithFilledAddressQueryBuilder(QueryBuilder $queryBuilder, string $alias = 'proposal'): QueryBuilder
    {
        return $queryBuilder
            ->andWhere($alias . '.address IS NOT NULL');
    }

    public function findFollowingProposalByUser(string $userid): array
    {
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.followers', 'f')
            ->leftJoin('f.user', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userid);

        return $query->getQuery()->getResult();
    }

//    public function getContributionsSince()
//    {
//        $date = new \DateTime('2015-02-01');
//        $qb = $this->createQueryBuilder('p')
//            ->select('p.title')
//            ->addSelect('COUNT(selectionVote.id) as selectionVotes,COUNT(collectVote.id) as collectVotes,COUNT(comment.id) as comments, COUNT(pStatus.id) as status')
//            ->leftJoin('p.selectionVotes', 'selectionVote')
//            ->leftJoin('p.collectVotes', 'collectVote')
//            ->leftJoin('collectVotes.collectStep', 'sc')
//            ->leftJoin('p.comments', 'comment')
//            ->leftJoin('p.progressSteps', 'pSteps')
//            ->leftJoin('p.status', 'pStatus')
//            ->leftJoin('p.proposalForm', 'f')
//            ->leftJoin('f.step', 's')
//            ->leftJoin('s.projectAbstractStep', 'pas')
//            ->where('selectionVote.createdAt = :since')
//            ->andWhere('collectVote.createdAt = :since')
//            ->andWhere('comment.createdAt = :since')
//            ->andWhere('p.updatedAt = :since')
//            ->andWhere('pStatus.createdAt = :since')
//            ->setParameter('since', new \DateTime());
//
//            $qb->getQuery()->getSQL();
//        ;
//
//    }

    public function getContributionsSince()
    {
        $qb = $this->getIsEnabledQueryBuilder('p')
        ->select('p.title')
        ->addSelect('COUNT(selectionVote.id) as selectionVotes,COUNT(collectVote.id) as collectVotes,COUNT(comment.id) as comments, user.username, user.email')
        ->leftJoin('p.followers', 'followers')
        ->leftJoin('followers.user', 'user')
        ->leftJoin('p.selectionVotes', 'selectionVote')
        ->leftJoin('p.collectVotes', 'collectVote')
        ->leftJoin('p.comments', 'comment')
        ->leftJoin('p.status', 'pStatus')
        ->where('selectionVote.createdAt BETWEEN :from and :to')
        ->orWhere('collectVote.createdAt BETWEEN :from and :to')
        ->orWhere('comment.createdAt BETWEEN :from and :to')
        ->orWhere('p.updatedAt  BETWEEN :from and :to')
        ->setParameter('from', new \DateTime('2018-02-12 00:00:00'))
        ->setParameter('to', new \DateTime('2018-02-12 23:59:59'));

        return $qb->getQuery()->getResult();
    }

    protected function getIsEnabledQueryBuilder(string $alias = 'proposal'): QueryBuilder
    {
        return $this->createQueryBuilder($alias)
            ->andWhere($alias . '.expired = false')
            ->andWhere($alias . '.draft = false')
            ->andWhere($alias . '.isTrashed = false')
            ->andWhere($alias . '.deletedAt IS NULL')
            ->andWhere($alias . '.enabled = true');
    }

    private function qbProposalsByFormAndEvaluer(ProposalForm $form, User $user)
    {
        return $this->createQueryBuilder('proposal')
            ->leftJoin('proposal.evaluers', 'group')
            ->leftJoin('group.userGroups', 'userGroup')
            ->andWhere('proposal.proposalForm = :form')
            ->andWhere('userGroup.user = :user')
            ->setParameter('form', $form)
            ->setParameter('user', $user);
    }
}
