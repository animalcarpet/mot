<?php

namespace DvsaEntities\DqlBuilder;

use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaEntities\DqlBuilder\SearchParam\OrgSlotUsageParam;
use DvsaEntities\Entity\SiteContactType;

/**
 * class SlotUsageParamDqlBuilder.
 */
class SlotUsageParamDqlBuilder extends SearchParamDqlBuilder
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
        $filters[] = '(tt.code IN (:SLOT_TEST_TYPES))';

        $orgId = $this->params->getOrganisationId();
        $this->addFiltersByValues($filters, [$orgId], 's.organisation = :ORG_ID', 'AND');

        if ($this->params->getDateFrom() != null) {
            $this->addFiltersByValues($filters, [$this->params->getDateFrom()], 't.completedDate >= :DATE_FROM', '%s');
        }

        if ($this->params->getDateTo() != null) {
            $this->addFiltersByValues($filters, [$this->params->getDateTo()], 't.completedDate <= :DATE_TO', '%s');
        }

        if (strlen($this->params->getSearchText())) {
            $this->addFiltersByValues(
                $filters,
                [$this->params->getSearchText()],
                's.siteNumber LIKE :SEARCH_TEXT OR
                 s.name liKE :SEARCH_TEXT OR
                 a.postcode LIKE :SEARCH_TEXT',
                '%s'
            );
        }

        $dql[] = count($filters) ? implode(' AND ', $filters) : '1';

        if (!$totalCount) {
            $dql[] = 'GROUP BY s, o';
        }

        $dql[] = 'HAVING count(t.id) > 0';

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

        $this->addParametersByValues($query, [$this->params->getOrganisationId()], 'ORG_ID');

        $query->setParameter('SLOT_TEST_TYPES', MotTestType::getSlotConsumingTypes());

        if ($this->params->getDateFrom() != null) {
            $query->setParameter('DATE_FROM', $this->params->getDateFrom());
        }

        if ($this->params->getDateTo() != null) {
            $query->setParameter('DATE_TO', $this->params->getDateTo());
        }

        if (strlen($this->params->getSearchText())) {
            $query->setParameter('SEARCH_TEXT', '%'.$this->params->getSearchText().'%');
        }

        $query->setParameter('contactType', SiteContactTypeCode::BUSINESS);

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
        $select = $totalCount ? 'count(distinct s), count(t.id)' : 's, o, count(t.id) usage';

        return 'SELECT '.$select.' from DvsaEntities\Entity\Site s '
            .' INNER JOIN s.tests t '
            .' LEFT JOIN s.contacts sc WITH sc.type = (SELECT sct.id FROM '.SiteContactType::class.' sct WHERE sct.code = :contactType )'
            .' LEFT JOIN sc.contactDetail cd '
            .' LEFT JOIN cd.address a '
            .' LEFT JOIN t.motTestType tt'
            .' LEFT JOIN t.status ts'
            .' LEFT JOIN s.organisation o'
            .' WHERE ';
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
            $dql[] = "ORDER BY {$this->getOrderForDql()} ".
                $this->params->getSortDirection();
            $this->searchDql = implode(' ', $dql);
        }
    }

    public function getOrderForDql()
    {
        $name = $this->params->getSortName();

        $entityAliasPart = '';

        if (in_array(
            $name,
            [
                OrgSlotUsageParam::SORT_COL_NAME,
                OrgSlotUsageParam::SORT_COL_SITE_NUMBER,
            ]
        ) !== false) {
            $entityAliasPart = 's.';
        }

        return $entityAliasPart.$name;
    }
}
