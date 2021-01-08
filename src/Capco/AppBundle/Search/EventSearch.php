<?php

namespace Capco\AppBundle\Search;

use Capco\AppBundle\GraphQL\Resolver\GlobalIdResolver;
use Capco\AppBundle\Repository\EventRepository;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Exists;
use Elastica\Query\Term;
use Elastica\Result;
use Capco\AppBundle\Enum\EventOrderField;

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
        'fullAddress',
        'fullAddress.std',
        'translations.locale',
    ];

    private EventRepository $eventRepository;

    public function __construct(Index $index, EventRepository $eventRepository)
    {
        parent::__construct($index);
        $this->eventRepository = $eventRepository;
        $this->type = 'event';
    }

    public function searchEvents(
        int $offset,
        int $limit,
        ?string $terms,
        array $providedFilters,
        array $orderBy
    ): array {
        $boolQuery = new Query\BoolQuery();
        $boolQuery = $this->searchTermsInMultipleFields(
            $boolQuery,
            self::SEARCH_FIELDS,
            $terms,
            'phrase_prefix'
        );

        if (isset($providedFilters['isFuture'])) {
            switch ($providedFilters['isFuture']) {
                // PASSED only
                case false:
                    $dateBoolQuery = new Query\BoolQuery();
                    $endDateIsNullQuery = new Query\BoolQuery();
                    $dateBoolQuery->addShould(new Query\Range('endAt', ['lt' => 'now/d']));
                    $endDateIsNullQuery->addMustNot(new Query\Exists('endAt'));
                    $dateBoolQuery->addShould($endDateIsNullQuery);
                    $boolQuery->addFilter($dateBoolQuery);
                    $boolQuery->addMust(new Query\Range('startAt', ['lt' => 'now/d']));

                    break;
                // FUTURE and current
                case true:
                    $dateBoolQuery = new Query\BoolQuery();
                    $dateBoolQuery->addShould(new Query\Range('startAt', ['gte' => 'now/d']));
                    $dateBoolQuery->addShould(new Query\Range('endAt', ['gte' => 'now/d']));
                    $boolQuery->addFilter($dateBoolQuery);

                    break;
                // FUTURE and PASSED (null case)
                default:
                    break;
            }
        }

        $filters = $this->getFilters($providedFilters);

        foreach ($filters as $key => $value) {
            $boolQuery->addFilter(new Term([$key => ['value' => $value]]));
        }
        $boolQuery->addFilter(new Exists('id'));

        if ('random' === $orderBy['field']) {
            $query = $this->getRandomSortedQuery($boolQuery);
        } else {
            $query = new Query($boolQuery);
            $query->setSort($this->getSort($orderBy));
        }

        $query
            ->setSource(['id'])
            ->setFrom($offset)
            ->setSize($limit);
        $this->addObjectTypeFilter($query, $this->type);
        $resultSet = $this->index->search($query);

        $ids = array_map(function (Result $result) {
            return $result->getData()['id'];
        }, $resultSet->getResults());

        $data = $resultSet->getResponse()->getData();
        $count = $data['hits']['total']['value'];

        return [
            'events' => $this->getHydratedResults($this->eventRepository, $ids),
            'count' => $count,
        ];
    }

    public function getAllIdsOfAuthorOfEvent(?string $terms = null): array
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery = $this->searchTermsInMultipleFields(
            $boolQuery,
            ['author.username', 'author.username.std'],
            $terms,
            'phrase_prefix'
        );

        $query = new Query($boolQuery);
        $this->addObjectTypeFilter($query, $this->type);
        $resultSet = $this->index->search($query);

        $authorIds = array_map(function (Result $result) {
            return $result->getData()['author']['id'];
        }, $resultSet->getResults());

        return array_unique($authorIds);
    }

    private function getSort(array $orderBy): array
    {
        switch ($orderBy['field']) {
            case EventOrderField::END_AT:
                return [
                    'endAt' => ['order' => $orderBy['direction']],
                    'startAt' => ['order' => $orderBy['direction']],
                    'createdAt' => ['order' => $orderBy['direction']],
                ];

            case EventOrderField::START_AT:
                return [
                    'startAt' => ['order' => $orderBy['direction']],
                    'endAt' => ['order' => $orderBy['direction']],
                    'createdAt' => ['order' => $orderBy['direction']],
                ];
            default:
                throw new \RuntimeException("Unknown order: ${$orderBy['field']}");
        }
    }

    private function getFilters(array $providedFilters): array
    {
        $filters = [];

        if (isset($providedFilters['themes'])) {
            $filters['themes.id'] = $providedFilters['themes'];
        }
        if (isset($providedFilters['locale'])) {
            $filters['translations.locale'] = $providedFilters['locale'];
        }
        if (isset($providedFilters['isRegistrable'])) {
            $filters['isRegistrable'] = $providedFilters['isRegistrable'];
        }
        if (isset($providedFilters['isPresential'])) {
            $filters['isPresential'] = $providedFilters['isPresential'];
        }
        if (isset($providedFilters['enabled'])) {
            $filters['enabled'] = $providedFilters['enabled'];
        }
        if (isset($providedFilters['projects'])) {
            $filters['projects.id'] = $providedFilters['projects'];
        }
        if (isset($providedFilters['userType'])) {
            $filters['author.userType.id'] = $providedFilters['userType'];
        }
        if (isset($providedFilters['author'])) {
            $filters['author.id'] = GlobalIdResolver::getDecodedId($providedFilters['author'])[
                'id'
            ];
        }

        return $filters;
    }
}
