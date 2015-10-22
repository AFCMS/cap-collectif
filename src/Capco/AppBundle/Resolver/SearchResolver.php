<?php

namespace Capco\AppBundle\Resolver;

use Elastica\Index;
use Elastica\Query;
use Elastica\Query\MultiMatch;
use Elastica\Query\Filtered;
use Elastica\Filter\Type;
use FOS\ElasticaBundle\Transformer\ElasticaToModelTransformerInterface;

class SearchResolver
{
    const RESULT_PER_PAGE = 10;

    protected $index;
    protected $transformer;

    public function __construct(Index $index, ElasticaToModelTransformerInterface $transformer)
    {
        $this->index = $index;
        $this->transformer = $transformer;
    }

    /**
     * search by term and type in elasticsearch
     *
     * @param integer   $page
     * @param string    $term
     * @param string    $type
     * @param string    $sort
     *
     * @return array
     */
    public function searchAll($page, $term, $type = 'all', $sort = 'score')
    {
        $from       = ($page - 1) * self::RESULT_PER_PAGE;

        if (!empty(trim($term))) {
            $termQuery = $this->getTermQuery($term);
            if ('all' !== $type) {
                $query = new Query($this->getTypeFilteredQuery($type, $termQuery));
            } else {
                $query = new Query($termQuery);
            }
        } else {
            $termQuery = new Query\MatchAll();
        }

        if ($sort !== null && $sort !== 'score') {
            $query->setSort($this->getSortSettings($sort));
        }

        $query->setHighlight($this->getHighlightSettings());

        $query->setFrom($from);
        $query->setSize(self::RESULT_PER_PAGE);

        $resultSet = $this->index->search($query);
        $count = $resultSet->getTotalHits();

        $results = $this->transformer->hybridTransform($resultSet->getResults());

        return ['count' => $count, 'results' => $results, 'pages' => ceil($count / self::RESULT_PER_PAGE)];
    }

    // get filtered query with type filter and term query
    public function getTypeFilteredQuery($type, $termQuery)
    {
        $typeFilter = new Type($type);

        return new Filtered($termQuery, $typeFilter);
    }

    // get multi match query on term
    protected function getTermQuery($term)
    {
        $termQuery = new MultiMatch();
        $termQuery->setQuery($term);
        $termQuery->setFields([
            'title^5',
            'strippedBody',
            'strippedObject',
            'body',
            'teaser',
            'excerpt',
            'username^5',
            'biography',
        ]);

        return $termQuery;
    }

    protected function getSortSettings($sort)
    {
        $term = null;
        if ($sort === 'date') {
            $term = 'updatedAt';
        }
        if ($term === null) {
            return;
        }

        return [
            $term => [
                'order' => 'desc',
            ],
        ];
    }

    // get array of settings for highlighted results
    protected function getHighlightSettings()
    {
        return [
            'pre_tags'            => ['<span class="search__highlight">'],
            'post_tags'           => ['</span>'],
            'number_of_fragments' => 3,
            'fragment_size'       => 175,
            'fields'              => [
                'title'          => ['number_of_fragments' => 0],
                'strippedObject' => new \stdClass(),
                'strippedBody'   => new \stdClass(),
                'body'           => new \stdClass(),
                'teaser'         => new \stdClass(),
                'excerpt'        => new \stdClass(),
                'username'       => ['number_of_fragments' => 0],
                'biography'      => new \stdClass(),
            ],
        ];
    }
}
