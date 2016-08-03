<?php

namespace DvsaCommon\ApiClient\Statistics\TesterPerformance;

use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\AuthorisedExaminerSitesPerformanceDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class AuthorisedExaminerSitePerformanceApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param int $organisationId
     * @param $page
     * @param $itemsPerPage
     * @return AuthorisedExaminerSitesPerformanceDto
     */
    public function getData($organisationId, $page, $itemsPerPage)
    {
        return $this->getSingle(
            AuthorisedExaminerSitesPerformanceDto::class,
            sprintf('statistic/tester-performance/authorised-examiner/%s', $organisationId),
            $this->getParams($page, $itemsPerPage)
        );
    }

    private function getParams($page, $itemsPerPage)
    {
        return [
            'page'          => $page,
            'itemsPerPage'  => $itemsPerPage,
        ];
    }
}
