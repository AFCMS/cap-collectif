<?php

namespace Capco\AppBundle\Normalizer;

use Capco\AppBundle\Entity\Proposal;
use Capco\AppBundle\GraphQL\DataLoader\Commentable\CommentableCommentsDataLoader;
use Capco\AppBundle\Repository\ProposalCollectVoteRepository;
use Capco\AppBundle\Repository\ProposalSelectionVoteRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;

class ProposalNormalizer implements
    NormalizerInterface,
    SerializerAwareInterface,
    CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;
    private ObjectNormalizer $normalizer;
    private $proposalSelectionVoteRepository;
    private $proposalCollectVoteRepository;
    private $commentableCommentsDataLoader;

    public function __construct(
        ObjectNormalizer $normalizer,
        ProposalSelectionVoteRepository $proposalSelectionVoteRepository,
        ProposalCollectVoteRepository $proposalCollectVoteRepository,
        CommentableCommentsDataLoader $commentableCommentsDataLoader
    ) {
        $this->normalizer = $normalizer;
        $this->proposalSelectionVoteRepository = $proposalSelectionVoteRepository;
        $this->proposalCollectVoteRepository = $proposalCollectVoteRepository;
        $this->commentableCommentsDataLoader = $commentableCommentsDataLoader;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $groups =
            isset($context['groups']) && \is_array($context['groups']) ? $context['groups'] : [];
        $data = $this->normalizer->normalize($object, $format, $context);
        if (\in_array('ElasticsearchNestedProposal', $groups)) {
            return $data;
        }

        // TODO: Migrate those queries to ES would be really faster.
        $selectionVotesCount = $this->proposalSelectionVoteRepository->getCountsByProposalGroupedByStepsId(
            $object
        );
        $collectVotesCount = $this->proposalCollectVoteRepository->getCountsByProposalGroupedByStepsId(
            $object
        );

        $data['progressStatus'] = $object->getGlobalProgressStatus();
        $stepCounter = [];
        $totalCount = 0;
        foreach ($collectVotesCount as $stepId => $value) {
            $stepCounter[] = [
                'step' => ['id' => $stepId],
                'count' => $value,
            ];
            $totalCount += $value;
        }
        foreach ($selectionVotesCount as $stepId => $value) {
            $stepCounter[] = [
                'step' => ['id' => $stepId],
                'count' => $value,
            ];
            $totalCount += $value;
        }

        $data['votesCountByStep'] = $stepCounter;
        $data['votesCount'] = $totalCount;

        if ($object->isCommentable()) {
            $args = new Argument([
                'orderBy' => ['field' => 'PUBLISHED_AT', 'direction' => 'DESC'],
                'first' => 0,
            ]);
            $commentsConnection = $this->commentableCommentsDataLoader->resolve(
                $object,
                $args,
                null
            );
            $data['commentsCount'] = $commentsConnection->{'totalCountWithAnswers'};
        } else {
            $data['commentsCount'] = 0;
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Proposal;
    }
}
