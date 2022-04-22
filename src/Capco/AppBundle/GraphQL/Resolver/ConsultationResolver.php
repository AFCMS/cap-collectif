<?php

namespace Capco\AppBundle\GraphQL\Resolver;

use Capco\AppBundle\Entity\Post;
use Capco\AppBundle\Entity\Reply;
use Capco\UserBundle\Entity\User;
use Capco\AppBundle\Entity\Answer;
use Capco\AppBundle\Entity\Source;
use Capco\AppBundle\Entity\Comment;
use Capco\AppBundle\Entity\Opinion;
use Capco\AppBundle\Entity\Argument;
use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\Entity\Reporting;
use Capco\AppBundle\Entity\OpinionType;
use Capco\AppBundle\Entity\OpinionVote;
use Capco\AppBundle\Entity\AbstractVote;
use Capco\AppBundle\Entity\OpinionVersion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Overblog\GraphQLBundle\Error\UserError;
use Capco\AppBundle\Model\CreatableInterface;
use Capco\AppBundle\Entity\OpinionVersionVote;
use Capco\AppBundle\Entity\Interfaces\Trashable;
use Capco\AppBundle\Entity\Steps\ConsultationStep;
use Capco\AppBundle\Entity\OpinionTypeAppendixType;
use Overblog\GraphQLBundle\Definition\Argument as Arg;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Capco\AppBundle\Entity\Interfaces\OpinionContributionInterface;

class ConsultationResolver implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public $project = null;

    public function resolveContributionType($data)
    {
        $typeResolver = $this->container->get('overblog_graphql.type_resolver');
        if ($data instanceof Opinion) {
            return $typeResolver->resolve('Opinion');
        }
        if ($data instanceof OpinionVote) {
            return $typeResolver->resolve('OpinionVote');
        }
        if ($data instanceof OpinionVersion) {
            return $typeResolver->resolve('Version');
        }
        if ($data instanceof OpinionVersionVote) {
            return $typeResolver->resolve('VersionVote');
        }
        if ($data instanceof Argument) {
            return $typeResolver->resolve('Argument');
        }
        if ($data instanceof Source) {
            return $typeResolver->resolve('Source');
        }
        if ($data instanceof Reporting) {
            return $typeResolver->resolve('Reporting');
        }
        if ($data instanceof Comment) {
            return $typeResolver->resolve('Comment');
        }
        if ($data instanceof Proposal) {
            return $typeResolver->resolve('Proposal');
        }
        if ($data instanceof Reply) {
            return $typeResolver->resolve('Reply');
        }

        if ($data instanceof Answer) {
            return $typeResolver->resolve('Answer');
        }

        if ($data instanceof Post) {
            return $typeResolver->resolve('Post');
        }
        throw new UserError('Could not resolve type of Contribution.');
    }

    public function getSectionAppendixId(OpinionTypeAppendixType $type)
    {
        return $type->getAppendixTypeId();
    }

    public function getSectionAppendixTitle(OpinionTypeAppendixType $type)
    {
        return $type->getAppendixTypeTitle();
    }

    public function getConsultationContributionsConnection(
        ConsultationStep $consultation,
        Arg $args
    ): Connection {
        $paginator = new Paginator(function ($offset, $limit) use ($consultation, $args) {
            $repo = $this->container->get('capco.opinion.repository');
            $criteria = ['step' => $consultation, 'trashed' => false];
            $field = $args->offsetGet('orderBy')['field'];
            $direction = $args->offsetGet('orderBy')['direction'];

            $orderBy = [$field => $direction];

            return $repo
                ->getByCriteriaOrdered($criteria, $orderBy, null, $offset)
                ->getIterator()
                ->getArrayCopy();
        });

        $totalCount = $consultation->getOpinionCount();

        return $paginator->auto($args, $totalCount);
    }

    public function getSectionContributionsConnection(OpinionType $section, Arg $args): Connection
    {
        $paginator = new Paginator(function ($offset, $limit) use ($section, $args) {
            $repo = $this->container->get('capco.opinion.repository');
            $criteria = ['section' => $section, 'trashed' => false];
            $field = $args->offsetGet('orderBy')['field'];
            $direction = $args->offsetGet('orderBy')['direction'];
            $orderBy = [$field => $direction];

            return $repo
                ->getByCriteriaOrdered($criteria, $orderBy, null, $offset)
                ->getIterator()
                ->getArrayCopy();
        });

        $totalCount = $section->getOpinions()->count();

        return $paginator->auto($args, $totalCount);
    }

    public function resolve(Arg $args)
    {
        $repo = $this->container->get('capco.consultation_step.repository');
        if (isset($args['id'])) {
            return [$repo->find($args['id'])];
        }

        return $repo->findAll();
    }

    public function getSectionChildren(OpinionType $type, Arg $argument)
    {
        $iterator = $type->getChildren()->getIterator();

        // define ordering closure, using preferred comparison method/field
        $iterator->uasort(function ($first, $second) {
            return (int) $first->getPosition() > (int) $second->getPosition() ? 1 : -1;
        });

        return $iterator;
    }

    public function getContributionsBySection(Arg $arg)
    {
        $typeId = $arg->offsetGet('sectionId');
        $type = $this->container->get('capco.opinion_type.repository')->find($typeId);

        return $this->getSectionOpinions($type, $arg);
    }

    public function getSectionUrl(OpinionType $type)
    {
        $step = $type->getStep();
        $project = $step->getProject();

        return (
            $this->container->get('router')->generate(
                'app_consultation_show_opinions',
                [
                    'projectSlug' => $project->getSlug(),
                    'stepSlug' => $step->getSlug(),
                    'opinionTypeSlug' => $type->getSlug(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '/1'
        );
    }

    public function getSectionOpinions(OpinionType $type, Arg $arg)
    {
        $limit = $arg->offsetGet('limit');

        if (0 === $type->getOpinions()->count()) {
            return [];
        }

        $opinionRepo = $this->container->get('capco.opinion.repository');
        $opinions = $opinionRepo->getByOpinionTypeOrdered(
            $type->getId(),
            $limit,
            1,
            $type->getDefaultFilter()
        );

        return $opinions;
    }

    public function getSectionOpinionsCount(OpinionType $type): int
    {
        $repo = $this->container->get('capco.opinion.repository');

        return $repo->countByOpinionType($type->getId());
    }

    public function resolveConsultationSections(
        ConsultationStep $consultation,
        Arg $argument
    ): \Traversable {
        /** @var Collection $sections */
        $sections = $consultation->getConsultationStepType()
            ? $consultation->getConsultationStepType()->getOpinionTypes()
            : new ArrayCollection();

        $iterator = $sections->getIterator();

        if ($sections) {
            $iterator = $sections
                ->filter(function (OpinionType $section) {
                    return null === $section->getParent();
                })
                ->getIterator();

            // define ordering closure, using preferred comparison method/field
            $iterator->uasort(function ($first, $second) {
                return (int) $first->getPosition() > (int) $second->getPosition() ? 1 : -1;
            });
        }

        return $iterator;
    }

    public function resolvePropositionSection(OpinionContributionInterface $proposition)
    {
        return $proposition->getOpinionType();
    }

    public function resolvePropositionVoteAuthor(AbstractVote $vote): ?User
    {
        return $vote->getUser();
    }

    public function resolveReportingType(Reporting $reporting): int
    {
        return $reporting->getStatus();
    }

    public function resolveReportingAuthor(Reporting $reporting)
    {
        return $reporting->getReporter();
    }

    public function resolvePropositionReportings(Opinion $opinion)
    {
        return $this->container->get('capco.reporting.repository')->findBy(['Opinion' => $opinion]);
    }

    public function resolveVersionReportings(OpinionVersion $version)
    {
        return $this->container->get('capco.reporting.repository')->findBy([
            'opinionVersion' => $version,
        ]);
    }

    public function resolveArgumentUrl(Argument $argument): string
    {
        $parent = $argument->getParent();
        if ($parent instanceof Opinion) {
            return (
                $this->container->get(
                    'Capco\AppBundle\GraphQL\Resolver\Opinion\OpinionUrlResolver'
                )->__invoke($parent) .
                '#arg-' .
                $argument->getId()
            );
        } elseif ($parent instanceof OpinionVersion) {
            return $this->resolveVersionUrl($parent) . '#arg-' . $argument->getId();
        }

        return '';
    }

    public function resolveVersionUrl(OpinionVersion $version): string
    {
        $opinion = $version->getParent();
        $opinionType = $opinion->getOpinionType();
        $step = $opinion->getStep();
        $project = $step->getProject();

        return $this->container->get('router')->generate(
            'app_project_show_opinion_version',
            [
                'projectSlug' => $project->getSlug(),
                'stepSlug' => $step->getSlug(),
                'opinionTypeSlug' => $opinionType->getSlug(),
                'opinionSlug' => $opinion->getSlug(),
                'versionSlug' => $version->getSlug(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    public function resolveCreatedAt(CreatableInterface $object): string
    {
        return $object->getCreatedAt()->format(\DateTime::ATOM);
    }

    public function resolveUpdatedAt($object): string
    {
        return $object->getUpdatedAt()->format(\DateTime::ATOM);
    }
}
