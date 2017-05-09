<?php

namespace SiteApiTest\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\Assertion\UpdateVtsAssertion;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\FacilityType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteFacility;
use DvsaEntities\Entity\SiteStatus;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Repository\AuthorisationForTestingMotAtSiteStatusRepository;
use DvsaEntities\Repository\NonWorkingDayCountryRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\SiteStatusRepository;
use DvsaEntities\Repository\SiteTestingDailyScheduleRepository;
use DvsaEntities\Repository\SiteTypeRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEventApi\Service\EventService;
use NonWorkingDaysApi\Constants\CountryCode;
use SiteApi\Service\SiteDetailsService;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\SiteValidator;
use Zend\Mvc\Router\Http\Method;

class SiteDetailsServiceTest extends AbstractServiceTestCase
{
    use TestCasePermissionTrait;

    const SITE_ID = 1;

    /** @var SiteRepository $siteRepository */
    private $siteRepository;
    /** @var AuthorisationServiceInterface $mockAuthService */
    private $mockAuthService;
    /** @var UpdateVtsAssertion $updateVtsAssertion */
    private $updateVtsAssertion;
    /** @var XssFilter $mockXssFilter */
    private $mockXssFilter;
    /** @var SiteValidator $siteValidator */
    private $siteValidator;
    /** @var EventService $eventService */
    private $eventService;
    /** @var MotIdentityInterface */
    private $mockIdentity;
    /** @var EntityManager $entityManager */
    private $entityManager;
    /** @var SiteTestingDailyScheduleRepository $scheduleRepository */
    private $scheduleRepository;
    /** @var VehicleClassRepository $vehicleClassRepository */
    private $vehicleClassRepository;
    /** @var AuthorisationForTestingMotAtSiteStatusRepository $authForTestingMotStatusRepository */
    private $authForTestingMotStatusRepository;
    /** @var SiteStatusRepository siteStatusRepository */
    private $siteStatusRepository;
    /** @var SiteDetailsValidator */
    private $siteDetailsValidator;
    /** @var SiteTypeRepository */
    private $siteTypeRepository;
    /** @var NonWorkingDayCountryRepository */
    private $nonWorkingDayCountryRepository;

    public function setUp()
    {
        $this->siteRepository = XMock::of(SiteRepository::class);
        $this->mockAuthService = $this->getMockAuthorizationService(false);
        $this->updateVtsAssertion = new UpdateVtsAssertion($this->mockAuthService);
        $this->mockXssFilter = $this->createXssFilterMock();
        $this->siteValidator = XMock::of(SiteValidator::class);
        $this->eventService = XMock::of(EventService::class);
        $this->mockIdentity = XMock::of(MotIdentityInterface::class);
        $this->scheduleRepository = XMock::of(SiteTestingDailyScheduleRepository::class);
        $this->entityManager = XMock::of(EntityManager::class);
        $this->vehicleClassRepository = XMock::of(VehicleClassRepository::class);
        $this->authForTestingMotStatusRepository = XMock::of(AuthorisationForTestingMotAtSiteStatusRepository::class);
        $this->siteStatusRepository = XMock::of(SiteStatusRepository::class);
        $this->siteDetailsValidator = XMock::of(SiteDetailsValidator::class);
        $this->siteTypeRepository = XMock::of(SiteTypeRepository::class);
        $this->nonWorkingDayCountryRepository = Xmock::of(NonWorkingDayCountryRepository::class);
    }

    private function getService()
    {
        return new SiteDetailsService(
            $this->siteRepository,
            $this->mockAuthService,
            $this->updateVtsAssertion,
            $this->mockXssFilter,
            $this->siteValidator,
            $this->eventService,
            $this->mockIdentity,
            $this->entityManager,
            $this->vehicleClassRepository,
            $this->authForTestingMotStatusRepository,
            $this->siteStatusRepository,
            $this->siteDetailsValidator,
            $this->siteTypeRepository,
            $this->nonWorkingDayCountryRepository
        );
    }

    /**
     * @dataProvider dataProviderTestPatchingSingleProperty
     */
    public function testPatchingSingleProperty($patchData, $validators, $siteRepositorySave)
    {
        $patchData['_class'] = 'DvsaCommon\\Dto\\Site\\VehicleTestingStationDto';

        $this->mockService($this->siteRepository, 'get', $this->getSiteEntityMock());
        $this->mockService($this->siteStatusRepository, 'getByCode', new SiteStatus([
            'name' => 'Approved',
        ]));
        $this->mockService($this->siteTypeRepository, 'getByCode', new SiteType([
            'name' => 'New type',
        ]));
        $this->mockService($this->siteDetailsValidator, 'getErrors', new ErrorSchema());

        /** @var MethodSpy[] $spies */
        $spies = [];

        foreach ($validators as $validator => $count) {
            $spies[$validator] = new MethodSpy($this->siteDetailsValidator, $validator);
        }
        $siteRepositorySpy = new MethodSpy($this->siteRepository, 'persist');

        $service = $this->getService();
        $service->patch(1, $patchData);

        $spiesToCheck = $validators;
        foreach ($spiesToCheck as $name => $count) {
            $spy = $spies[$name];
            $this->assertEquals($count, $spy->invocationCount());
        }

        if ($siteRepositorySave) {
            $this->assertEquals(1, $siteRepositorySpy->invocationCount());
        }
    }

    public function dataProviderTestPatchingSingleProperty()
    {
        return [
            [
                'patchData' => [
                    'name' => 'Test Garage',
                ],
                'validators' => [
                    'validateName' => true,
                    'validateStatus' => false,
                    'validateTestClasses' => false,
                    'validateType' => false,
                ],
                'siteRepositorySave' => true,
            ],
            [
                'patchData' => [
                    'status' => 'LA',
                ],
                'validators' => [
                    'validateName' => false,
                    'validateStatus' => true,
                    'validateTestClasses' => false,
                    'validateType' => false,
                ],
                'siteRepositorySave' => true,
            ],
            [
                'patchData' => [
                    'testClasses' => [1, 2, 3, 4],
                ],
                'validators' => [
                    'validateName' => false,
                    'validateStatus' => false,
                    'validateTestClasses' => true,
                    'validateType' => false,
                ],
                'siteRepositorySave' => true,
            ],
            [
                'patchData' => [
                    'type' => 'AO',
                ],
                'validators' => [
                    'validateName' => false,
                    'validateStatus' => false,
                    'validateTestClasses' => false,
                    'validateType' => true,
                ],
                'siteRepositorySave' => true,
            ],
        ];
    }

    private function getSiteEntityMock()
    {
        $facilities = new ArrayCollection();
        $facilities->add(
            (new SiteFacility())->setId(2)
                ->setName('One Lane')->setFacilityType(
                    (new FacilityType())->setId(self::SITE_ID)->setName('OPTL')->setCode('OPTL')
                )
        );
        $facilities->add(
            (new SiteFacility())->setId(3)
                ->setName('Two Lane')->setFacilityType(
                    (new FacilityType())->setId(self::SITE_ID)->setName('TPTL')->setCode('TPTL')
                )
        );

        $siteStatus = (new SiteStatus())
            ->setId(1111)
            ->setCode(SiteStatusCode::APPROVED)
        ;

        $siteType = (new SiteType())
            ->setCode(SiteTypeCode::VEHICLE_TESTING_STATION);

        $site = new Site();
        $site->setId(self::SITE_ID)
            ->setName('VTS Test')
            ->setFacilities($facilities)
            ->setStatus($siteStatus)
            ->setType($siteType)
        ;

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

    /**
     * @dataProvider dataProviderTestIfChangingCountryUpdatesSiteFlagsAndNonWorkingDays
     */
    public function testIfChangingCountryUpdatesSiteFlagsAndNonWorkingDays($country)
    {
        $patchData = [
            '_class' => 'DvsaCommon\\Dto\\Site\\VehicleTestingStationDto',
            'country' => $country,
        ];

        $site = Xmock::of(Site::class);

        $siteSetDualLanguageSpy = new MethodSpy($site, 'setDualLanguage');
        $siteSetScottishBankHolidaySpy = new MethodSpy($site, 'setScottishBankHoliday');
        $siteSetNonWorkingDayCountrySpy = new MethodSpy($site, 'setNonWorkingDayCountry');
        $nonWorkingDayCountryRepositoryGetOneByCodeSpy = new MethodSpy(
            $this->nonWorkingDayCountryRepository, 'getOneByCode'
        );

        $this->mockService($this->siteRepository, 'get', $site);

        $this->mockService($this->siteStatusRepository, 'getByCode', new SiteStatus([
            'name' => 'Approved',
        ]));
        $this->mockService($this->siteTypeRepository, 'getByCode', new SiteType([
            'name' => 'New type',
        ]));
        $this->mockService($this->siteDetailsValidator, 'getErrors', new ErrorSchema());

        $service = $this->getService();
        $service->patch(1, $patchData);

        $siteSetDualLanguageSpyParam = $siteSetDualLanguageSpy->paramsForLastInvocation()[0];
        $siteSetScottishBankHolidaySpyParam = $siteSetScottishBankHolidaySpy->paramsForLastInvocation()[0];
        $nonWorkingDayCountryRepositoryGetOneByCodeSpyParam =
            $nonWorkingDayCountryRepositoryGetOneByCodeSpy->paramsForLastInvocation()[0];

        switch ($country) {
            case CountryCode::ENGLAND:
            case CountryCode::SCOTLAND:
                $this->assertEquals(false, $siteSetDualLanguageSpyParam);
                break;
            case CountryCode::WALES:
                $this->assertEquals(true, $siteSetDualLanguageSpyParam);
                break;
        }

        switch ($country) {
            case CountryCode::ENGLAND:
            case CountryCode::WALES:
                $this->assertEquals(false, $siteSetScottishBankHolidaySpyParam);
                break;
            case CountryCode::SCOTLAND:
                $this->assertEquals(true, $siteSetScottishBankHolidaySpyParam);
                break;
        }

        $this->assertEquals($country, $nonWorkingDayCountryRepositoryGetOneByCodeSpyParam);
        $this->assertEquals(1, $siteSetNonWorkingDayCountrySpy->invocationCount(1));
    }

    public function dataProviderTestIfChangingCountryUpdatesSiteFlagsAndNonWorkingDays()
    {
        return [
            [
                'country' => CountryCode::ENGLAND,
            ],
            [
                'country' => CountryCode::SCOTLAND,
            ],
            [
                'country' => CountryCode::WALES,
            ],
        ];
    }
}
