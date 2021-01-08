<?php

namespace Capco\AppBundle\Search;

use Capco\AppBundle\Elasticsearch\ElasticsearchPaginatedResult;
use Capco\AppBundle\Enum\ProjectVisibilityMode;
use Capco\AppBundle\Repository\CommentRepository;
use Capco\UserBundle\Entity\User;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Exists;
use Elastica\Query\Term;
use Elastica\ResultSet;

class CommentSearch extends Search
{
    private $commentRepository;

    public function __construct(Index $index, CommentRepository $commentRepository)
    {
        parent::__construct($index);
        $this->type = 'comment';
        $this->index = $index;
        $this->commentRepository = $commentRepository;
    }

    public function getCommentsByAuthorViewerCanSee(
        User $author,
        User $viewer,
        int $limit = 100,
        ?string $cursor = null
    ): ElasticsearchPaginatedResult {
        $query = $this->createCommentsByAuthorViewerCanSeeQuery($author, $viewer);
        $this->applyCursor($query, $cursor);
        $query->setSize($limit);
        $this->addObjectTypeFilter($query, $this->type);
        $response = $this->index->search($query);
        $cursors = $this->getCursors($response);

        return $this->getData($cursors, $response);
    }

    public function getPublicCommentsByAuthor(
        User $author,
        int $limit = 100,
        ?string $cursor = null
    ): ElasticsearchPaginatedResult {
        $query = $this->createPublicCommentsByAuthorQuery($author);
        $this->applyCursor($query, $cursor);
        $query->setSize($limit);
        $response = $this->index->search($query);
        $this->addObjectTypeFilter($query, $this->type);
        $cursors = $this->getCursors($response);

        return $this->getData($cursors, $response);
    }

    public function getCommentsByUser(
        User $user,
        int $limit = 100,
        ?string $cursor = null
    ): ElasticsearchPaginatedResult {
        $query = $this->createCommentsByUserQuery($user);
        $this->applyCursor($query, $cursor);
        $query->setSize($limit);
        $this->addObjectTypeFilter($query, $this->type);
        $response = $this->index->search($query);
        $cursors = $this->getCursors($response);

        return $this->getData($cursors, $response);
    }

    private function getData(array $cursors, ResultSet $response): ElasticsearchPaginatedResult
    {
        $count = $response->getResponse()->getData()['hits']['total']['value'];

        return new ElasticsearchPaginatedResult(
            $this->getHydratedResultsFromResultSet($this->commentRepository, $response),
            $cursors,
            $count
        );
    }

    private function createCommentsByUserQuery(User $user): Query
    {
        $boolQuery = new BoolQuery();
        $boolQuery
            ->addFilter(new Term(['published' => ['value' => true]]))
            ->addFilter(new Term(['author.id' => ['value' => $user->getId()]]));
        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'DESC'], 'id' => new \stdClass()]);

        return $query;
    }

    private function createPublicCommentsByAuthorQuery(User $author): Query
    {
        $boolQuery = new BoolQuery();
        $boolQuery->addFilter(
            (new BoolQuery())->addShould([
                (new BoolQuery())->addMustNot([new Exists('proposal')]),
                (new BoolQuery())
                    ->addFilter(
                        new Term([
                            'proposal.project.visibility' => [
                                'value' => ProjectVisibilityMode::VISIBILITY_PUBLIC,
                            ],
                        ])
                    )
                    ->addFilter(new Term(['proposal.visible' => ['value' => true]])),
            ])
        );
        $boolQuery
            ->addFilter(new Term(['published' => ['value' => true]]))
            ->addFilter(new Term(['author.id' => ['value' => $author->getId()]]));

        $boolQuery->addMustNot([new Exists('trashedStatus')]);
        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'DESC'], 'id' => new \stdClass()]);

        return $query;
    }

    private function createCommentsByAuthorViewerCanSeeQuery(User $author, User $viewer): Query
    {
        $boolQuery = new BoolQuery();
        $conditions = [
            new Term(['author.id' => ['value' => $author->getId()]]),
            new Term(['published' => ['value' => true]]),
        ];

        if ($viewer !== $author && !$viewer->isSuperAdmin()) {
            $adminSubConditions = [];
            $superAdminSubConditions = $this->getFiltersForProjectViewerCanSee(
                'proposal.project',
                $viewer
            );
            if (!$viewer->isAdmin()) {
                $adminSubConditions = [new Term(['proposal.visible' => ['value' => true]])];
            }
            $conditions[] = (new BoolQuery())->addShould([
                (new BoolQuery())->addMustNot([new Exists('proposal')]),
                (new BoolQuery())->addMust(
                    array_merge(
                        [(new BoolQuery())->addShould($superAdminSubConditions)],
                        $adminSubConditions
                    )
                ),
            ]);
        }

        foreach ($conditions as $condition) {
            $boolQuery->addFilter($condition);
        }
        $boolQuery->addMustNot(new Exists('trashedStatus'));
        $query = new Query($boolQuery);
        $query->addSort(['createdAt' => ['order' => 'DESC'], 'id' => new \stdClass()]);

        return $query;
    }
}
