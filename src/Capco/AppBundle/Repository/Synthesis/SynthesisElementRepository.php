<?php

namespace Capco\AppBundle\Repository\Synthesis;

use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Gedmo\Tool\Wrapper\EntityWrapper;

/**
 * SynthesisElementRepository.
 */
class SynthesisElementRepository extends MaterializedPathRepository
{
    protected static $allowedFields = ['synthesis', 'published', 'archived', 'parent'];

    /**
     * Cout elements with values.
     *
     * @return int
     */
    public function countWith($synthesis, $type)
    {
        $qb = $this->createQueryBuilder('se')
            ->select('COUNT(se.id)')
        ;
        $qb = $this->addQueryConditionsForTypeAndSynthesis($qb, $type, $synthesis);

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get elements with values.
     *
     * @return int
     */
    public function getWith($synthesis, $type, $term, $offset = 0, $limit = 10)
    {
        $qb = $this->createQueryBuilder('se')
            ->addSelect('a', 'am')
            ->leftJoin('se.author', 'a')
            ->leftJoin('a.Media', 'am')
        ;

        if ($term !== null) {
            $qb
                ->andWhere('se.title LIKE :term')
                ->setParameter('term', '%'.$term.'%')
            ;
        }

        $qb = $this->addQueryConditionsForTypeAndSynthesis($qb, $type, $synthesis);

        $qb->orderBy('se.linkedDataLastUpdate', 'DESC');

        $qb
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        return new Paginator($qb);
    }

    /**
     * Get one element by id.
     *
     * @param $id
     *
     * @return mixed
     */
    public function getOne($id)
    {
        $qb = $this->createQueryBuilder('se')
            ->addSelect('a', 'am', 'parent', 'children', 'cauts', 'cautms', 'div', 'odiv')
            ->leftJoin('se.author', 'a')
            ->leftJoin('a.Media', 'am')
            ->leftJoin('se.parent', 'parent')
            ->leftJoin('se.children', 'children')
            ->leftJoin('children.author', 'cauts')
            ->leftJoin('cauts.Media', 'cautms')
            ->leftJoin('se.division', 'div')
            ->leftJoin('se.originalDivision', 'odiv')
            ->where('se.id = :id')
            ->setParameter('id', $id)
        ;

        return $qb
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Add necessary where clauses to query builder.
     *
     * @param $qb
     * @param $conditions
     *
     * @return mixed
     */
    protected function addQueryConditionsForTypeAndSynthesis($qb, $type, $synthesis)
    {
        $qb = $this->addAndQueryCondition($qb, 'synthesis', $synthesis);

        switch ($type) {
            case 'new':
                $qb = $this->addAndQueryCondition($qb, 'archived', false);
                break;
            case 'unpublished':
                $qb = $this->addAndQueryCondition($qb, 'archived', true);
                $qb = $this->addAndQueryCondition($qb, 'published', false);
                break;
            case 'archived':
                $qb = $this->addAndQueryCondition($qb, 'archived', true);
                break;
            case 'published':
                $qb = $this->addAndQueryCondition($qb, 'archived', true);
                $qb = $this->addAndQueryCondition($qb, 'published', true);
                break;
            case 'notIgnored':
                $qb->andWhere('se.published = :published OR se.archived = :notArchived')
                    ->setParameter('published', true)
                    ->setParameter('notArchived', false)
                ;
                break;
            default:
                break;
        }

        return $qb;
    }

    protected function getOnClauseForChildren($type, $alias = 'c')
    {
        switch ($type) {
            case 'new':
                return $alias.'.archived = 0';
                break;
            case 'unpublished':
                return $alias.'.archived = 1 AND '.$alias.'.published = 0';
                break;
            case 'archived':
                return $alias.'.archived = 1';
                break;
            case 'published':
                return $alias.'.archived = 1 AND '.$alias.'.published = 1';
                break;
            case 'notIgnored':
                return $alias.'.published = 1 OR '.$alias.'.archived = 0';
                break;
            default:
                return '';
                break;
        }
    }

    protected function addAndQueryCondition($qb, $field, $value)
    {
        if (in_array($field, self::$allowedFields)) {
            if ($value === null) {
                return $qb
                    ->andWhere('se.'.$field.' IS NULL');
            }

            return $qb
                ->andWhere('se.'.$field.' = :'.$field)
                ->setParameter($field, $value);
        }

        return $qb;
    }

    // ************************ Methods for handling tree generation *************************

    /**
     * Returns tree of elements depending on conditions, parent and depth.
     * Tree can be decorated with options.
     */
    public function getFormattedTree($synthesis, $type, $parentId = null, $depth = null, $options = [])
    {
        $parent = $parentId ? $this->find($parentId) : null;
        $nodes = $this->getElementsHierarchy($synthesis, $type, $parent, $depth);

        return $this->buildTree($nodes, $options);
    }

    /**
     * Based on Gedmo\Tree\RepositoryUtils::buildTreeArray to
     * prevent element from being added to the tree if its parent is not in it.
     * Basically used to discard children of unpublished elements.
     */
    public function buildTreeArray(array $nodes)
    {
        $meta = $this->getClassMetadata();
        $level = 'level';
        $childrenIndex = 'children';
        $childrenCountIndex = 'childrenCount';
        $nestedTree = [];
        $l = 0;

        if (count($nodes) > 0) {
            // Node Stack. Used to help building the hierarchy
            $stack = [];
            // Array of ids, used to check if the element's parent is in the tree
            $idsStack = [];
            foreach ($nodes as $child) {
                $item = $child;
                $item[$childrenIndex] = [];
                if (array_key_exists($childrenCountIndex, $item)) {
                    $item[$childrenCountIndex] = intval($item[$childrenCountIndex]);
                }
                if (array_key_exists('publishedChildrenCount', $item)) {
                    $item['publishedChildrenCount'] = intval($item['publishedChildrenCount']);
                }
                if (array_key_exists('publishedParentChildrenCount', $item)) {
                    $item['publishedParentChildrenCount'] = intval($item['publishedParentChildrenCount']);
                }
                if (array_key_exists('childrenScore', $item)) {
                    $item['childrenScore'] = intval($item['childrenScore']);
                }
                if (array_key_exists('parentChildrenScore', $item)) {
                    $item['parentChildrenScore'] = intval($item['parentChildrenScore']);
                }
                if (array_key_exists('childrenElementsNb', $item)) {
                    $item['childrenElementsNb'] = intval($item['childrenElementsNb']);
                }
                if (array_key_exists('parentChildrenElementsNb', $item)) {
                    $item['parentChildrenElementsNb'] = intval($item['parentChildrenElementsNb']);
                }
                if (array_key_exists($childrenCountIndex, $item)) {
                    $item[$childrenCountIndex] = intval($item[$childrenCountIndex]);
                }
                if (array_key_exists('body', $item)) {
                    $item['body'] = html_entity_decode($item['body'], ENT_QUOTES);
                }
                // Number of stack items
                $l = count($stack);
                // Check if we're dealing with different levels
                while ($l > 0 && $stack[$l - 1][$level] >= $item[$level]) {
                    array_pop($stack);
                    --$l;
                }
                // Stack is empty (we are inspecting the root)
                if ($l == 0) {
                    // Assigning the root child
                    $i = count($nestedTree);
                    $nestedTree[$i] = $item;
                    $stack[] = &$nestedTree[$i];
                    $idsStack[] = $item['id'];
                } else {
                    // Check if item parent is present in the tree
                    $parentId = $this->extractParentIdFromPath($item['path']);
                    if (in_array($parentId, $idsStack)) {
                        // Add child to parent
                        $i = count($stack[$l - 1][$childrenIndex]);
                        $stack[$l - 1][$childrenIndex][$i] = $item;
                        $stack[] = &$stack[$l - 1][$childrenIndex][$i];
                        $idsStack[] = $item['id'];
                    }
                }
            }
        }

        return $nestedTree;
    }

    /**
     * Return id from a path in format Title 1-id-of-title-1|Title 2-id-of-title-2.
     */
    public function extractParentIdFromPath($path)
    {
        $splitted = explode('|', $path);
        if (count($splitted) < 2) {
            return;
        }
        $parent = explode('-', $splitted[count($splitted) - 2]);
        $parent = implode('-', array_splice($parent, count($parent) - 5, count($parent)));

        return $parent;
    }

    /**
     * Get children elements depending on conditions, parent and depth
     * Based on Gedmo\Tree\Entity\Repository\MaterializedPathRepository::getChildrenQueryBuilder to
     * - allow to add conditions (used in where clauses)
     * - allow to specify the depth of the query, to get only some levels of the tree
     * - return results as arrays with only a few fields
     * - add children count to each result.
     */
    public function getElementsHierarchy($synthesis, $type = 'published', $parent = null, $depth = null, $includeNode = false)
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $separator = addcslashes($config['path_separator'], '%');
        $path = $config['path'];
        $qb = $this->createQueryBuilder('se')
            ->select('se.id', 'se.level', 'se.path', 'se.displayType', 'se.title', 'se.body', 'se.description', 'se.published', 'COUNT(c.id) as childrenCount')
            ->leftJoin('se.children', 'c', 'WITH', $this->getOnClauseForChildren($type))
        ;
        if ($type === 'published') {
            $qb->addSelect(
                'se.votes',
                'se.publishedChildrenCount',
                'se.publishedParentChildrenCount',
                'se.childrenScore',
                'se.parentChildrenScore',
                '(se.publishedChildrenCount + se.childrenScore) as childrenElementsNb',
                '(se.publishedParentChildrenCount + se.parentChildrenScore) as parentChildrenElementsNb',
                'se.linkedDataUrl',
                'se.subtitle',
                'a.username as authorName',
                'se.linkedDataCreation'
            )->leftJoin('se.author', 'a');
        }
        $expr = '';
        $includeNodeExpr = '';

        if (is_object($parent) && $parent instanceof $meta->name) {
            $parent = new EntityWrapper($parent, $this->_em);
            $nodePath = $parent->getPropertyValue($path);
            $expr = $qb->expr()->andx()->add(
                $qb->expr()->like(
                    'se.'.$path,
                    $qb->expr()->literal(
                        $nodePath
                        .($config['path_ends_with_separator'] ? '' : $separator).'%'
                    )
                )
            );

            if ($includeNode) {
                $includeNodeExpr = $qb->expr()->eq('se.'.$path, $qb->expr()->literal($nodePath));
            } else {
                $expr->add($qb->expr()->neq('se.'.$path, $qb->expr()->literal($nodePath)));
            }

            if ($depth && $depth > 0) {
                $expr->add(
                    $qb->expr()->andx(
                        $qb->expr()->gte('se.'.$config['level'], $qb->expr()->literal($parent->getPropertyValue($config['level']))),
                        $qb->expr()->lte('se.'.$config['level'], $qb->expr()->literal($parent->getPropertyValue($config['level']) + $depth))
                    )
                );
            }
        } elseif ($depth && $depth > 0) {
            $expr = $qb->expr()->lte('se.'.$config['level'], $qb->expr()->literal($depth - 1));
        }

        if ($expr) {
            $qb->where('('.$expr.')');
        }

        $qb = $this->addQueryConditionsForTypeAndSynthesis($qb, $type, $synthesis);

        if ($includeNodeExpr) {
            $qb->orWhere('('.$includeNodeExpr.')');
        }

        $orderByField = 'se.'.$config['path'];
        $orderByDir = 'asc';
        $qb->orderBy($orderByField, $orderByDir);

        $qb->groupBy('se.id');

        return $qb
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
