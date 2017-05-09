<?php

namespace DvsaElasticSearchTest\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Constants\SearchParamConst;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonTest\TestUtils\XMock;
use DvsaElasticSearch\Service\ElasticSearchService;
use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestCancelled;
use DvsaEntities\Entity\MotTestReasonForCancel;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaFeature\FeatureToggles;
use PHPUnit_Framework_TestCase;

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
                    'number' => $motTestNumber,
                ]
            ),
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
                    'number' => $motTestNumber,
                ]
            ),
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
        $site->setName('Site 1');
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
                    'number' => $motTestNumber,
                ]
            ),
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
                    'number' => $motTestNumber,
                ]
            ),
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
            ->method('getName')
            ->willReturn(MotTestStatusName::ABORTED);

        $model = (new Model())->setName('Clio');
        $model->setMake((new Make())->setName('Renault'));

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model);

        $vehicle = new Vehicle();
        $vehicle->setVersion(1);
        $vehicle->setColour((new Colour())->setName('Black'));
        $vehicle->setRegistration('FNZ6110');
        $vehicle->setVin('1M8GDM9AXKP042788');
        $vehicle->setModelDetail($modelDetail);

        $motTestCancelled = new MotTestCancelled();
        $motTestCancelled->setMotTestReasonForCancel(
            (new MotTestReasonForCancel())->setReason([])
        );

        $motTest = new MotTest();
        $motTest
            ->setVehicleVersion(1)
            ->setId(1)
            ->setNumber($motTestNumber)
            ->setStatus($status)
            ->setHasRegistration(1)
            ->setOdometerValue(10000)
            ->setOdometerUnit(OdometerUnit::MILES)
            ->setOdometerResultType(OdometerReadingResultType::OK)
            ->setMotTestType((new MotTestType())->setDescription($blah))
            ->setVehicleTestingStation(
                (new Site())
                    ->setId(9999)
                    ->setSiteNumber('V1234')
            )
            ->setTester((new Person())->setUsername('tester1'))
            ->setStartedDate(DateUtils::toDateTime('2011-01-01T11:11:11Z'))
            ->setMotTestCancelled($motTestCancelled)
            ->setVehicle($vehicle);

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
            'emLogId' => null,
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
