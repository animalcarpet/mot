<?php

namespace DvsaEntities\DqlBuilder;

use DvsaCommon\Domain\MotTestType;

/**
 * Class SiteSlotUsageParamDqlBuilder.
 */
class SiteSlotUsageParamDqlBuilder extends SearchParamDqlBuilder
{
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

        $filters[] = '(ts.name = \'PASSED\')';
        $filters[] = '(tt.code IN (:TEST_TYPES))';

        $vtsId = $this->params->getVtsId();
        $this->addFiltersByValues($filters, [$vtsId], 't.vehicleTestingStation = :SITE_ID', 'AND');

        if ($this->params->getDateFrom() != null) {
            $this->addFiltersByValues($filters, [$this->params->getDateFrom()], 't.completedDate >= :DATE_FROM', '%s');
        }

        if ($this->params->getDateTo() != null) {
            $this->addFiltersByValues($filters, [$this->params->getDateTo()], 't.completedDate <= :DATE_TO', '%s');
        }

        $dql[] = count($filters) ? implode(' AND ', $filters) : '1';

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

        $this->addParametersByValues($query, [$this->params->getVtsId()], 'SITE_ID');
        $query->setParameter('TEST_TYPES', MotTestType::getSlotConsumingTypes());

        if ($this->params->getDateFrom() != null) {
            $query->setParameter('DATE_FROM', $this->params->getDateFrom());
        }

        if ($this->params->getDateTo() != null) {
            $query->setParameter('DATE_TO', $this->params->getDateTo());
        }

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
        $select = $totalCount ? 'count(distinct t)' : 't';

        return 'SELECT '.$select.' from DvsaEntities\Entity\MotTest t'
            .' LEFT JOIN DvsaEntities\Entity\Person p WITH t.tester = p.id'
            .' LEFT JOIN DvsaEntities\Entity\Vehicle v WITH v.id = t.vehicle'
            .' LEFT JOIN DvsaEntities\Entity\MotTestType tt WITH t.motTestType = tt.id'
            .' LEFT JOIN DvsaEntities\Entity\MotTestStatus ts WITH t.status = ts.id'
            .' WHERE';
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
            $dql[] = "ORDER BY {$this->getOrderForDql()}";
            $this->searchDql = implode(' ', $dql);
        }
    }

    public function getOrderForDql()
    {
        $name = $this->params->getSortName();

        switch ($name) {
            case 'date':
            default:
                $name = 't.completedDate '.$this->params->getSortDirection();
                break;
            case 'tester':
                $name = 'p.firstName '.$this->params->getSortDirection().', p.familyName';
                break;
            case 'vrn':
                $name = 'v.registration '.$this->params->getSortDirection();
                break;
        //@codeCoverageIgnoreStart
        }
        //@codeCoverageIgnoreEnd

        return $name;
    }
}
