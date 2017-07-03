<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\AuthorisedExaminer\Mapper;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper\AuthorisedExaminerSiteMapper;
use DvsaCommon\ApiClient\Statistics\AePerformance\Dto\SiteDto;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;

class AuthorisedExaminerSiteMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testDtoMapping()
    {
        $siteEntity = $this->getSiteEntity();

        $mapper = new AuthorisedExaminerSiteMapper();
        $siteDto = $mapper->toDto($siteEntity);
        $this->assertDtoFieldsEqualsEntityFields($siteEntity, $siteDto);
    }

    private function getSiteEntity()
    {
        $siteEntity = new Site();
        $contactDetail = new ContactDetail();
        $address = new Address();
        $siteContactType = new SiteContactType();
        $contactDetail->setAddress($address
            ->setAddressLine1('address1')
            ->setAddressLine2('address2')
            ->setAddressLine3('address3')
            ->setCountry('country')
            ->setPostcode('postcode')
            ->setTown('town')
        );

        $siteEntity->setName('siteName')
            ->setId(1)
            ->setSiteNumber('siteNumber')
            ->setLastSiteAssessment((new EnforcementSiteAssessment())->setSiteAssessmentScore(100.03))
            ->setContact(
                $contactDetail, $siteContactType->setCode(SiteContactTypeCode::BUSINESS)
            )
            ->addRiskAssessment((new EnforcementSiteAssessment())->setSiteAssessmentScore(100.03))
        ;

        return $siteEntity;
    }

    private function assertDtoFieldsEqualsEntityFields(Site $siteEntity, SiteDto $siteDto)
    {
        $address = $siteEntity->getBusinessContact()->getDetails()->getAddress();
        /** @var EnforcementSiteAssessment $currentRiskAssessment */
        $currentRiskAssessment = $siteEntity->getRiskAssessments()->get(0);
        $this->assertEquals(
            [
                $siteEntity->getName(),
                $siteEntity->getSiteNumber(),
                $siteEntity->getId(),
                $siteEntity->getLastSiteAssessment()->getSiteAssessmentScore(),
                $currentRiskAssessment->getSiteAssessmentScore(),
                $currentRiskAssessment->getVisitDate(),
                $address->getAddressLine1(),
                $address->getAddressLine2(),
                $address->getAddressLine3(),
                $address->getAddressLine4(),
                $address->getCountry(),
                $address->getTown(),
                $address->getPostcode(),
            ],
            [
                $siteDto->getName(),
                $siteDto->getNumber(),
                $siteDto->getId(),
                $siteDto->getCurrentRiskAssessment()->getScore(),
                $siteDto->getCurrentRiskAssessment()->getScore(),
                $siteDto->getCurrentRiskAssessment()->getDate(),
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
