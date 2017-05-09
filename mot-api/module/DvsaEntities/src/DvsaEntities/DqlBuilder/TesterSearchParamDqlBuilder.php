<?php

namespace DvsaEntities\DqlBuilder;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;

/**
 * Class TesterSearchParamDqlBuilder.
 */
class TesterSearchParamDqlBuilder extends SearchParamDqlBuilder
{
    const SORT_BY_ID = 0;
    const SORT_BY_USERNAME = 1;

    protected $orderByTable = 'tester';
    protected $searchWords = [];

    /**
     * Provides an opportunity to initialize and values before processing.
     *
     * @return $this
     */
    public function initialize()
    {
        $search = trim($this->params->getSearch());
        $this->searchWords = strlen($search) > 0 ? explode(' ', strtoupper($search)) : null;
        // todo: make this the default action in the child class.
        return $this;
    }

    /**
     * Build the Dql from the params.
     *
     * @param bool $totalCount
     *
     * @return mixed|void
     */
    protected function buildDql($totalCount = false)
    {
        $dql = [];
        $filters = [];

        $dql[] = $this->generateDqlHeader($totalCount);

        if ($this->searchWords) {
            $this->addFiltersByValues($filters, $this->searchWords, 'tester.username = :TESTER_USERNAME', 'OR');
        }

        $dql[] = count($filters) ? implode(' OR ', $filters) : '1';

        $this->generateDqlFooter($totalCount, $dql);
    }

    /**
     * Build the Query and add any parameters.
     *
     * @param bool $totalCount
     *
     * @return mixed|void
     */
    protected function buildQuery($totalCount = false)
    {
        $query = $this->createQuery($totalCount);
        $searchLength = strlen($this->params->getSearch());

        if ($searchLength) {
            $this->addParametersByValues($query, $this->searchWords, 'TESTER_USERNAME');
        }

        $query->setParameter(
            'statuses', [
                AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
                AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
                AuthorisationForTestingMotStatusCode::QUALIFIED,
                AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED,
                AuthorisationForTestingMotStatusCode::SUSPENDED,
            ]
        );

        $this->assignQuery($totalCount, $query);
    }

    /**
     * Build the correct DQL header depending on the current settings.
     *
     * @param $totalCount
     *
     * @return string
     */
    protected function generateDqlHeader($totalCount)
    {
        $select = $totalCount ? 'count(DISTINCT tester)' : ' DISTINCT tester';

        return 'SELECT '.$select.' FROM
                     DvsaEntities\Entity\AuthorisationForTestingMot auth,
                     DvsaEntities\Entity\AuthorisationForTestingMotStatus status,
                     DvsaEntities\Entity\Person tester
                     WHERE auth.person = tester.id
                     AND auth.status = status.id
                     AND status.code IN (:statuses)
                     AND ';
    }

    /**
     * Build the correct DQL footer depending on the current settings.
     *
     * @param $totalCount
     * @param $dql
     */
    protected function generateDqlFooter($totalCount, $dql)
    {
        if ($totalCount) {
            $this->searchCountDql = implode(' ', $dql);
        } else {
            $dql[] = "ORDER BY {$this->getOrderByTable()}.{$this->params->getSortColumnName()} ".
                $this->params->getSortDirection();
            $this->searchDql = implode(' ', $dql);
        }
    }

    /**
     * Returns the current table to order by. Useful in multi join searches.
     *
     * @return string
     */
    public function getOrderByTable()
    {
        return $this->orderByTable;
    }
}
