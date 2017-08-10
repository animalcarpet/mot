<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper;

use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\RiskAssessmentDto;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaEntities\Entity\EnforcementSiteAssessment;

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
        if ($site["current_assessment"] === null) {
            return null;
        }

        /** @var EnforcementSiteAssessment $currentAssessment */
        $currentAssessment = $site['current_assessment'];
        $riskAssessmentDto = new RiskAssessmentDto();
        $riskAssessmentDto->setScore($currentAssessment->getSiteAssessmentScore());
        $riskAssessmentDto->setDate($currentAssessment->getVisitDate());

        return $riskAssessmentDto;
    }

    private function extractPreviousAssessment(array $site)
    {
        if ($site["previous_assessment"] === null) {
            return null;
        }

        /** @var EnforcementSiteAssessment $previousAssessment */
        $previousAssessment = $site['previous_assessment'];
        $riskAssessmentDto = new RiskAssessmentDto();
        $riskAssessmentDto->setScore($previousAssessment->getSiteAssessmentScore());
        $riskAssessmentDto->setDate($previousAssessment->getVisitDate());

        return $riskAssessmentDto;
    }
}
