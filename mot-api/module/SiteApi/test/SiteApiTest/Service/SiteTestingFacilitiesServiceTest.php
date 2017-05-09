<?php

namespace SiteApiTest\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\FacilityType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteFacility;
use DvsaEntities\Repository\FacilityTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteTestingDailyScheduleRepository;
use DvsaEventApi\Service\EventService;
use SiteApi\Service\SiteTestingFacilitiesService;
use SiteApi\Service\Validator\SiteValidator;

/**
 * Class SiteTestingFacilitiesServiceTest.
 */
class SiteTestingFacilitiesServiceTest extends AbstractServiceTestCase
{
    use TestCasePermissionTrait;

    const SITE_ID = 1;

    /** @var SiteRepository $siteRepository */
    private $siteRepository;

    /** @var AuthorisationServiceInterface|MockObj */
    private $mockAuthService;

    /** @var FacilityTypeRepository $facilityTypeRepository */
    private $facilityTypeRepository;

    /** @var SiteValidator $siteValidator */
    private $siteValidator;

    /** @var EventService $eventService */
    private $eventService;

    /** @var MotIdentityInterface|MockObj */
    private $mockIdentity;

    /** @var UpdateVtsAssertion */
    private $updateVtsAssertion;

    /** @var XssFilter */
    private $mockXssFilter;

    /** @var EntityManager */
    private $entityManager;

    public function setUp()
    {
        $this->siteRepository = XMock::of(SiteRepository::class);
        $this->mockAuthService = $this->getMockAuthorizationService(false);
        $this->updateVtsAssertion = new UpdateVtsAssertion($this->mockAuthService);
        $this->mockXssFilter = $this->createXssFilterMock();
        $this->siteValidator = XMock::of(SiteValidator::class);
        $this->eventService = XMock::of(EventService::class);
        $this->facilityTypeRepository = XMock::of(FacilityTypeRepository::class);
        $this->mockIdentity = XMock::of(MotIdentityInterface::class);
        $this->scheduleRepository = XMock::of(SiteTestingDailyScheduleRepository::class);
        $this->entityManager = XMock::of(EntityManager::class);
    }

    private function getService()
    {
        return new SiteTestingFacilitiesService(
            $this->siteRepository,
            $this->mockAuthService,
            $this->updateVtsAssertion,
            $this->mockXssFilter,
            $this->siteValidator,
            $this->eventService,
            $this->facilityTypeRepository,
            $this->mockIdentity,
            $this->entityManager
        );
    }

    public function testExceptionThrownIfPermissionNotGrantedOnSiteTestingFacilitiesGet()
    {
        $this->mockAssertGranted($this->mockAuthService, []);

        $this->setExpectedException(UnauthorisedException::class);
        $this->getService()->get(self::SITE_ID);
    }

    public function testSiteFacilitiesRetrievedOnRequestWithPermissions()
    {
        $this->mockAssertGranted($this->mockAuthService, [PermissionInSystem::VEHICLE_TESTING_STATION_LIST]);

        $this->mockService($this->siteRepository, 'get', $this->getSiteEntityMock());

        $siteFacilities = $this->getService()->get(self::SITE_ID);

        $this->assertInstanceOf(ArrayCollection::class, $siteFacilities);
        $this->assertCount(2, $siteFacilities);
    }

    public function testExceptionThrownIfPermissionNotGrantedOnSiteTestingFacilitiesUpdate()
    {
        $this->mockAssertGrantedAtSite(
            $this->mockAuthService,
            [PermissionInSystem::VEHICLE_TESTING_STATION_LIST],
            1
        );

        $this->mockService($this->siteRepository, 'get', $this->getSiteEntityMock());
        $this->setExpectedException(UnauthorisedException::class);
        $this->getService()->update(self::SITE_ID, new VehicleTestingStationDto());
    }

    /**
     * Permissions are as follows:.
     *
     * PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS,
     * PermissionAtSite::VTS_UPDATE_NAME,
     * PermissionAtSite::VTS_UPDATE_CORRESPONDENCE_DETAILS,
     * PermissionAtSite::VTS_UPDATE_BUSINESS_DETAILS,
     * PermissionAtSite::VTS_UPDATE_SITE_DETAILS
     */
    public function testExceptionThrownIfCurrentUserHasNoPermissionsToEditAtSite()
    {
        $this->mockAssertGrantedAtSite(
            $this->mockAuthService,
            [PermissionInSystem::VEHICLE_TESTING_STATION_LIST],
            1
        );

        $this->setExpectedException(UnauthorisedException::class);
        $this->getService()->update(self::SITE_ID, new VehicleTestingStationDto());
    }

    /**
     * We have a confirmation setter to define whether we need to update and validate the data generated,
     * or do we just validate and return results.
     */
    public function testIfAllBelowPermissionsAvailableButNeedsConfirmationReturnTrue()
    {
        // The VTS Assertion Service checks the following permissions
        $this->mockAssertGrantedAtSite(
            $this->mockAuthService,
            [
                PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS,
                PermissionAtSite::VTS_UPDATE_NAME,
            ],
            self::SITE_ID
        );

        $this->mockService($this->siteRepository, 'get', $this->getSiteEntityMock());

        $dto = new VehicleTestingStationDto();
        $dto->setIsNeedConfirmation(true);

        $response = $this->getService()->update(self::SITE_ID, $dto);

        $this->assertTrue($response);
    }

    public function testIfAllBelowPermissionsAvailableAndConfirmedReturnsTrue()
    {
        // The VTS Assertion Service checks the following permissions
        $this->mockAssertGrantedAtSite(
            $this->mockAuthService,
            [
                PermissionAtSite::VTS_UPDATE_TESTING_FACILITIES_DETAILS,
                PermissionAtSite::VTS_UPDATE_NAME,
            ],
            self::SITE_ID
        );

        $this->mockService($this->siteRepository, 'get', $this->getSiteEntityMock());
        $this->mockService(
            $this->facilityTypeRepository,
            'getByCode',
            (new FacilityType())->setId(1)->setName('OPTL')->setCode('OPTL')
        );

        $dto = new VehicleTestingStationDto();
        $dto->setIsNeedConfirmation(false);
        $dto->setFacilities(
            [
                (new FacilityDto())->setId(1)->setName('TPTL')
                    ->setType(
                        (new FacilityTypeDto())->setId(1)->setName('TPTL')->setCode('TPTL')
                    ),
            ]
        );

        $response = $this->getService()->update(self::SITE_ID, $dto);

        $this->assertTrue($response['success']);
    }

    private function getSiteEntityMock()
    {
        $facilities = new ArrayCollection();
        $facilities->add(
            (new SiteFacility())->setId(2)
                ->setName('One Lane')->setFacilityType(
                    (new FacilityType())->setId(1)->setName('OPTL')->setCode('OPTL')
                )
        );
        $facilities->add(
            (new SiteFacility())->setId(3)
                ->setName('Two Lane')->setFacilityType(
                    (new FacilityType())->setId(1)->setName('TPTL')->setCode('TPTL')
                )
        );

        $site = new Site();
        $site->setId(self::SITE_ID)
            ->setName('VTS Test')
            ->setFacilities($facilities);

        return $site;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createXssFilterMock()
    {
        $xssFilterMock = $this
            ->getMockBuilder(XssFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $xssFilterMock
            ->method('filter')
            ->will($this->returnArgument(0));

        $xssFilterMock
            ->method('filterMultiple')
            ->will($this->returnArgument(0));

        return $xssFilterMock;
    }

    private function mockService($mockService, $method, $result)
    {
        $mockService->expects($this->any())
            ->method($method)
            ->willReturn($result);
    }
}
