<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityRepository;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer as AuthorisationForAuthorisedExaminerEntity;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository;
use DvsaEntities\Repository\OrganisationBusinessRoleRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;
use DvsaMotApi\Service\TesterService;
use NotificationApi\Service\NotificationService;
use SiteApi\Service\SiteService;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

/**
 * Tests for DashboardService
 */
class DashboardServiceTest extends AbstractServiceTestCase
{
    /** @var EntityManager */
    private $entityManager;

    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    /** @var SiteService */
    private $siteService;

    /** @var SpecialNoticeService */
    private $specialNoticeService;

    /** @var NotificationService */
    private $notificationService;

    /** @var PersonalAuthorisationForMotTestingService */
    private $personalAuthorisationService;

    /** @var TesterService */
    private $testerService;

    /** @var AuthorisationForAuthorisedExaminerRepository */
    private $afaRepository;

    /** @var EntityRepository */
    private $businessRoleStatusRepository;

    /** @var OrganisationRepository */
    private $organisationRepository;

    /** @var OrganisationBusinessRoleRepository */
    private $organisationBusinessRoleRepository;

    /** @var SiteBusinessRoleMapRepository */
    private $siteBusinessRoleMapRepository;

    /** @var VehicleService */
    private $vehicleService;

    /** @var ParamObfuscator */
    private $paramObfuscator;

    public function setUp()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->authorisationService = XMock::of(AuthorisationService::class);
        $this->siteService = XMock::of(SiteService::class);
        $this->specialNoticeService = XMock::of(SpecialNoticeService::class);
        $this->notificationService = XMock::of(NotificationService::class);
        $this->personalAuthorisationService = XMock::of(PersonalAuthorisationForMotTestingService::class);
        $this->testerService = XMock::of(TesterService::class);
        $this->afaRepository = XMock::of(AuthorisationForAuthorisedExaminerRepository::class);
        $this->businessRoleStatusRepository = XMock::of(EntityRepository::class);
        $this->organisationRepository = XMock::of(OrganisationRepository::class);
        $this->organisationBusinessRoleRepository = XMock::of(OrganisationBusinessRoleRepository::class);
        $this->siteBusinessRoleMapRepository = XMock::of(SiteBusinessRoleMapRepository::class);
        $this->vehicleService = XMock::of(VehicleService::class);
        $this->paramObfuscator = XMock::of(ParamObfuscator::class);
    }

    public function testGetAesWithSitesAndPositions_empty()
    {
        //given
        $aesById = [];
        $personId = 1;
        $sitesByAe = [];
        $positionsBySite = [];
        $aesPositionNames = [];

        //when
        $result = $this->buildService()->getAesWithSitesAndPositions(
            $aesById, $personId, $sitesByAe, $positionsBySite, $aesPositionNames
        );

        //then
        $this->assertEquals([], $result);
    }

    public function testGetAesWithSitesAndPositions_mapsPositions()
    {
        //given
        $ae1Id = 123;
        $aesById = [
            $ae1Id => (new AuthorisationForAuthorisedExaminerEntity())
                ->setId($ae1Id)->setOrganisation(new Organisation())
        ];
        $personId = 18765;
        $site1Id = 12345;
        $sitesByAe = [
            $ae1Id => [
                (new Site())->setId($site1Id)
            ]
        ];
        $sitePosition = 'NOBODY';
        $positionsBySite = [
            $site1Id => [
                (new SiteBusinessRoleMap())
                    ->setSiteBusinessRole((new Entity\SiteBusinessRole())->setName($sitePosition))
            ]
        ];
        $ae1Position = 'GOD';
        $aesPositionNames = [
            $ae1Id => $ae1Position
        ];

        //when
        $result = $this->buildService()->getAesWithSitesAndPositions(
            $aesById, $personId, $sitesByAe, $positionsBySite, $aesPositionNames
        );

        //then
        $this->assertEquals($ae1Position, $result[$ae1Id]->getPosition());
        $this->assertEquals(
            $sitePosition,
            $result[$ae1Id]->getSites()[0]->getPositions()[0]->getSiteBusinessRole()->getName()
        );
    }

    public function testGetDataForDashboardByPersonIdAssemblesDashboardData()
    {
        $specialNoticeSummary = [
            'overdueCount'            => 1,
            'unreadCount'             => 2,
            'acknowledgementDeadline' => date('Y-m-d', strtotime('tomorrow'))
        ];

        $overdueSpecialNoticesForClasses = [
            3 => 1
        ];

        $notification = new Notification();

        $inProgressDemoTestNumber = 'ABCD1234';

        $hero = 'vehicle-examiner';

        //

        $this->setDummyDependenciesForGetDataForDashboardByPersonId();

        $this->specialNoticeService
            ->expects($this->any())
            ->method('specialNoticeSummaryForUser')
            ->willReturn($specialNoticeSummary);

        $this->specialNoticeService
            ->expects($this->any())
            ->method('getAmountOfOverdueSpecialNoticesForClasses')
            ->willReturn($overdueSpecialNoticesForClasses);

        $this->notificationService
            ->expects($this->any())
            ->method('getUnreadByPersonId')
            ->willReturn([$notification]);

        $this->testerService
            ->expects($this->any())
            ->method('findInProgressDemoTestNumberForTester')
            ->willReturn($inProgressDemoTestNumber);

        $this->authorisationService
            ->expects($this->any())
            ->method('getHero')
            ->willReturn($hero);

        //

        $dashboardData = $this->buildService()->getDataForDashboardByPersonId(1);

        //

        $this->assertEquals($hero, $dashboardData->getHero());
        $this->assertCount(0, $dashboardData->getAuthorisedExaminers());

        $specialNotice = $dashboardData->getSpecialNotice();
        $this->assertEquals($specialNoticeSummary['overdueCount'], $specialNotice->getOverdueCount());
        $this->assertEquals($specialNoticeSummary['unreadCount'], $specialNotice->getUnreadCount());
        $this->assertEquals(1, $specialNotice->getDaysLeftToView());

        $this->assertEquals($overdueSpecialNoticesForClasses, $dashboardData->getOverdueSpecialNotices());
        $this->assertSame($notification, $dashboardData->getNotifications()[0]);
        $this->assertNull($dashboardData->getInProgressTestNumber());
        $this->assertNull($dashboardData->getInProgressTestTypeCode());
        $this->assertEquals($inProgressDemoTestNumber, $dashboardData->getInProgressDemoTestNumber());
    }

    public function testNonMotNumberIncludedInDashboardDataForVehicleExaminers()
    {
        $this
            ->setDummyDependenciesForGetDataForDashboardByPersonId()
            ->setDefaultDependenciesForGetDataForDashboardByPersonId();

        $inProgressNonMotTestNumber = 123456789;

        $this
            ->withNonMotTestPermissionGranted()
            ->expectServiceWillRequestInProgressNonMotTestNumber($inProgressNonMotTestNumber);

        $dashboardData = $this->buildService()->getDataForDashboardByPersonId(1);

        $this->assertEquals($inProgressNonMotTestNumber, $dashboardData->getInProgressNonMotTestNumber());
    }

    public function testNonMotNumberNotIncludedInDashboardDataForNonVehicleExaminers()
    {
        $this
            ->setDummyDependenciesForGetDataForDashboardByPersonId()
            ->setDefaultDependenciesForGetDataForDashboardByPersonId();

        $this
            ->withoutNonMotTestPermissionGranted()
            ->expectServiceWillNotRequestInProgressNonMotTestNumber();

        $dashboardData = $this->buildService()->getDataForDashboardByPersonId(1);

        $this->assertNull($dashboardData->getInProgressNonMotTestNumber());
    }

    private function withNonMotTestPermissionGranted()
    {
        $this->authorisationService
            ->expects($this->any())
            ->method('isGranted')
            ->with(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM)
            ->willReturn(true);

        return $this;
    }

    private function withoutNonMotTestPermissionGranted()
    {
        $this->authorisationService
            ->expects($this->any())
            ->method('isGranted')
            ->with(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM)
            ->willReturn(false);

        return $this;
    }

    private function expectServiceWillRequestInProgressNonMotTestNumber($inProgressNonMotTestNumber)
    {
        $this->testerService
            ->expects($this->once())
            ->method('findInProgressNonMotTestNumberForVehicleExaminer')
            ->willReturn($inProgressNonMotTestNumber);
    }

    private function expectServiceWillNotRequestInProgressNonMotTestNumber()
    {
        $this->testerService
            ->expects($this->never())
            ->method('findInProgressNonMotTestNumberForVehicleExaminer');
    }

    private function setDummyDependenciesForGetDataForDashboardByPersonId()
    {
        $this->setDummyDependenciesForGetAuthorisedExaminersByPerson();

        $this->entityManager
            ->expects($this->any())
            ->method('find')
            ->willReturn(new Person());

        $this->testerService
            ->expects($this->any())
            ->method('findInProgressTestForTester')
            ->willReturn(null);

        $this->testerService
            ->expects($this->any())
            ->method('isTesterActiveByUser')
            ->willReturn(true);

        return $this;
    }

    private function setDefaultDependenciesForGetDataForDashboardByPersonId()
    {
        $this->specialNoticeService
            ->expects($this->any())
            ->method('specialNoticeSummaryForUser')
            ->willReturn([
                'overdueCount'            => 0,
                'unreadCount'             => 0,
                'acknowledgementDeadline' => date('Y-m-d')
            ]);

        $this->specialNoticeService
            ->expects($this->any())
            ->method('getAmountOfOverdueSpecialNoticesForClasses')
            ->willReturn([]);

        $this->notificationService
            ->expects($this->any())
            ->method('getAllByPersonId')
            ->willReturn([]);

        $this->testerService
            ->expects($this->any())
            ->method('findInProgressDemoTestNumberForTester')
            ->willReturn(null);

        $this->authorisationService
            ->expects($this->any())
            ->method('getHero')
            ->willReturn('tester');

        return $this;
    }

    private function setDummyDependenciesForGetAuthorisedExaminersByPerson()
    {
        $this->afaRepository
            ->expects($this->any())
            ->method('getBySitePositionForPerson')
            ->willReturn([]);

        $this->businessRoleStatusRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->willReturn(new BusinessRoleStatus());

        $this->organisationRepository
            ->expects($this->any())
            ->method('findForPersonWithRole')
            ->willReturn([]);

        $this->organisationBusinessRoleRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->willReturn(new OrganisationBusinessRole());

        $this->siteBusinessRoleMapRepository
            ->expects($this->any())
            ->method('findBy')
            ->willReturn([]);

        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(function ($className) {
                switch ($className) {
                    case BusinessRoleStatus::class:
                        return $this->businessRoleStatusRepository;
                    case Organisation::class:
                        return $this->organisationRepository;
                    case OrganisationBusinessRole::class:
                        return $this->organisationBusinessRoleRepository;
                    case SiteBusinessRoleMap::class:
                        return $this->siteBusinessRoleMapRepository;
                    default:
                        return XMock::of(EntityRepository::class);
                }
            });

        return $this;
    }

    private function buildService()
    {
        return new DashboardService(
            $this->entityManager,
            $this->authorisationService,
            $this->siteService,
            $this->specialNoticeService,
            $this->notificationService,
            $this->personalAuthorisationService,
            $this->testerService,
            $this->afaRepository,
            $this->vehicleService,
            $this->paramObfuscator
        );
    }
}
