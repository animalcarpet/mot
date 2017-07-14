<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper;

use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\RiskAssessmentDto;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Dto\Contact\AddressDto;

class AuthorisedExaminerSiteMapper implements AutoWireableInterface
{
    /**
     * @param array $site
     *
     * @return SiteDto
     */
    public function toDto(array $site)
    {
        $addressDto = new AddressDto();
        $addressDto
            ->setTown($site["town"])
            ->setPostcode($site["postcode"])
            ->setCountry($site["country"])
            ->setAddressLine1($site["address_line_1"])
            ->setAddressLine2($site["address_line_2"])
            ->setAddressLine3($site["address_line_3"])
            ->setAddressLine4($site["address_line_4"])
            ;

        $siteDto = (new SiteDto())
            ->setId($site["id"])
            ->setName($site["name"])
            ->setNumber($site["site_number"])
            ->setCurrentRiskAssessment($this->extractCurrentAssessment($site))
            ->setPreviousRiskAssessment($this->extractPreviousAssessment($site))
            ->setAddress($addressDto);

        return $siteDto;
    }

    private function extractCurrentAssessment(array $site)
    {
        if ($site["current_score"] === null) {
            return null;
        }

        $riskAssessmentDto = new RiskAssessmentDto();
        $riskAssessmentDto->setScore($site["current_score"]);
        $riskAssessmentDto->setDate(new \DateTime($site["current_visit_date"]));

        return $riskAssessmentDto;
    }

    private function extractPreviousAssessment(array $site)
    {
        if ($site["previous_score"] === null) {
            return null;
        }

        $riskAssessmentDto = new RiskAssessmentDto();
        $riskAssessmentDto->setScore($site["previous_score"]);
        $riskAssessmentDto->setDate(new \DateTime($site["previous_visit_date"]));

        return $riskAssessmentDto;
    }
}
