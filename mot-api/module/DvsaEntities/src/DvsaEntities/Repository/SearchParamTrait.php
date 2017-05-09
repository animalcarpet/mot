<?php

namespace DvsaEntities\Repository;

trait SearchParamTrait
{
    protected $searchDql = null;
    protected $searchCountDql = null;
    protected $totalResultCount = 0;

    /**
     * @param null $searchCountDql
     *
     * @return $this
     */
    public function setSearchCountDql($searchCountDql)
    {
        $this->searchCountDql = $searchCountDql;

        return $this;
    }

    public function getSearchCountDql()
    {
        return $this->searchCountDql;
    }

    /**
     * @param null $searchDql
     *
     * @return $this
     */
    public function setSearchDql($searchDql)
    {
        $this->searchDql = $searchDql;

        return $this;
    }

    public function getSearchDql()
    {
        return $this->searchDql;
    }

    /**
     * @return int
     */
    public function getTotalResultCount()
    {
        return $this->totalResultCount;
    }
}
