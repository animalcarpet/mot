<?php

namespace DvsaElasticSearchTest\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Service\ElasticSearchService;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaFeature\FeatureToggles;
use PHPUnit_Framework_TestCase;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\MotTestReasonForCancel;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\OdometerReading;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;

class ElasticSearchServiceTest extends PHPUnit_Framework_TestCase
{
    const TEST_TYPE_NAME_NORMAL_TEST = 'Normal Test';
    const TEST_TYPE_NAME_MYSTERY_SHOPPER = 'Mystery Shopper';
    const TEST_TYPE_NAME_NON_MOT = 'Non-Mot Test';

    private $authorizationService;
    private $siteRepository;
    private $entityManager;
    private $motTestRepository;
    private $featureToggles;

    protected function setUp()
    {
        $this->authorizationService = XMock::of(AuthorisationServiceInterface::class);
        $this->siteRepository = XMock::of(SiteRepository::class);
        $this->motTestRepository = XMock::of(MotTestRepository::class);
        $this->featureToggles = XMock::of(FeatureToggles::class);

        $this->entityManager = XMock::of(EntityManager::class);
        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with(MotTest::class)
            ->willReturn($this->motTestRepository);

        parent::setUp();
    }

    public function testFindTestsWithViewMysteryShopperTestsAndViewNonMotTestsPermissions()
    {
        $motTestNumber = 999999999014;
        $motTestsResult = [
            $this->buildMotTestResultItem($motTestNumber, self::TEST_TYPE_NAME_NORMAL_TEST),
            $this->buildMotTestResultItem($motTestNumber, self::TEST_TYPE_NAME_MYSTERY_SHOPPER),
            $this->buildMotTestResultItem($motTestNumber, self::TEST_TYPE_NAME_NON_MOT),
        ];

        $this->motTestRepository
            ->expects($this->once())
            ->method('getMotTestSearchResult')
            ->willReturn($motTestsResult);

        $this->authorizationService
            ->expects($this->at(1))
            ->method('isGranted')
            ->with(PermissionInSystem::VIEW_MYSTERY_SHOPPER_TESTS)
            ->willReturn(true);

        $this->authorizationService
            ->expects($this->at(2))
            ->method('isGranted')
            ->with(PermissionInSystem::VIEW_NON_MOT_TESTS)
            ->willReturn(true);

        $this->featureToggles
            ->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::MYSTERY_SHOPPER)
            ->willReturn(true);

        $resultDto = $this->buildService()->findTests($this->buildSearchParams());

        $this->assertEquals(3, $resultDto->getResultCount());
    }

    public function testFindTestsLog()
    {
        $motTestNumber = 999999999014;
        $motTestLogsResult = [
            $this->buildMotTestLogResultItem(
                [
                    'testTypeName' => self::TEST_TYPE_NAME_NORMAL_TEST,
                    'number' => $motTestNumber
                ]
            )
        ];

        $this->motTestRepository
            ->expects($this->any())
            ->method('getMotTestLogsResult')
            ->willReturn($motTestLogsResult);

        $resultDto = $this->buildService()->findTestsLog($this->buildSearchParams());

        $this->assertEquals(1, $resultDto->getResultCount());
        $this->assertEquals(self::TEST_TYPE_NAME_NORMAL_TEST, $resultDto->getData()[$motTestNumber]['testType']);
    }

    public function testFindSiteTestsLog()
    {
        $motTestNumber = 999999999014;
        $motTestLogsResult = [
            $this->buildMotTestLogResultItem(
                [
                    'testTypeName' => self::TEST_TYPE_NAME_NORMAL_TEST,
                    'number' => $motTestNumber
                ]
            )
        ];

        $this->motTestRepository
            ->expects($this->any())
            ->method('getMotTestLogsResult')
            ->willReturn($motTestLogsResult);

        $organisation = new Organisation();
        $organisation->setId(1);
        $organisation->setName('Organisation 1');

        $site = new Site();
        $site->setid(1);
        $site->setName("Site 1");
        $site->setOrganisation($organisation);

        $this->siteRepository
            ->expects($this->any())
            ->method('get')
            ->with(1)
            ->willReturn($site);

        $resultDto = $this->buildService()->findSiteTestsLog($this->buildSearchParams());

        $this->assertEquals(1, $resultDto->getResultCount());
        $this->assertEquals(self::TEST_TYPE_NAME_NORMAL_TEST, $resultDto->getData()[$motTestNumber]['testType']);
    }

    public function testMysteryShopperTestDisguisedInTesterTestsLogWithoutViewMysteryShopperPermission()
    {
        $motTestNumber = 999999999014;
        $motTestLogsResult = [
            $this->buildMotTestLogResultItem(
                [
                    'testTypeName' => self::TEST_TYPE_NAME_MYSTERY_SHOPPER,
                    'number' => $motTestNumber
                ]
            )
        ];

        $this->motTestRepository
            ->expects($this->any())
            ->method('getMotTestLogsResult')
            ->willReturn($motTestLogsResult);

        $this->authorizationService
            ->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::VIEW_MYSTERY_SHOPPER_TESTS)
            ->willReturn(false);

        $resultDto = $this->buildService()->findTesterTestsLog($this->buildSearchParams());

        $this->assertEquals(1, $resultDto->getResultCount());
        $this->assertEquals(self::TEST_TYPE_NAME_NORMAL_TEST, $resultDto->getData()[$motTestNumber]['testType']);
    }

    public function testMysteryShopperTestNotDisguisedInTesterTestsLogWithoutViewMysteryShopperPermission()
    {
        $motTestNumber = 999999999014;
        $motTestLogsResult = [
            $this->buildMotTestLogResultItem(
                [
                    'testTypeName' => self::TEST_TYPE_NAME_MYSTERY_SHOPPER,
                    'number' => $motTestNumber
                ]
            )
        ];

        $this->motTestRepository
            ->expects($this->any())
            ->method('getMotTestLogsResult')
            ->willReturn($motTestLogsResult);

        $this->authorizationService
            ->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::VIEW_MYSTERY_SHOPPER_TESTS)
            ->willReturn(true);

        $resultDto = $this->buildService()->findTesterTestsLog($this->buildSearchParams());

        $this->assertEquals(1, $resultDto->getResultCount());
        $this->assertEquals(self::TEST_TYPE_NAME_MYSTERY_SHOPPER, $resultDto->getData()[$motTestNumber]['testType']);
    }

    private function buildMotTestResultItem($motTestNumber, $blah)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn(MotTestStatusName::ABORTED);

        $motTest = new MotTest();
        $motTest
            ->setId(1)
            ->setNumber($motTestNumber)
            ->setStatus($status)
            ->setPrimaryColour((new Colour())->setName('Black'))
            ->setHasRegistration(1)
            ->setOdometerReading(
                (new OdometerReading())
                    ->setResultType(OdometerReadingResultType::OK)
                    ->setValue(10000)
                    ->setUnit('mi')
            )
            ->setVin('1M8GDM9AXKP042788')
            ->setRegistration('FNZ6110')
            ->setMake((new Make())->setName('Renault'))
            ->setModel((new Model())->setName('Clio'))
            ->setMotTestType((new MotTestType())->setDescription($blah))
            ->setVehicleTestingStation(
                (new Site())
                    ->setId(9999)
                    ->setSiteNumber('V1234')
            )
            ->setTester((new Person())->setUsername('tester1'))
            ->setStartedDate(DateUtils::toDateTime('2011-01-01T11:11:11Z'))
            ->setMotTestReasonForCancel((new MotTestReasonForCancel())->setReason([]))
        ;

        return $motTest;
    }

    private function buildMotTestLogResultItem(array $override = [])
    {
        $defaults = [
            'testDate' => '2016-11-07 11:39:25.000000',
            'testDuration' => '48',
            'number' => '100000000000',
            'client_ip' => '0.0.0.0',
            'status' => 'PASSED',
            'registration' => 'FNZ6110',
            'vin' => '1M8GDM9AXKP042788',
            'make_code' => '188A9',
            'makeName' => 'RENAULT',
            'modelName' => 'CLIO',
            'vehicle_class' => '4',
            'siteNumber' => 'V1234',
            'userName' => 'tester1',
            'testTypeName' => self::TEST_TYPE_NAME_NORMAL_TEST,
            'emLogId' => null
        ];

        return array_merge($defaults, $override);
    }

    private function buildSearchParams()
    {
        $searchParams = new MotTestSearchParam($this->entityManager);
        $searchParams
            ->setSiteId(1)
            ->setSiteNumber('V1234')
            ->setDateFrom(new DateTime('1970-01-01'))
            ->setDateTo(new DateTime())
            ->setFormat(SearchParamConst::FORMAT_DATA_TABLES)
            ->setIsApiGetData(true);

        return $searchParams;
    }

    private function buildService()
    {
        return new ElasticSearchService(
            $this->authorizationService,
            $this->siteRepository,
            $this->featureToggles
        );
    }
}