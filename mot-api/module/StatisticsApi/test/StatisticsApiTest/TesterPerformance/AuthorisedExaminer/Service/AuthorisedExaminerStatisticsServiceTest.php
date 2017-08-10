<?php

namespace Dvsa\Mot\Api\StatisticsApiTest\TesterPerformance\AuthorisedExaminer\Service;

use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Mapper\AuthorisedExaminerSiteMapper;
use Dvsa\Mot\Api\StatisticsApi\TesterQualityInformation\TesterPerformance\AuthorisedExaminer\Service\AuthorisedExaminerStatisticsService;
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
use DvsaEntities\Repository\SiteRiskAssessmentRepository;

class AuthorisedExaminerStatisticsServiceTest extends \PHPUnit_Framework_TestCase
{
    const AE_ID = 1;
    /** @var \PHPUnit_Framework_MockObject_MockObject | AuthorisedExaminerStatisticsService */
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
        $this->authorisedExaminerStatisticService = new AuthorisedExaminerStatisticsService(
            $this->siteRepository,
            XMock::of(SiteRiskAssessmentRepository::class),
            $this->organisationRepository,
            $this->authorisationService,
            $this->authorisedExaminerSiteMapper
        );
    }

    public function testGetList()
    {
        $this->markTestSkipped();
        $this->siteRepository->expects($this->once())
            ->method('getSitesForOrganisationTestQualityInformation')
            ->willReturn($this->getSites());

        $this->organisationRepository->expects($this->once())
            ->method('getOrganisationSiteCount')
            ->willReturn(3);

        $dtos = $this->authorisedExaminerStatisticService->getListForPage(self::AE_ID, 1, 10);
        $this->assertEquals(3, $dtos->getSiteTotalCount());
        $this->assertEquals(3, count($dtos->getSites()));
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testWeThrow404ExceptionForInvalidPageNumbers()
    {
        $this->markTestSkipped();
        $this->siteRepository->expects($this->never())
            ->method('getSitesForOrganisationTestQualityInformation');

        $this->organisationRepository->expects($this->once())
            ->method('getOrganisationSiteCount')
            ->willReturn(10);

        $this->authorisedExaminerStatisticService->getListForPage(self::AE_ID, 2, 10);
    }

    public function testWeDontThrow404ExceptionWhenAeHasNoSites()
    {
        $this->markTestSkipped();
        $this->siteRepository->expects($this->once())
            ->method('getSitesForOrganisationTestQualityInformation')
            ->willReturn([]);

        $this->organisationRepository->expects($this->once())
            ->method('getOrganisationSiteCount')
            ->willReturn(0);

        $dto = $this->authorisedExaminerStatisticService->getListForPage(self::AE_ID, 1, 10);
        $this->assertEquals(0, $dto->getSiteTotalCount());
        $this->assertEquals(0, count($dto->getSites()));
    }

    protected function getSites()
    {
        $keys = [
            "id", "name", "site_number",
            "current_assessment",
            "previous_assessment",
            "address_line_1", "address_line_2", "address_line_3", "address_line_4", "town", "postcode", "country"
        ];

        $site1 = array_combine($keys,
            [
                1, "name 1", "number 1",
                (new EnforcementSiteAssessment())->setVisitDate(new \DateTime(2017-07-01))->setSiteAssessmentScore(90),
                (new EnforcementSiteAssessment())->setVisitDate(new \DateTime(2017-05-05))->setSiteAssessmentScore(144),
                "address line 1", "address line 2", "address line 3", "address line 4", "Bristol", "BL 10NS", "GB"
            ]
        );

        $site2 = array_combine($keys,
            [
                2, "name 2", "number 2",
                (new EnforcementSiteAssessment())->setVisitDate(new \DateTime(2017-07-01))->setSiteAssessmentScore(90),
                null,
                "address line 1", "address line 2", "address line 3", "address line 4", "Bristol", "BL 10NS", "GB"
            ]
        );

        $site3 = array_combine($keys,
            [
                3, "name 3", "number 3",
                null,
                null,
                "address line 1", "address line 2", "address line 3", "address line 4", "Bristol", "BL 10NS", "GB"
            ]
        );

        return [$site1, $site2, $site3];
    }
}
