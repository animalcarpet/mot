<?php

namespace DvsaMotApi\Model\DqlBuilder;

use DvsaEntities\DqlBuilder\SearchParamDqlBuilder;

/**
 * Class VehicleTestingStationSearchParamDqlBuilder.
 */
class VehicleTestingStationElasticSearchParamBuilder extends SearchParamDqlBuilder
{
    protected $es;

    protected $searchMode = self::SEARCH_MODE_EFFICIENT;
    protected $orderByTableName = 'vts';
    protected $searchWords = [];

    const SEARCH_MODE_EFFICIENT = 'EFFICIENT';
    const SEARCH_MODE_JOINED = 'JOINED';

    const SORT_BY_SEARCH = 0;
    const SORT_BY_NAME = 1;
    const SORT_BY_ADDRESS = 2;
    const SORT_BY_TOWN = 3;
    const SORT_BY_POSTCODE = 4;
    const SORT_BY_TELEPHONE = 5;
    const SORT_BY_ROLE = 6;
    const SORT_BY_TYPE = 7;
    const SORT_BY_STATUS = 8;

    /**
     * Provides an opportunity to initialize and values before processing.
     *
     * @return $this
     */
    public function initialize()
    {
        $params['hosts'] = array('80.240.131.19:9200');
        $this->es = new Elasticsearch\Client($params);

        $search = trim($this->params->getChomped());
        $this->searchWords = strlen($search) > 0 ? explode(' ', strtoupper($search)) : null;

        $this->calculateSearchMode();

        return $this;
    }

    /**
     * Build the query from the params.
     */
    protected function buildDql($totalCount = false)
    {
        $dql = [];
        $filters = [];

        $dql[] = $this->generateDqlHeader($totalCount);

        $this->addFiltersByValues($filters, $this->params->getTypes(), 'vts.type = :TYPE_%d', 'OR');
        $this->addFiltersByValues($filters, $this->params->getStatuses(), 'vts.status = :STATUS_%d', 'OR');
        $this->addFiltersByValues($filters, $this->params->getVehicleClasses(), 'vts.roles LIKE :CLASS_%d', 'AND');
        $this->addFiltersByValues($filters, $this->searchWords, 'vts.search LIKE :WORD_%d', 'AND');

        $dql[] = count($filters) ? implode(' AND ', $filters) : '1';

        $this->generateDqlFooter($totalCount, $dql);
    }

    protected function buildQuery($totalCount = false)
    {
        $query = $this->createQuery($totalCount);

        $this->addParametersByValues($query, $this->params->getTypes(), 'TYPE_%d');
        $this->addParametersByValues($query, $this->params->getStatuses(), 'STATUS_%d');
        $this->addParametersByValues($query, $this->params->getVehicleClasses(), 'CLASS_%d', '%%%d%%');
        $this->addParametersByValues($query, $this->searchWords, 'WORD_%d', '%%%s%%');

        $this->assignQuery($totalCount, $query);
    }

    /**
     * @return string
     */
    public function getSearchMode()
    {
        return $this->searchMode;
    }

    /**
     * $columnMappings = [.
     */
    protected function calculateSearchMode()
    {
        $this->searchMode = self::SEARCH_MODE_EFFICIENT;
        if ($this->params->getSortColumnId() == self::SORT_BY_NAME
            || $this->params->getSortColumnId() == self::SORT_BY_ADDRESS
            || $this->params->getSortColumnId() == self::SORT_BY_TOWN
            || $this->params->getSortColumnId() == self::SORT_BY_POSTCODE
        ) {
            $this->searchMode = self::SEARCH_MODE_JOINED;
        }
    }

    /**
     * Depending on the current settings; Build the correct DQL header.
     *
     * @param $totalCount
     *
     * @return string
     */
    protected function generateDqlHeader($totalCount)
    {
        if ($totalCount) {
            return 'SELECT count(vts.id) from DvsaMotApi\Entity\VehicleTestingStationSearch vts WHERE';
        }

        if ($this->searchMode == self::SEARCH_MODE_EFFICIENT) {
            return 'SELECT vts from DvsaMotApi\Entity\VehicleTestingStationSearch vts WHERE';
        }

        if ($this->params->getSortColumnId() == self::SORT_BY_NAME) {
            $this->orderByTableName = 'vts_full';

            return 'SELECT vts from DvsaMotApi\Entity\VehicleTestingStationSearch vts '.
            'LEFT JOIN DvsaMotApi\Entity\VehicleTestingStation vts_full WITH '.
            'vts.vehicleTestingStation = vts_full.id '.
            'WHERE';
        }

        $this->orderByTableName = 'vts_address';

        return 'SELECT vts from DvsaMotApi\Entity\VehicleTestingStationSearch vts '.
                    'LEFT JOIN DvsaMotApi\Entity\VehicleTestingStation vts_full WITH '.
                    'vts.vehicleTestingStation = vts_full.id '.
                    'LEFT JOIN DvsaMotApi\Entity\VtsAddress vts_address WITH '.
                    'vts_full.address = vts_address.id '.
                    'WHERE';
    }

    /**
     * @param $totalCount
     * @param $dql
     */
    protected function generateDqlFooter($totalCount, $dql)
    {
        if ($totalCount) {
            $this->searchCountDql = implode(' ', $dql);
        } else {
            $dql[] = "ORDER BY {$this->orderByTableName}.{$this->params->getSortColumnName()} ".
                $this->params->getSortDirection();

            $this->searchDql = implode(' ', $dql);
        }
    }
}
