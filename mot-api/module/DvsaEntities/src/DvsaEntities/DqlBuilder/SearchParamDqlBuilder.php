<?php

namespace DvsaEntities\DqlBuilder;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Model\SearchParam;

/**
 * Class SearchParamDqlBuilder.
 */
abstract class SearchParamDqlBuilder
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * The standard search DQL statement.
     *
     * @var null
     */
    protected $searchDql = null;
    /**
     * The count search DQL statement.
     *
     * @var null
     */
    protected $searchCountDql = null;
    /**
     * The standard search query object.
     *
     * @var null
     */
    protected $searchQuery;
    /**
     * The count search query object.
     *
     * @var null
     */
    protected $searchCountQuery = null;

    /**
     * @var SearchParam
     */
    protected $params = null;

    /**
     * @param EntityManager $em
     * @param SearchParam   $params
     */
    public function __construct(EntityManager $em, SearchParam $params)
    {
        $this->em = $em;
        $this->params = $params;
    }

    /**
     * @return SearchParam
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    public function getSearchDql()
    {
        return $this->searchDql;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    public function getSearchCountDql()
    {
        return $this->searchCountDql;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function getSearchCountQuery()
    {
        return $this->searchCountQuery;
    }

    /**
     * Generate both the DQL and the Query objects.
     */
    public function generate()
    {
        $this->initialize();
        $this->buildDql();
        $this->buildDql(true);
        $this->buildQuery();
        $this->buildQuery(true);

        return $this;
    }

    /**
     * Provides an opportunity to initialize and values before processing.
     *
     * @return $this
     */
    public function initialize()
    {
        return $this;
    }

    /**
     * Build the DQL from the params.
     *
     * @param bool $totalCount
     *
     * @return mixed
     */
    abstract protected function buildDql($totalCount = false);

    /**
     * Build the Query from the params.
     *
     * @param bool $totalCount
     *
     * @return mixed
     */
    abstract protected function buildQuery($totalCount = false);

    /**
     * Add any filter elements by either ORing or ANDing them together..
     *
     * Example:
     * $this->addFiltersByValues($filters, $types, "vts.type = :TYPE_%d", "OR");
     * $this->addFiltersByValues($filters, $searchWords, "vts.search LIKE :WORD_%d", "AND");
     *
     * @param $filters
     * @param $values
     * @param $dql
     * @param $joinType
     */
    protected function addFiltersByValues(&$filters, $values, $dql, $joinType)
    {
        if (count($values)) {
            $parts = [];
            for ($i = 0; $i < count($values); ++$i) {
                $parts[] = sprintf($dql, $i);
            }
            $filters[] = '('.implode(' '.$joinType.' ', $parts).')';
        }
    }

    /**
     * Add parameters to the passed query object.
     *
     * Examples:
     *
     * $this->addParametersByValues($query, $types, 'TYPE_%d');
     * $this->addParametersByValues($query, $searchWords, 'WORD_%d', '%%%s%%');
     *
     * @param \Doctrine\ORM\Query $query
     * @param array               $values
     * @param string              $name
     * @param string              $format
     */
    protected function addParametersByValues(&$query, $values, $name, $format = '%s')
    {
        if ($values) {
            $ctr = 0;
            foreach ($values as $value) {
                $query->setParameter(
                    sprintf($name, $ctr),
                    sprintf($format, $value)
                );
                ++$ctr;
            }
        }
    }

    /**
     * Initialize the Query object using the correct DQL.
     *
     * @param $totalCount
     *
     * @return \Doctrine\ORM\Query
     */
    protected function createQuery($totalCount)
    {
        if ($totalCount) {
            return  $this->em->createQuery($this->getSearchCountDql());
        }

        return $this->em->createQuery($this->getSearchDql());
    }

    /**
     * Assign to the correct Query object.
     *
     * @param bool                $totalCount
     * @param \Doctrine\ORM\Query $query
     */
    protected function assignQuery($totalCount, $query)
    {
        if ($totalCount) {
            $this->searchCountQuery = $query;
        } else {
            $query->setFirstResult($this->params->getStart());

            $rowCount = $this->params->getRowCount();
            if ($rowCount > 0) {
                $query->setMaxResults($rowCount);
            }

            $this->searchQuery = $query;
        }
    }
}
