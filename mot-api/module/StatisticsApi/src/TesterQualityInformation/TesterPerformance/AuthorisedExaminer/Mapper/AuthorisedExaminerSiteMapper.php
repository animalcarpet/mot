<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper;

use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\RiskAssessmentDto;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\Site;
use DvsaEntities\Mapper\AddressMapper;

class AuthorisedExaminerSiteMapper implements AutoWireableInterface
{
    const CURRENT_ASSESSMENT_INDEX = 0;
    const PREVIOUS_ASSESSMENT_INDEX = 1;

    /**
     * @var AddressMapper
     */
    private $addressMapper;

    public function __construct()
    {
        $this->addressMapper = new AddressMapper();
    }

    /**
     * @param Site $site
     *
     * @return SiteDto
     */
    public function toDto(Site $site)
    {
        $siteAddress = $site->getAddress();
        $siteDto = (new SiteDto())
            ->setId($site->getId())
            ->setName($site->getName())
            ->setNumber($site->getSiteNumber())
            ->setCurrentRiskAssessment($this->extractCurrentAssessment($site))
            ->setPreviousRiskAssessment($this->extractPreviousAssessment($site))
            ->setAddress($this->addressMapper->toDto($siteAddress));

        return $siteDto;
    }

    private function extractCurrentAssessment(Site $site)
    {
        return $this->extractAssessment($site, self::CURRENT_ASSESSMENT_INDEX);
    }

    private function extractPreviousAssessment(Site $site)
    {
        return $this->extractAssessment($site, self::PREVIOUS_ASSESSMENT_INDEX);
    }

    private function extractAssessment(Site $site, $index)
    {
        $riskAssessmentDto = null;
        if ($site->getRiskAssessments()->offsetExists($index)) {
            /** @var EnforcementSiteAssessment $assessment */
            $assessment = $site->getRiskAssessments()->get($index);
            $riskAssessmentDto = new RiskAssessmentDto();
            $riskAssessmentDto->setScore($assessment->getSiteAssessmentScore());
            $riskAssessmentDto->setDate($assessment->getVisitDate());
        }

        return $riskAssessmentDto;
    }
}
