<?php

namespace DvsaMotApiTest\Service;

use DvsaCommon\Auth\PermissionAtSite;
use DateTime;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryItemDto;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\VehicleHistoryService;

/**
 * Unit test for MotTestService.
 */
class VehicleHistoryServiceTest extends AbstractMotTestServiceTest
{
    use TestCasePermissionTrait;

    const VEHICLE_ID = 12345;
    const PERSON_ID = 123;

    public function setUp()
    {
        $this->getMocksForVehicleHistoryService();
    }

    public function testFindHistoricalTestsForVehicleSinceGivenVehicleIdAndStartDateShouldRelayTheThemAsIs()
    {
        $captureVehicleId = ArgCapture::create();
        $captureStartDate = ArgCapture::create();
        $testStartDate = new \DateTime();

        $this->mockMotTestRepository
            ->expects($this->any())
            ->method('findHistoricalTestsForVehicle')
            ->with($captureVehicleId(), $captureStartDate())
            ->will($this->returnValue([]));

        $service = $this->constructVehicleHistoryServiceWithMocks();
        $service->findHistoricalTestsForVehicleSince(self::VEHICLE_ID, $testStartDate);

        $this->assertEquals(self::VEHICLE_ID, $captureVehicleId->get(), "Relayed vehicle id mismatch!");
        $this->assertEquals($testStartDate, $captureStartDate->get(), "Relayed start date mismatch");
    }

    public function testFindHistoricalTestsForVehicleSinceGivenVehicleIdAndNoStartDateShouldRelayTheSameId()
    {
        $captureVehicleId = ArgCapture::create();
        $maxDefaultHistoryLength = MotTestService::CONFIG_PARAM_MAX_VISIBLE_VEHICLE_TEST_HISTORY_IN_MONTHS;

        $testDate = DateUtils::toDate('2012-09-30');

        $this->mockMotTestRepository
            ->expects($this->any())
            ->method('findHistoricalTestsForVehicle')
            ->with($captureVehicleId(), null)
            ->will($this->returnValue([]));

        $this->mockMethod($this->mockConfigurationRepository, "getValue", null, $maxDefaultHistoryLength);
        $this->mockMethod($this->mockAuthService, 'isGranted', null, true);

        $service = $this->constructVehicleHistoryServiceWithMocks();
        $service->findHistoricalTestsForVehicleSince(self::VEHICLE_ID);

        DateUtils::subtractCalendarMonths($testDate, $maxDefaultHistoryLength);
        $this->assertEquals(self::VEHICLE_ID, $captureVehicleId->get(), "Relayed vehicle id mismatch!");
    }

    public function testFindHistoricalTestsForVehicleSinceAsActiveTesterShouldReturnOnlyFirstNormalAndRetestMotTestMarkedAsEditable()
    {
        $this->mockIsGranted($this->mockAuthService, [PermissionInSystem::CERTIFICATE_REPLACEMENT]);

        $this->commonAllowEditCountTest(1, $this->createMotTests());
    }

    public function testFindHistoricalTestsForVehicleSinceAsInactiveTesterShouldReturnTestsWithOverdueDateSetAsUneditable()
    {
        $listOfMotTests = [
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode(MotTestTypeCode::NORMAL_TEST)
                )
                ->setIssuedDate((new \DateTime())->sub(new \DateInterval('P8D')))
                ->setExpiryDate((new \DateTime())->modify('-1 year -2 days')),
        ];

        $this->mockIsGranted($this->mockAuthService, [PermissionInSystem::CERTIFICATE_REPLACEMENT]);

        $this->commonAllowEditCountTest(0, $listOfMotTests);
    }

    public function testFindHistoricalTestsForVehicleSinceAsUserHavingCertificateReplacementFullPermissionShouldReturnAllNormalAndRetestMotTestMarkedAsEditable()
    {
        $this->mockMethod($this->mockAuthService, 'isGranted', null, true);

        $this->commonAllowEditCountTest(2, $this->createMotTests());
    }

    protected function commonAllowEditCountTest($expectedCount, $listOfMotTests)
    {
        $now = new \DateTime();

        $this->mockMethod($this->mockMotTestRepository, 'findHistoricalTestsForVehicle', null, $listOfMotTests);

        $service = $this->constructVehicleHistoryServiceWithMocks();
        $vehicleHistoryDto = $service->findHistoricalTestsForVehicleSince(self::VEHICLE_ID, $now);

        $count = 0;
        foreach ($vehicleHistoryDto->getIterator() as $item) {
            /* @var VehicleHistoryItemDto $item */
            if ($item->isAllowEdit()) {
                $count++;
            }
        }

        $this->assertEquals($expectedCount, $count, "Number of editable Mot tests is incorrect");
    }

    public function testFindHistoricalTestsForVehicleIncludesMysteryShopperTestsInReturnedHistoryWithToggleOn()
    {
        $listOfNormalMotTests = $this->createListOfMotTests(MotTestTypeCode::MYSTERY_SHOPPER);
        $listOfMysteryShopperMotTests = $this->createListOfMotTests(MotTestTypeCode::MYSTERY_SHOPPER);
        $this->mockIsGranted($this->mockAuthService, [PermissionInSystem::CERTIFICATE_REPLACEMENT]);
        $this->mockMethod($this->mockMysteryShopperHelper, 'isMysteryShopperToggleEnabled', null, true);
        $this->mockMethod($this->mockMysteryShopperHelper, 'hasPermissionToMaskAndUnmaskVehicles', null, true);
        $this->mockMethod($this->mockMotTestRepository, 'findHistoricalTestsForVehicle', null, $listOfNormalMotTests);
        $this->mockMethod($this->mockMotTestRepository, 'findHistoricalMysteryShopperTestsForVehicle', null, $listOfMysteryShopperMotTests);

        $service           = $this->constructVehicleHistoryServiceWithMocks();
        $vehicleHistoryDto = $service->findHistoricalTestsForVehicleSince(self::VEHICLE_ID, new DateTime());

        $countOfMotTests = $vehicleHistoryDto->getIterator()->count();
        $this->assertEquals(4, $countOfMotTests, "Number of vehicle history Mot tests is incorrect");
    }

    public function testFindHistoricalTestsForVehicleExcludesMysteryShopperTestsInReturnedHistoryWithToggleOff()
    {
        $listOfNormalMotTests = $this->createListOfMotTests(MotTestTypeCode::MYSTERY_SHOPPER);
        $listOfMysteryShopperMotTests = $this->createListOfMotTests(MotTestTypeCode::MYSTERY_SHOPPER);
        $this->mockIsGranted($this->mockAuthService, [PermissionInSystem::CERTIFICATE_REPLACEMENT]);
        $this->mockMethod($this->mockMysteryShopperHelper, 'isMysteryShopperToggleEnabled', null, false);
        $this->mockMethod($this->mockMysteryShopperHelper, 'hasPermissionToMaskAndUnmaskVehicles', null, true);
        $this->mockMethod($this->mockMotTestRepository, 'findHistoricalTestsForVehicle', null, $listOfNormalMotTests);
        $this->mockMethod($this->mockMotTestRepository, 'findHistoricalMysteryShopperTestsForVehicle', null, $listOfMysteryShopperMotTests);

        $service           = $this->constructVehicleHistoryServiceWithMocks();
        $vehicleHistoryDto = $service->findHistoricalTestsForVehicleSince(self::VEHICLE_ID, new DateTime());

        $countOfMotTests = $vehicleHistoryDto->getIterator()->count();
        $this->assertEquals(2, $countOfMotTests, "Number of vehicle history Mot tests is incorrect");
    }

    protected function checkAllowEditForTest($testId, $listOfMotTests, $expectedOutput)
    {
        $now = new \DateTime();

        $this->mockMethod($this->mockMotTestRepository, 'findHistoricalTestsForVehicle', null, $listOfMotTests);

        $this->mockMethod($this->mockPersonRepository, 'get', null, $this->setPersonMock());

        $service = $this->constructVehicleHistoryServiceWithMocks();
        $editAllowedDto = $service->getEditAllowedPermissionsDto(self::VEHICLE_ID, self::PERSON_ID, $testId, $now);

        $this->assertEquals($expectedOutput, $editAllowedDto->getEditAllowed(), "Allow edit permissions are not correct");
    }

    private function setPersonMock()
    {
        $person = new Person();

        $authorisationForTestingMotClass1 = new AuthorisationForTestingMot();
        $authorisationForTestingMotClass1
            ->setStatus((new AuthorisationForTestingMotStatus())
                ->setCode(AuthorisationForTestingMotStatusCode::QUALIFIED))
            ->setVehicleClass((new VehicleClass())->setCode(1));

        $authorisationForTestingMotClass3 = new AuthorisationForTestingMot();
        $authorisationForTestingMotClass3
            ->setStatus((new AuthorisationForTestingMotStatus())
                ->setCode(AuthorisationForTestingMotStatusCode::QUALIFIED))
            ->setVehicleClass((new VehicleClass())->setCode(3));


        $person->setAuthorisationsForTestingMot([$authorisationForTestingMotClass1, $authorisationForTestingMotClass3]);

        return $person;
    }

    protected function getMocksForVehicleHistoryService()
    {
        $this->mockAuthService = $this->getMockAuthorizationService(false);
        $this->mockPersonRepository = $this->getMockRepository(PersonRepository::class);
        $this->mockMotTestRepository = $this->getMockRepository(MotTestRepository::class);
        $this->mockConfigurationRepository = $this->getMockWithDisabledConstructor(ConfigurationRepository::class);
        $this->mockMysteryShopperHelper = $this->getMockWithDisabledConstructor(MysteryShopperHelper::class);
    }

    protected function constructVehicleHistoryServiceWithMocks() //$mocks)
    {
        $vehicleHistoryService = new VehicleHistoryService(
            $this->mockPersonRepository,
            $this->mockMotTestRepository,
            $this->mockAuthService,
            $this->mockConfigurationRepository,
            $this->mockMysteryShopperHelper
        );

        XMock::mockClassField($vehicleHistoryService, 'dateTimeHolder', new TestDateTimeHolder(new \DateTime()));

        return $vehicleHistoryService;
    }

    /**
     * @return MotTest[]
     */
    protected function createMotTests()
    {
        return [
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::ABANDONED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode(MotTestTypeCode::NORMAL_TEST)
                )
                ->setIssuedDate(new \DateTime()),
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode(MotTestTypeCode::NORMAL_TEST)
                )
                ->setIssuedDate(new \DateTime()),
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode(MotTestTypeCode::OTHER)
                )
                ->setIssuedDate(new \DateTime()),
        ];
    }

    protected function createMotTestStatus($name)
    {
        /** @var MotTestStatus|PHPUnit_Framework_MockObject_MockObject $status */
        $status = XMock::of(MotTestStatus::class);
        $this->mockMethod($status, "getName", null, $name);

        return $status;
    }

    /** @dataProvider testIdAndExpectedForTesterOutputForEditPermissions
     * @param $testId
     * @param $expectedOutput
     */
    public function testGetEditAllowedPermissionsDtoAsActiveTesterForSpecificTestIdShouldBe($testId, $expectedOutput)
    {
        $this->mockIsGranted(
            $this->mockAuthService,
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::VEHICLE_MOT_TEST_HISTORY_READ
            ]);

        $this->mockIsGrantedAtSite(
            $this->mockAuthService,
            [
                PermissionAtSite::MOT_TEST_PERFORM_AT_SITE
            ],
            1
        );

        $tests = $this->createMotTestsWithIds();

        $this->checkAllowEditForTest($testId, $tests, $expectedOutput);
    }

    /** @dataProvider testIdAndExpectedForAOOutputForEditPermissions
     * @param $testId
     * @param $expectedOutput
     */
    public function testGetEditAllowedPermissionsDtoAsAreaOfficeForSpecificTestIdShouldBe($testId, $expectedOutput)
    {
        $this->mockIsGranted(
            $this->mockAuthService,
            [
                PermissionInSystem::CERTIFICATE_REPLACEMENT,
                PermissionInSystem::VEHICLE_MOT_TEST_HISTORY_READ,
                PermissionInSystem::CERTIFICATE_REPLACEMENT_FULL,
            ]);

        $tests = $this->createMotTestsWithIds();

        $this->checkAllowEditForTest($testId, $tests, $expectedOutput);
    }

    public function testIdAndExpectedForAOOutputForEditPermissions()
    {
        return [
            [
                'testId' => '1',
                'expectedOutput' => false,
            ],
            [
                'testId' => '2',
                'expectedOutput' => true,
            ],
            [
                'testId' => '3',
                'expectedOutput' => true,
            ],
            [
                'testId' => '4',
                'expectedOutput' => true,
            ],
            [
                'testId' => '5',                //notExistingTest
                'expectedOutput' => false,
            ],
        ];
    }

    public function testIdAndExpectedForTesterOutputForEditPermissions()
    {
        return [
            [
                'testId' => '1',
                'expectedOutput' => false,
            ],
            [
                'testId' => '2',
                'expectedOutput' => false,
            ],
            [
                'testId' => '3',
                'expectedOutput' => true,
            ],
            [
                'testId' => '4',
                'expectedOutput' => false,
            ],
            [
                'testId' => '5',                //notExistingTest
                'expectedOutput' => false,
            ],
        ];
    }

    /**
     * @return MotTest[]
     */
    protected function createMotTestsWithIds()
    {
        return [
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::ABANDONED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode(MotTestTypeCode::NORMAL_TEST)
                )
                ->setVehicleTestingStation(
                    (new Site())
                        ->setId(1)
                )
                ->setVehicleClass((new VehicleClass())
                    ->setCode(1))
                ->setNumber(1)
                ->setIssuedDate(new \DateTime()),
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::FAILED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode(MotTestTypeCode::NORMAL_TEST)
                )
                ->setVehicleTestingStation(
                    (new Site())
                        ->setId(2)
                )
                ->setVehicleClass((new VehicleClass())
                    ->setCode(2))
                ->setNumber(2)
                ->setIssuedDate(new \DateTime()),
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode(MotTestTypeCode::NORMAL_TEST)
                )
                ->setVehicleClass((new VehicleClass())
                    ->setCode(3))
                ->setVehicleTestingStation(
                    (new Site())
                        ->setId(1)
                )
                ->setNumber(3)
                ->setIssuedDate(new \DateTime()),
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode(MotTestTypeCode::OTHER)
                )
                ->setVehicleClass((new VehicleClass())
                    ->setCode(7))
                ->setVehicleTestingStation(
                    (new Site())
                        ->setId(2)
                )
                ->setNumber(4)
                ->setIssuedDate(new \DateTime()),
        ];
    }


    /**
     * @param String $MotTestTypeCode
     * @return MotTest[]
     */
    private function createListOfMotTests($MotTestTypeCode)
    {
        $listOfNormalMotTests = [(new MotTest())
            ->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED))
            ->setMotTestType(
                (new MotTestType())
                    ->setCode($MotTestTypeCode)
            )
            ->setIssuedDate(new DateTime()),
            (new MotTest())
                ->setStatus($this->createMotTestStatus(MotTestStatusName::FAILED))
                ->setMotTestType(
                    (new MotTestType())
                        ->setCode($MotTestTypeCode)
                )
                ->setIssuedDate(new DateTime())];
        return $listOfNormalMotTests;
    }
}
