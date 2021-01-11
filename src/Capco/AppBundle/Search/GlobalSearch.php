<?php

namespace Capco\AppBundle\Search;

use Capco\AppBundle\Elasticsearch\ElasticaToDoctrineTransformer;
use Elastica\Index;
use Elastica\Query;

class GlobalSearch extends Search
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
        'username',
        'username.std',
        'biography',
        'biography.std',
    ];

    protected $transformer;

    public function __construct(Index $index, ElasticaToDoctrineTransformer $transformer)
    {
        parent::__construct($index);
        $this->transformer = $transformer;
    }

    public function search($page, $terms, $sortField, $sortOrder, $type = 'all'): array
    {
        $boolQuery = new Query\BoolQuery();
        $boolQuery = $this->searchTermsInMultipleFields($boolQuery, self::SEARCH_FIELDS, $terms, 'phrase_prefix');

        $query = new Query($boolQuery);
        $query->setSort([
            $sortField => ['order' => $sortOrder],
        ]);

        $pagination = self::RESULTS_PER_PAGE;
        $from = ($page - 1) * $pagination;
        $query->setFrom($from);
        $query->setSize($pagination);

        if ('all' === $type) {
            $resultSet = $this->index->search($query);
        } else {
            $resultSet = $this->index->getType($type)->search($query);
        }

        $results = $this->transformer->hybridTransform($resultSet->getResults());
        $count = $resultSet->getTotalHits();

        return [
            'count' => $count,
            'results' => $results,
            'pages' => ceil($count / $pagination),
        ];
    }
}
