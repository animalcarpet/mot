<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Search\MotTestSearchParamsDto;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;
use DvsaCommon\UrlBuilder\TesterUrlBuilder;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Provide functionality for receiving Mot Test Log data from api.
 */
class MotTestLogMapper extends DtoMapper
{
    /**
     * @param int $orgId
     *
     * @return \DvsaCommon\Dto\Search\MotTestSearchParamsDto
     */
    public function getSummary($orgId)
    {
        $apiUrl = AuthorisedExaminerUrlBuilder::motTestLogSummary($orgId);

        return $this->get($apiUrl);
    }

    /**
     * @param $siteId
     *
     * @return \DvsaCommon\Dto\Search\MotTestSearchParamsDto
     */
    public function getSiteSummary($siteId)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::motTestLogSummary($siteId);

        return $this->get($apiUrl);
    }

    /**
     * @param $testerId
     *
     * @return MotTestSearchParamsDto
     */
    public function getTesterSummary($testerId)
    {
        $apiUrl = TesterUrlBuilder::motTestLogSummary($testerId);

        return $this->get($apiUrl);
    }

    /**
     * @param int                    $orgId
     * @param MotTestSearchParamsDto $searchParams
     *
     * @return \DvsaCommon\Dto\Search\MotTestSearchParamsDto
     */
    public function getData($orgId, $data)
    {
        $apiUrl = AuthorisedExaminerUrlBuilder::motTestLog($orgId)->toString();

        return $this->post($apiUrl, DtoHydrator::dtoToJson($data));
    }

    /**
     * @param int                    $siteId
     * @param MotTestSearchParamsDto $searchParams
     *
     * @return \DvsaCommon\Dto\Search\MotTestSearchParamsDto
     */
    public function getSiteData($siteId, $searchParams)
    {
        $apiUrl = VehicleTestingStationUrlBuilder::motTestLog($siteId)->toString();

        return $this->post($apiUrl, DtoHydrator::dtoToJson($searchParams));
    }

    /**
     * @param int $testerId
     * @param $data
     *
     * @return SearchResultDto
     */
    public function getTesterData($testerId, $data)
    {
        $apiUrl = TesterUrlBuilder::motTestLog($testerId);

        return $this->post($apiUrl, DtoHydrator::dtoToJson($data));
    }
}
