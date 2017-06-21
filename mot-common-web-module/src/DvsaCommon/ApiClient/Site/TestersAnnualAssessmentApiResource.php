<?php


namespace DvsaCommon\ApiClient\Site;


use DvsaCommon\ApiClient\Site\Dto\TestersAnnualAssessmentDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class TestersAnnualAssessmentApiResource extends AbstractApiResource implements AutoWireableInterface
{
    const PATH_TESTERS_ANNUAL_ASSESSMENT = 'vehicle-testing-station/%d/testers-annual-assessment';

    /**
     * @param int $siteId
     * @return TestersAnnualAssessmentDto
     */
    public function getTestersAnnualAssessmentForSite($siteId)
    {
        return $this->getSingle(
            TestersAnnualAssessmentDto::class,
            sprintf(self::PATH_TESTERS_ANNUAL_ASSESSMENT, $siteId));
    }
}