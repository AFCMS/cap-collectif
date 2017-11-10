<?php

namespace Capco\UserBundle\Repository;

use Capco\AppBundle\Entity\Project;
use Capco\AppBundle\Entity\Steps\CollectStep;
use Capco\AppBundle\Entity\Steps\ConsultationStep;
use Capco\AppBundle\Entity\Steps\QuestionnaireStep;
use Capco\AppBundle\Entity\Steps\SelectionStep;
use Capco\UserBundle\Entity\User;
use Capco\UserBundle\Entity\UserType;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * UserRepository.
 */
class UserRepository extends EntityRepository
{
    public function getRegisteredContributorCount(): int
    {
        $qb = $this->createQueryBuilder('u');

        $qbOpinion = $this->createQueryBuilder('userOpinion');
        $qbOpinion->select('userOpinion.id')->innerJoin('userOpinion.opinions', 'opinion', 'WITH', 'opinion.isEnabled = 1 AND opinion.expired = 0');

        $qbSource = $this->createQueryBuilder('userSource');
        $qbSource->select('userSource.id')->innerJoin('userSource.sources', 'source', 'WITH', 'source.isEnabled = 1 AND source.expired = 0');

        $qbComment = $this->createQueryBuilder('userComment');
        $qbComment->select('userComment.id')->innerJoin('userComment.comments', 'comment', 'WITH', 'comment.isEnabled = 1 AND comment.expired = 0');

        $qbVote = $this->createQueryBuilder('userVote');
        $qbVote->select('userVote.id')->innerJoin('userVote.votes', 'vote', 'WITH', 'vote.expired = 0');

        $qbArgument = $this->createQueryBuilder('userArgument');
        $qbArgument->select('userArgument.id')->innerJoin('userArgument.arguments', 'argument', 'WITH', 'argument.isEnabled = 1 AND argument.expired = 0');

        $qbOpinionVersions = $this->createQueryBuilder('userOpinionVersions');
        $qbOpinionVersions->select('userOpinionVersions.id')->innerJoin('userOpinionVersions.opinionVersions', 'version', 'WITH', 'version.enabled = 1 AND version.expired = 0');

        $qbProposal = $this->createQueryBuilder('userProposal');
        $qbProposal->select('userProposal.id')->innerJoin('userProposal.proposals', 'proposal', 'WITH', 'proposal.enabled = 1 AND proposal.expired = 0');

        $qbReply = $this->createQueryBuilder('userReply');
        $qbReply->select('userReply.id')->innerJoin('userReply.replies', 'reply', 'WITH', 'reply.enabled = 1 AND reply.expired = 0');

        $qbIdea = $this->createQueryBuilder('userIdea');
        $qbIdea->select('userIdea.id')->innerJoin('userIdea.ideas', 'idea', 'WITH', 'idea.expired = 0');

        $qb->select('count(DISTINCT u.id)')
      ->orWhere(
        $qb->expr()->in(
          'u.id',
          $qbIdea->getDQL()
          )
        )
        ->orWhere(
          $qb->expr()->in(
            'u.id',
            $qbReply->getDQL()
            )
          )
          ->orWhere(
            $qb->expr()->in(
              'u.id',
              $qbOpinion->getDQL()
            )
          )
          ->orWhere(
            $qb->expr()->in(
              'u.id',
              $qbArgument->getDQL()
            )
          )
          ->orWhere(
            $qb->expr()->in(
              'u.id',
              $qbProposal->getDQL()
            )
          )
          ->orWhere(
            $qb->expr()->in(
              'u.id',
              $qbOpinionVersions->getDQL()
            )
          )
          ->orWhere(
            $qb->expr()->in(
              'u.id',
              $qbVote->getDQL()
              )
          )
          ->orWhere(
            $qb->expr()->in(
              'u.id',
              $qbComment->getDQL()
              )
          )
          ->orWhere(
            $qb->expr()->in(
              'u.id',
              $qbSource->getDQL()
              )
          )
      ;

        return $qb
                ->getQuery()
                ->useQueryCache(true)
                ->getSingleScalarResult();
    }

    public function findProjectSourceContributorsWithCount(Project $project)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
         'SELECT u.id, count(distinct s) as sources_count
          FROM CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:Source s WITH s.Author = u
          LEFT JOIN CapcoAppBundle:OpinionVersion ov WITH s.opinionVersion = ov
          LEFT JOIN CapcoAppBundle:Opinion o WITH s.Opinion = o
          LEFT JOIN o.step ostep
          LEFT JOIN ostep.projectAbstractStep opas
          LEFT JOIN CapcoAppBundle:Opinion ovo WITH ov.parent = ovo
          LEFT JOIN ovo.step ovostep
          LEFT JOIN ovostep.projectAbstractStep ovopas
          WHERE s.isEnabled = 1 AND s.expired = 0 AND (
            (s.Opinion IS NOT NULL AND o.isEnabled = 1 AND o.expired = 0 AND opas.project = :project)
            OR
            (s.opinionVersion IS NOT NULL AND ov.enabled = 1 AND ov.expired = 0 AND ovo.isEnabled = 1 AND ovo.expired = 0 AND ovopas.project = :project)
          )
          GROUP BY u.id
        ')
            ->setParameter('project', $project);

        return $query->getResult();
    }

    public function findProjectArgumentContributorsWithCount(Project $project)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
          'SELECT u.id, count(distinct a) as arguments_count
          from CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:Argument a WITH a.Author = u
          LEFT JOIN CapcoAppBundle:OpinionVersion ov WITH a.opinionVersion = ov
          LEFT JOIN CapcoAppBundle:Opinion o WITH a.opinion = o
          LEFT JOIN o.step ostep
          LEFT JOIN ostep.projectAbstractStep opas
          LEFT JOIN CapcoAppBundle:Opinion ovo WITH ov.parent = ovo
          LEFT JOIN ovo.step ovostep
          LEFT JOIN ovostep.projectAbstractStep ovopas
          WHERE a.isEnabled = 1 AND a.expired = 0 AND (
            (a.opinion IS NOT NULL AND o.isEnabled = 1 AND o.expired = 0 AND opas.project = :project)
            OR
            (a.opinionVersion IS NOT NULL AND ov.enabled = 1 AND ov.expired = 0 AND ovo.isEnabled = 1 AND ovo.expired = 0 AND ovopas.project = :project)
          )
          GROUP BY u.id
        ')
        ->setParameter('project', $project);

        return $query->getResult();
    }

    public function findUserByNewEmailConfirmationToken(string $token)
    {
        $qb = $this->createQueryBuilder('u');
        $qb
        ->andWhere('u.newEmailConfirmationToken = :token')
        ->setParameter('token', $token)
      ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findUsersThatJustExpired()
    {
        $qb = $this->createQueryBuilder('u');
        $qb
          ->andWhere('u.expired = false')
          ->andWhere('u.expiresAt IS NOT NULL')
          ->andWhere('u.expiresAt < :now')
          ->setParameter('now', new \DateTime())
        ;

        return $qb->getQuery()->getResult();
    }

    public function findUsersThatWillExpireIn24Hours()
    {
        $qb = $this->createQueryBuilder('u');
        $qb
          ->andWhere('u.expired = false')
          ->andWhere('u.expiresAt IS NOT NULL')
          ->andWhere('u.expiresAt < :tomorrow')
          ->andWhere('u.alertExpirationSent = false')
          ->setParameter('tomorrow', new \DateTime('+1 day'))
        ;

        return $qb->getQuery()->getResult();
    }

    public function findProjectOpinionContributorsWithCount(Project $project)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct opinions) as opinions_count')
            ->leftJoin('u.opinions', 'opinions', 'WITH', 'opinions.isEnabled = 1 AND opinions.expired = 0')
            ->leftJoin('opinions.step', 'step', 'WITH', 'step.isEnabled = 1')
            ->leftJoin('step.projectAbstractStep', 'cas')
            ->where('cas.project = :project')
            ->groupBy('u.id')
            ->setParameter('project', $project)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findProjectProposalContributorsWithCount(Project $project)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct proposals) as proposals_count')
            ->leftJoin('u.proposals', 'proposals', 'WITH', 'proposals.enabled = 1 AND proposals.expired = 0')
            ->leftJoin('proposals.proposalForm', 'proposalForm')
            ->leftJoin('proposalForm.step', 'step', 'WITH', 'step.isEnabled = 1')
            ->leftJoin('step.projectAbstractStep', 'pas')
            ->where('pas.project = :project')
            ->groupBy('u.id')
            ->setParameter('project', $project)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findProjectReplyContributorsWithCount(Project $project, $excludePrivate = false)
    {
        $replyWith = $excludePrivate ? '(replies.enabled = 1 AND replies.private = 0)' : 'replies.enabled = 1';
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct replies) as replies_count')
            ->leftJoin('u.replies', 'replies', 'WITH', $replyWith)
            ->leftJoin('replies.questionnaire', 'questionnaire')
            ->leftJoin('questionnaire.step', 'step', 'WITH', 'step.isEnabled = 1')
            ->leftJoin('step.projectAbstractStep', 'pas')
            ->where('pas.project = :project')
            ->groupBy('u.id')
            ->setParameter('project', $project)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findProjectVersionContributorsWithCount(Project $project)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct versions) as versions_count')
            ->leftJoin('u.opinionVersions', 'versions', 'WITH', 'versions.enabled = 1 AND versions.expired = 0')
            ->leftJoin('versions.parent', 'opinions', 'WITH', 'opinions.isEnabled = 1 AND opinions.expired = 0')
            ->leftJoin('opinions.step', 'step', 'WITH', 'step.isEnabled = 1')
            ->leftJoin('step.projectAbstractStep', 'cas')
            ->where('cas.project = :project')
            ->groupBy('u.id')
            ->setParameter('project', $project)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findProjectOpinionVotersWithCount(Project $project)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct opinions_votes) as opinions_votes_count')
            ->leftJoin('CapcoAppBundle:OpinionVote', 'opinions_votes', 'WITH', 'opinions_votes.user = u AND opinions_votes.expired = 0')
            ->leftJoin('opinions_votes.opinion', 'opinions_votes_opinion', 'WITH', 'opinions_votes_opinion.isEnabled = 1 AND opinions_votes_opinion.expired = 0')
            ->leftJoin('opinions_votes_opinion.step', 'opinions_votes_opinion_step', 'WITH', 'opinions_votes_opinion_step.isEnabled = 1')
            ->leftJoin('opinions_votes_opinion_step.projectAbstractStep', 'cas')
            ->where('cas.project = :project')
            ->groupBy('u.id')
            ->setParameter('project', $project)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findProjectVersionVotersWithCount(Project $project)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct versions_votes) as versions_votes_count')
            ->leftJoin('CapcoAppBundle:OpinionVersionVote', 'versions_votes', 'WITH', 'versions_votes.user = u AND versions_votes.expired = 0')
            ->leftJoin('versions_votes.opinionVersion', 'versions_votes_version', 'WITH', 'versions_votes_version.enabled = 1 AND versions_votes_version.expired = 0')
            ->leftJoin('versions_votes_version.parent', 'versions_votes_version_parent', 'WITH', 'versions_votes_version_parent.isEnabled = 1 AND versions_votes_version_parent.expired = 0')
            ->leftJoin('versions_votes_version_parent.step', 'versions_votes_version_step', 'WITH', 'versions_votes_version_step.isEnabled = 1')
            ->leftJoin('versions_votes_version_step.projectAbstractStep', 'cas')
            ->where('cas.project = :project')
            ->groupBy('u.id')
            ->setParameter('project', $project)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findProjectArgumentVotersWithCount(Project $project)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
         'SELECT u.id, count(distinct av) as arguments_votes_count
          FROM CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:ArgumentVote av WITH av.user = u
          LEFT JOIN CapcoAppBundle:Argument a WITH av.argument = a
          LEFT JOIN CapcoAppBundle:OpinionVersion ov WITH a.opinionVersion = ov
          LEFT JOIN CapcoAppBundle:Opinion o WITH a.opinion = o
          LEFT JOIN CapcoAppBundle:Opinion ovo WITH ov.parent = ovo
          WHERE av.user = u AND a.isEnabled = 1 AND a.expired = 0 AND (
            (a.opinion IS NOT NULL AND o.isEnabled = 1 AND o.expired = 0 AND o.step = :project)
            OR
            (a.opinionVersion IS NOT NULL AND ov.enabled = 1 AND ov.expired = 0 AND ovo.isEnabled = 1 AND ovo.expired = 0 AND ovo.step = :project)
          )
          GROUP BY av.user')
        ->setParameter('project', $project);

        return $query->getResult();
    }

    public function findProjectSourceVotersWithCount(Project $project)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
          'SELECT u.id, count(distinct sv) as sources_votes_count
          FROM CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:SourceVote sv WITH sv.user = u
          LEFT JOIN CapcoAppBundle:Source s WITH sv.source = s
          LEFT JOIN CapcoAppBundle:OpinionVersion ov WITH s.opinionVersion = ov
          LEFT JOIN CapcoAppBundle:Opinion o WITH s.Opinion = o
          LEFT JOIN CapcoAppBundle:Opinion ovo WITH ov.parent = ovo
          WHERE sv.user = u AND s.isEnabled = 1 AND (
            (s.Opinion IS NOT NULL AND o.isEnabled = 1 AND o.expired = 0 AND o.step = :project)
            OR
            (s.opinionVersion IS NOT NULL AND ov.enabled = 1 AND ov.expired = 0 AND ovo.isEnabled = 1 AND ovo.expired = 0 AND ovo.step = :project)
          )
          GROUP BY sv.user
        ')
            ->setParameter('project', $project);

        return $query->getResult();
    }

    public function findProjectProposalVotersWithCount(Project $project, $excludePrivate = false)
    {
        $em = $this->getEntityManager();
        $voteWith = $excludePrivate ? '(pv.user = u AND pv.private = 0)' : 'pv.user = u';
        $rawQuery = 'SELECT u.id, count(distinct pv) as proposals_votes_count
          from CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:ProposalSelectionVote pv WITH ' . $voteWith . '
          LEFT JOIN CapcoAppBundle:Proposal p WITH pv.proposal = p
          LEFT JOIN pv.selectionStep s
          LEFT JOIN s.projectAbstractStep pas
          WHERE pv.user = u AND p.enabled = 1 AND p.expired = 0 AND pas.project = :project
          GROUP BY pv.user
        ';
        $query = $em->createQuery($rawQuery)
            ->setParameter('project', $project)
        ;

        return $query->getResult();
    }

    public function countProjectProposalAnonymousVotersWithCount(Project $project, $excludePrivate = false)
    {
        $query = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('COUNT(DISTINCT proposal_selection_vote.email)')
            ->from('CapcoAppBundle:ProposalSelectionVote', 'proposal_selection_vote')
            ->leftJoin('CapcoAppBundle:Proposal', 'proposal', Join::WITH, 'proposal_selection_vote.proposal = proposal')
            ->leftJoin('proposal_selection_vote.selectionStep', 'selection_step')
            ->leftJoin('selection_step.projectAbstractStep', 'project_abstract_step')
            ->andWhere('proposal.enabled = 1')
            ->andWhere('proposal.expired = 0')
            ->andWhere('project_abstract_step.project = :project')
            ->setParameter('project', $project);

        $query->andWhere($query->expr()->andX($query->expr()->isNotNull('proposal_selection_vote.email')));

        if ($excludePrivate) {
            $query->andWhere('proposal_selection_vote.private = 0');
        }

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    public function findWithMediaByIds($ids)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->addSelect('m')
            ->leftJoin('u.Media', 'm')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb->getQuery()->getResult();
    }

    public function getEnabledWith($type = null, $from = null, $to = null)
    {
        $qb = $this->getIsEnabledQueryBuilder();

        if ($type) {
            $qb->andWhere('u.userType = :type')
               ->setParameter('type', $type)
               ;
        }

        if ($from) {
            $qb->andWhere('u.createdAt >= :from')
               ->setParameter('from', $from)
               ;
        }

        if ($to) {
            $qb->andWhere('u.createdAt <= :to')
               ->setParameter('to', $to)
               ;
        }

        return $qb->getQuery()->getResult();
    }

    public function findConsultationStepSourceContributorsWithCount(ConsultationStep $step)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT u.id, count(distinct s) as sources_count
          from CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:Source s WITH s.Author = u
          LEFT JOIN CapcoAppBundle:OpinionVersion ov WITH s.opinionVersion = ov
          LEFT JOIN CapcoAppBundle:Opinion o WITH s.Opinion = o
          LEFT JOIN CapcoAppBundle:Opinion ovo WITH ov.parent = ovo
          WHERE s.isEnabled = 1 AND s.expired = 0 AND (
            (s.Opinion IS NOT NULL AND o.isEnabled = 1 AND o.expired = 0 AND o.step = :step)
            OR
            (s.opinionVersion IS NOT NULL AND ov.enabled = 1 AND ov.expired = 0 AND ovo.isEnabled = 1 AND ovo.expired = 0 AND ovo.step = :step)
          )
          GROUP BY u.id
        ')
            ->setParameter('step', $step);

        return $query->getResult();
    }

    public function findConsultationStepArgumentContributorsWithCount(ConsultationStep $step)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT u.id, count(distinct a) as arguments_count
          FROM CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:Argument a WITH a.Author = u
          LEFT JOIN CapcoAppBundle:OpinionVersion ov WITH a.opinionVersion = ov
          LEFT JOIN CapcoAppBundle:Opinion o WITH a.opinion = o
          LEFT JOIN CapcoAppBundle:Opinion ovo WITH ov.parent = ovo
          WHERE a.isEnabled = 1 AND a.expired = 0 AND (
            (a.opinion IS NOT NULL AND o.isEnabled = 1 AND o.expired = 0 AND o.step = :step)
            OR
            (a.opinionVersion IS NOT NULL AND ov.enabled = 1 AND ov.expired = 0 AND ovo.isEnabled = 1 AND ovo.expired = 0 AND ovo.step = :step)
          )
          GROUP BY u.id
        ')
            ->setParameter('step', $step);

        return $query->getResult();
    }

    public function findConsultationStepOpinionContributorsWithCount(ConsultationStep $step)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct opinions) as opinions_count')
            ->leftJoin('u.opinions', 'opinions', 'WITH', 'opinions.isEnabled = 1 AND opinions.expired = 0')
            ->where('opinions.step = :step')
            ->groupBy('u.id')
            ->setParameter('step', $step)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCollectStepProposalContributorsWithCount(CollectStep $step)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct proposals) as proposals_count')
            ->leftJoin('u.proposals', 'proposals', 'WITH', 'proposals.enabled = 1 AND proposals.expired = 0')
            ->leftJoin('proposals.proposalForm', 'proposalForm')
            ->where('proposalForm.step = :step')
            ->groupBy('u.id')
            ->setParameter('step', $step)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findQuestionnaireStepReplyContributorsWithCount(QuestionnaireStep $step)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct replies) as replies_count')
            ->leftJoin('u.replies', 'replies', 'WITH', 'replies.enabled = 1 AND replies.expired = 0')
            ->leftJoin('replies.questionnaire', 'questionnaire')
            ->where('questionnaire.step = :step')
            ->groupBy('u.id')
            ->setParameter('step', $step)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationStepVersionContributorsWithCount(ConsultationStep $step)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct versions) as versions_count')
            ->leftJoin('u.opinionVersions', 'versions', 'WITH', 'versions.enabled = 1 AND versions.expired = 0')
            ->leftJoin('versions.parent', 'opinions', 'WITH', 'opinions.isEnabled = 1 AND opinions.expired = 0')
            ->where('opinions.step = :step')
            ->groupBy('u.id')
            ->setParameter('step', $step)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationStepOpinionVotersWithCount(ConsultationStep $step)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct opinions_votes) as opinions_votes_count')
            ->leftJoin('CapcoAppBundle:OpinionVote', 'opinions_votes', 'WITH', 'opinions_votes.user = u AND opinions_votes.expired = 0')
            ->leftJoin('opinions_votes.opinion', 'opinions_votes_opinion', 'WITH', 'opinions_votes_opinion.isEnabled = 1 AND opinions_votes_opinion.expired = 0')
            ->where('opinions_votes_opinion.step = :step')
            ->groupBy('u.id')
            ->setParameter('step', $step)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationStepVersionVotersWithCount(ConsultationStep $step)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u.id', 'count(distinct versions_votes) as versions_votes_count')
            ->leftJoin('CapcoAppBundle:OpinionVersionVote', 'versions_votes', 'WITH', 'versions_votes.user = u AND versions_votes.expired = 0')
            ->leftJoin('versions_votes.opinionVersion', 'versions_votes_version', 'WITH', 'versions_votes_version.enabled = 1 AND versions_votes_version.expired = 0')
            ->leftJoin('versions_votes_version.parent', 'versions_votes_version_parent', 'WITH', 'versions_votes_version_parent.isEnabled = 1 AND versions_votes_version_parent.expired = 0')
            ->where('versions_votes_version_parent.step = :step')
            ->groupBy('u.id')
            ->setParameter('step', $step)
        ;

        return $qb->getQuery()->getResult();
    }

    public function findConsultationStepArgumentVotersWithCount(ConsultationStep $step)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
          'SELECT u.id, count(distinct av) as arguments_votes_count
          from CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:ArgumentVote av WITH av.user = u
          LEFT JOIN CapcoAppBundle:Argument a WITH av.argument = a
          LEFT JOIN CapcoAppBundle:OpinionVersion ov WITH a.opinionVersion = ov
          LEFT JOIN CapcoAppBundle:Opinion o WITH a.opinion = o
          LEFT JOIN CapcoAppBundle:Opinion ovo WITH ov.parent = ovo
          WHERE av.user = u AND a.isEnabled = 1 AND a.expired = 0 AND (
            (a.opinion IS NOT NULL AND o.isEnabled = 1 AND o.expired = 0 AND o.step = :step)
            OR
            (a.opinionVersion IS NOT NULL AND ov.enabled = 1 AND ov.expired = 0 AND ovo.isEnabled = 1 AND ovo.expired = 0 AND ovo.step = :step)
          )
          GROUP BY av.user
        ')
            ->setParameter('step', $step);

        return $query->getResult();
    }

    public function findConsultationStepSourceVotersWithCount(ConsultationStep $step)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
          'SELECT u.id, count(distinct sv) as sources_votes_count
          FROM CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:SourceVote sv WITH sv.user = u
          LEFT JOIN CapcoAppBundle:Source s WITH sv.source = s
          LEFT JOIN CapcoAppBundle:OpinionVersion ov WITH s.opinionVersion = ov
          LEFT JOIN CapcoAppBundle:Opinion o WITH s.Opinion = o
          LEFT JOIN CapcoAppBundle:Opinion ovo WITH ov.parent = ovo
          WHERE sv.user = u AND s.isEnabled = 1 AND s.expired = 0 AND (
            (s.Opinion IS NOT NULL AND o.isEnabled = 1 AND o.expired = 0 AND o.step = :step)
            OR
            (s.opinionVersion IS NOT NULL AND ov.enabled = 1 AND ov.expired = 0 AND ovo.isEnabled = 1 AND ovo.expired = 0 AND ovo.step = :step)
          )
          GROUP BY sv.user
        ')
            ->setParameter('step', $step);

        return $query->getResult();
    }

    public function findSelectionStepProposalVotersWithCount(SelectionStep $step)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
         'SELECT u.id, count(distinct pv) as proposals_votes_count
          FROM CapcoUserBundle:User u
          LEFT JOIN CapcoAppBundle:ProposalSelectionVote pv WITH (pv.user = u AND pv.selectionStep = :step)
          LEFT JOIN CapcoAppBundle:Proposal p WITH pv.proposal = p
          WHERE pv.user = u AND pv.expired = 0 AND p.enabled = 1 AND p.expired = 0
          GROUP BY pv.user
        ')
            ->setParameter('step', $step);

        return $query->getResult();
    }

    public function countSelectionStepProposalAnonymousVoters(SelectionStep $step)
    {
        $query = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(DISTINCT proposal_selection_vote.email)')
            ->from('CapcoAppBundle:ProposalSelectionVote', 'proposal_selection_vote')
            ->leftJoin('CapcoAppBundle:Proposal', 'proposal', Join::WITH, 'proposal_selection_vote.proposal = proposal')
            ->andWhere('proposal_selection_vote.expired = 0')
            ->andWhere('proposal.enabled = 1')
            ->andWhere('proposal.expired = 0')
            ->andWhere('proposal_selection_vote.selectionStep = :step');

        $query->andWhere($query->expr()->andX($query->expr()->isNotNull('proposal_selection_vote.email')))
            ->setParameter('step', $step);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * Get search results.
     *
     * @param int  $nbByPage
     * @param int  $page
     * @param null $sort
     * @param null $type
     *
     * @return Paginator
     */
    public function getSearchResults($nbByPage = 8, $page = 1, $sort = null, $type = null)
    {
        if ($page < 1) {
            throw new \InvalidArgumentException(sprintf(
                'The argument "page" cannot be lower than 1 (current value: "%s")',
                $page
            ));
        }

        $qb = $this->getIsEnabledQueryBuilder()
            ->addSelect('m', 'ut')
            ->leftJoin('u.Media', 'm')
            ->leftJoin('u.userType', 'ut')
        ;

        if ($type !== null && $type !== UserType::FILTER_ALL) {
            $qb->andWhere('ut.slug = :type')
                ->setParameter('type', $type)
            ;
        }

        if (isset(User::$sortOrder[$sort]) && User::$sortOrder[$sort] === User::SORT_ORDER_CONTRIBUTIONS_COUNT) {
            $qb = $this->orderByContributionsCount($qb, 'DESC');
        } else {
            $qb->orderBy('u.createdAt', 'DESC');
        }

        if ($nbByPage > 0) {
            $qb->setFirstResult(($page - 1) * $nbByPage)
                ->setMaxResults($nbByPage);
        }

        return new Paginator($qb);
    }

    public function orderByContributionsCount(QueryBuilder $qb, $order = 'DESC')
    {
        return $qb->addSelect('(u.opinionsCount + u.opinionVersionsCount + u.argumentsCount + u.sourcesCount + u.ideasCount + u.ideaCommentsCount + u.postCommentsCount + u.eventCommentsCount) AS HIDDEN contributionsCount')
            ->orderBy('contributionsCount', $order)
        ;
    }

    public function getAllUsersWithoutSuperAdmin()
    {
        $query = $this->createQueryBuilder('user')
            ->andWhere('user.roles != :role')
            ->setParameter('role', serialize(['ROLE_SUPER_ADMIN']));

        return $query->getQuery()->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getIsEnabledQueryBuilder()
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.enabled = :enabled')
            ->setParameter('enabled', true);
    }
}
