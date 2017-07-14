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
}
