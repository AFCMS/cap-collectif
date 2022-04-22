<?php

namespace Capco\AppBundle\Search;

use Capco\AppBundle\Repository\EventRepository;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Exists;
use Elastica\Query\Term;
use Elastica\Result;
use Capco\AppBundle\Entity\Event;
use Psr\Log\LoggerInterface;

class EventSearch extends Search
{
    const SEARCH_FIELDS = [
        'title',
        'title.std',
        'reference',
        'reference.std',
        'body',
        'body.std',
        'object',
        'object.std',
        'teaser',
        'teaser.std',
    ];

    private const OLD = 'PASSED';
    private const LAST = 'FUTURE';

    private $eventRepository;
    private $logger;

    public function __construct(Index $index, EventRepository $eventRepository, LoggerInterface $logger)
    {
        parent::__construct($index);
        $this->eventRepository = $eventRepository;
        $this->logger = $logger;
        $this->type = 'event';
    }

    public function searchEvents(
        int $offset,
        int $limit,
        ?string $order = null,
        $terms,
        array $providedFilters,
        string $seed
    ): array {
        $boolQuery = new Query\BoolQuery();
        $boolQuery = $this->searchTermsInMultipleFields(
            $boolQuery,
            self::SEARCH_FIELDS,
            $terms,
            'phrase_prefix'
        );

        $filters = $this->getFilters($providedFilters);

        foreach ($filters as $key => $value) {
            if ($key == 'endAt' || $key == 'startAt') {
                $boolQuery->addMust(new Query\Range($key, $value));
            } else {
                $boolQuery->addMust(new Term([$key => ['value' => $value]]));
            }
        }
        $boolQuery->addMust(new Exists('id'));

        if ('random' === $order) {
            $query = $this->getRandomSortedQuery($boolQuery, $seed);
        } else {
            $query = new Query($boolQuery);
            if ($order) {
                $query->setSort($this->getSort($order));
            }
        }

        $query->setSource(['id'])->setFrom($offset)->setSize($limit);
        $resultSet = $this->index->getType($this->type)->search($query);
        $events = $this->getHydratedResults(
            array_map(
                function (Result $result) {
                    return $result->getData()['id'];
                },
                $resultSet->getResults()
            )
        );

        return [
            'events' => $events,
            'count' => $resultSet->getTotalHits(),
            'order' => $order,
        ];
    }

    public function getHydratedResults(array $ids): array
    {
        // We can't use findById because we would lost the correct order of ids
        // https://stackoverflow.com/questions/28563738/symfony-2-doctrine-find-by-ordered-array-of-id/28578750
        return array_values(
            array_filter(
                array_map(
                    function (string $id) {
                        return $this->eventRepository->findOneBy(['id' => $id, 'isEnabled' => true]);
                    },
                    $ids
                ),
                function (?Event $event) {
                    return null !== $event;
                }
            )
        );
    }

    private function getSort(string $order): array
    {
        switch ($order) {
            case self::OLD:
                $sortField = 'endAt';
                $sortOrder = 'desc';
                break;
            case self::LAST:
                $sortField = 'startAt';
                $sortOrder = 'desc';
                break;
            case 'slug':
                $sortField = 'slug';
                $sortOrder = 'desc';
                break;
            default:
                throw new \RuntimeException("Unknow order: $order");
                break;
        }

        return [$sortField => ['order' => $sortOrder]];
    }

    private function getFilters(array $providedFilters): array
    {
        $filters = [];
        $now = "now/d";
        if (isset($providedFilters['time'])) {
            switch ($providedFilters['time']) {
                // PASSED only
                case self::OLD:
                    $filters['endAt'] = ['lt' => $now];
                    $filters['startAt'] = ['lt' => $now];
                    break;
                // FUTURE and current
                case self::LAST:
                    $filters['endAt'] = ['gte' => $now];
                    $filters['startAt'] = ['lte' => $now];
                    break;
                // FUTURE and PASSED
                default:
                    break;
            }
        }
        if (isset($providedFilters['themes'])) {
            $filters['theme.id'] = $providedFilters['themes'];
        }
        if (isset($providedFilters['author'])) {
            $filters['author.id'] = $providedFilters['author'];
        }
        if (isset($providedFilters['project'])) {
            $filters['project.id'] = $providedFilters['project'];
        }

        return $filters;
    }
}
