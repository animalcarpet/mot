<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\AuthorisedExaminer\Mapper;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper\AuthorisedExaminerSiteMapper;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;

class AuthorisedExaminerSiteMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testDtoMapping()
    {
        $sites = $this->getSites();

        $mapper = new AuthorisedExaminerSiteMapper();

        foreach ($sites as $site) {
            $siteDto = $mapper->toDto($site);
            $this->assertDtoFieldsEqualsEntityFields($site, $siteDto);
        }
    }

    protected function getSites()
    {
        $keys = [
            "id", "name", "site_number",
            "current_visit_date", "current_score",
            "previous_visit_date", "previous_score",
            "address_line_1", "address_line_2", "address_line_3", "address_line_4", "town", "postcode", "country"
        ];

        $site1 = array_combine($keys,
            [
                1, "name 1", "number 1",
                "20017-07-01", 90,
                "2017-05-05", 144,
                "address line 1", "address line 2", "address line 3", "address line 4", "Bristol", "BL 10NS", "GB"
            ]
        );

        $site2 = array_combine($keys,
            [
                2, "name 2", "number 2",
                "20017-07-01", 90,
                null, null,
                "address line 1", "address line 2", "address line 3", "address line 4", "Bristol", "BL 10NS", "GB"
            ]
        );

        $site3 = array_combine($keys,
            [
                3, "name 3", "number 3",
                null, null,
                null, null,
                "address line 1", "address line 2", "address line 3", "address line 4", "Bristol", "BL 10NS", "GB"
            ]
        );

        return [$site1, $site2, $site3];
    }

    private function assertDtoFieldsEqualsEntityFields(array $site, SiteDto $siteDto)
    {
        $this->assertEquals(
            [
                $site["name"],
                $site["site_number"],
                $site["id"],
                $site["current_score"],
                ($site["current_visit_date"] !== null)? new \DateTime($site["current_visit_date"]): null,
                $site["previous_score"],
                ($site["previous_visit_date"] !== null)? new \DateTime($site["previous_visit_date"]): null,
                $site["address_line_1"],
                $site["address_line_2"],
                $site["address_line_3"],
                $site["address_line_4"],
                $site["country"],
                $site["town"],
                $site["postcode"],
            ],
            [
                $siteDto->getName(),
                $siteDto->getNumber(),
                $siteDto->getId(),
                ($siteDto->getCurrentRiskAssessment())? $siteDto->getCurrentRiskAssessment()->getScore() : null,
                ($siteDto->getCurrentRiskAssessment())? $siteDto->getCurrentRiskAssessment()->getDate() : null,
                ($siteDto->getPreviousRiskAssessment())? $siteDto->getPreviousRiskAssessment()->getScore() : null,
                ($siteDto->getPreviousRiskAssessment())? $siteDto->getPreviousRiskAssessment()->getDate() : null,
                $siteDto->getAddress()->getAddressLine1(),
                $siteDto->getAddress()->getAddressLine2(),
                $siteDto->getAddress()->getAddressLine3(),
                $siteDto->getAddress()->getAddressLine4(),
                $siteDto->getAddress()->getCountry(),
                $siteDto->getAddress()->getTown(),
                $siteDto->getAddress()->getPostcode(),
            ]
        );
    }
}
