<?php

namespace Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper;

use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Entity\Site;
use DvsaEntities\Mapper\AddressMapper;

class AuthorisedExaminerSiteMapper implements AutoWireableInterface
{
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
            ->setRiskAssessmentScore($site->getLastSiteAssessment() ? $site->getLastSiteAssessment()->getSiteAssessmentScore() : null)
            ->setAddress($this->addressMapper->toDto($siteAddress));

        return $siteDto;
    }
}
