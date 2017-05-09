<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\AuthorisedExaminer\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper\AuthorisedExaminerSiteMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\SiteRepository;

class AuthorisedExaminerStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    const AE_ID = 1;
    /** @var \PHPUnit_Framework_MockObject_MockObject | \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Service\AuthorisedExaminerStatisticsService */
    protected $authorisedExaminerStatisticService;
    /** @var \PHPUnit_Framework_MockObject_MockObject | SiteRepository */
    private $siteRepository;
    /** @var \PHPUnit_Framework_MockObject_MockObject | MotAuthorisationServiceInterface */
    private $authorisationService;
    /** @var \PHPUnit_Framework_MockObject_MockObject | AuthorisedExaminerSiteMapper */
    private $authorisedExaminerSiteMapper;
    /** @var \PHPUnit_Framework_MockObject_MockObject | OrganisationRepository */
    private $organisationRepository;

    public function setUp(
    ) {
        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_VIEW_TEST_QUALITY, self::AE_ID);
        $this->organisationRepository = XMock::of(OrganisationRepository::class);
        $this->authorisedExaminerSiteMapper = XMock::of(AuthorisedExaminerSiteMapper::class);
        $this->siteRepository = XMock::of(SiteRepository::class);
        $this->authorisedExaminerStatisticService = new \Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Service\AuthorisedExaminerStatisticsService(
            $this->siteRepository, $this->organisationRepository, $this->authorisationService, $this->authorisedExaminerSiteMapper
        );
    }

    public function testGetList()
    {
        $this->siteRepository->expects($this->once())
            ->method('getSitesTestQualityForOrganisationId')
            ->willReturn([
                $this->getSiteEntity(),
                $this->getSiteEntity(),
            ]);

        $this->organisationRepository->expects($this->once())
            ->method('getOrganisationSiteCount')
            ->willReturn(2);

        $dtos = $this->authorisedExaminerStatisticService->getListForPage(self::AE_ID, 1, 10);
        $this->assertEquals(2, $dtos->getSiteTotalCount());
        $this->assertEquals(2, count($dtos->getSites()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownForWrongEntity()
    {
        $this->siteRepository->expects($this->once())
            ->method('getSitesTestQualityForOrganisationId')
            ->willReturn([
                new Organisation(),
            ]);

        $this->authorisedExaminerStatisticService->getListForPage(self::AE_ID, 1, 10);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testWeThrow404ExceptionForInvalidPageNumbers()
    {
        $this->siteRepository->expects($this->never())
            ->method('getSitesTestQualityForOrganisationId');

        $this->organisationRepository->expects($this->once())
            ->method('getOrganisationSiteCount')
            ->willReturn(10);

        $this->authorisedExaminerStatisticService->getListForPage(self::AE_ID, 2, 10);
    }

    public function testWeDontThrow404ExceptionWhenAeHasNoSites()
    {
        $this->siteRepository->expects($this->once())
            ->method('getSitesTestQualityForOrganisationId')
            ->willReturn([]);

        $this->organisationRepository->expects($this->once())
            ->method('getOrganisationSiteCount')
            ->willReturn(0);

        $dto = $this->authorisedExaminerStatisticService->getListForPage(self::AE_ID, 1, 10);
        $this->assertEquals(0, $dto->getSiteTotalCount());
        $this->assertEquals(0, count($dto->getSites()));
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
            );

        return $siteEntity;
    }
}
