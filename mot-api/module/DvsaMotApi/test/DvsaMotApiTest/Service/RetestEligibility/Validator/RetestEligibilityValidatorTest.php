<?php

namespace DvsaMotApiTest\Service\Validator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\NonWorkingDayCountry;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityCheckCode;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use NonWorkingDaysApi\Constants\CountryCode;
use NonWorkingDaysApi\NonWorkingDaysHelper;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use DvsaCommonApi\Service\Exception\NotFoundException;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

/**
 * Class RetestEligibilityValidatorTest
 */
class RetestEligibilityValidatorTest extends \PHPUnit_Framework_TestCase
{
    const VEHICLE_ID = 44;
    const TEST_VTS_ID = 4;

    /** @var  \DateTime */
    private $currentDate;
    /** @var  MotTest|MockObj */
    private $mockMotTest;

    /** @var  MotTestRepository|MockObj */
    private $motTestRepository;

    private $nonWorkingDaysHelper;

    private $dateTimeHolder;

    /**
     * @var MysteryShopperHelper
     */
    private $mysteryShopperHelper;

    /** @var SpecialNoticeService */
    private $specialNoticeService;

    public function setUp()
    {
        $this->currentDate = DateUtils::toDate("2014-01-10");
        $this->dateTimeHolder = new TestDateTimeHolder($this->currentDate);
        $this->mockMotTest = $this->mockMotTest($this->currentDate);
        $this->motTestRepository = $this->setupMotTestRepositoryMock();
        $this->setUpNonWorkingDaysHelper(
            $this->any(), DateUtils::toDate("2014-01-10"), DateUtils::toDate("2014-01-10"), true
        );
        $this->mysteryShopperHelper = XMock::of(MysteryShopperHelper::class);
        $this->specialNoticeService = XMock::of(SpecialNoticeService::class);
    }

    public function testCheckVehicleIsEligibleForRetest()
    {
        //given
        $this->setupMotTestRepositoryMockReturnsLastNormalMotTest($this->motTestRepository, $this->mockMotTest);
        $this->setupMotTestRepositoryMockFindRetestForNormalTest($this->motTestRepository, null);

        // when
        $validator = $this->createRetestEligibilityValidator();
        $isEligible = $validator->checkEligibilityForRetest(self::VEHICLE_ID, self::TEST_VTS_ID);

        // then
        $this->assertTrue($isEligible);
    }

    /**
     * @dataProvider getMotTestCancelledStatus
     */
    public function testCheckVehicleIsEligibleForRetestWhenTestCancelledAtDifferentSite($motTestCancelledStatus)
    {
        //Given
        $cancelledTest = $this->mockMotTest($this->currentDate, $motTestCancelledStatus);
        $cancelledTest->getVehicleTestingStation()->setId(5);

        $this
            ->motTestRepository
            ->expects($this->at(0))
            ->method("findLastNormalTest")
            ->willReturn($cancelledTest);

        $this
            ->motTestRepository
            ->expects($this->at(1))
            ->method("findLastNormalTest")
            ->willReturn($this->mockMotTest($this->currentDate, MotTestStatusName::FAILED));

        $this
            ->motTestRepository
            ->expects($this->at(0))
            ->method("countNotAbortedTests")
            ->willReturn(0);

        // when
        $validator = $this->createRetestEligibilityValidator();
        $isEligible = $validator->checkEligibilityForRetest(self::VEHICLE_ID, 6);

        // then
        $this->assertTrue($isEligible);
    }

    public function getMotTestCancelledStatus()
    {
        return [
            [MotTestStatusName::ABORTED],
            [MotTestStatusName::ABORTED_VE],
            [MotTestStatusName::ABANDONED],
        ];
    }

    private function exampleNonWorkingDayCountry()
    {
        $country = new CountryOfRegistration();
        $country->setCode(CountryCode::ENGLAND);
        $nonWorkingDayCountry = new NonWorkingDayCountry();
        $nonWorkingDayCountry->setCountry($country);
        return $nonWorkingDayCountry;
    }

    /**
     * @dataProvider getMotTestStatus
     */
    public function testCheckVehicleReturnsProperErrorCodeWhenTestNotCancelledAtDifferentSite($motTestStatus, $expectedRetestEligibilityCheckCode)
    {
        //Given
        $this
            ->motTestRepository
            ->expects($this->at(0))
            ->method("findLastNormalTest")
            ->willReturn($this->mockMotTest($this->currentDate, $motTestStatus));

        $validator = $this->createRetestEligibilityValidator();

        // when
        $checkResult = XMock::invokeMethod($validator, 'validateVehicleForRetest', [self::VEHICLE_ID, 12345]);

        // then
        $this->assertEquals(
            [$expectedRetestEligibilityCheckCode],
            $checkResult
        );
    }

    public function getMotTestStatus()
    {
        return [
            [MotTestStatusName::PASSED, RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED],
            [MotTestStatusName::ACTIVE, RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED],
            [MotTestStatusName::REFUSED, RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED],
            [MotTestStatusName::FAILED, RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_AT_A_DIFFERENT_VTS],
        ];
    }

    public function testCheckVehicleReturnsProperErrorCodeWhenTestPerformedAtDifferentSite()
    {
        //Given
        $cancelledTest = $this->mockMotTest($this->currentDate, MotTestStatusName::ABORTED);
        $cancelledTest->getVehicleTestingStation()->setId(5);

        $this
            ->motTestRepository
            ->expects($this->at(0))
            ->method("findLastNormalTest")
            ->willReturn($cancelledTest);

        $this
            ->motTestRepository
            ->expects($this->at(1))
            ->method("findLastNormalTest")
            ->willReturn($this->mockMotTest($this->currentDate, MotTestStatusName::FAILED));

        $this
            ->motTestRepository
            ->expects($this->at(2))
            ->method("countNotCancelledTests")
            ->willReturn(1);

        $validator = $this->createRetestEligibilityValidator();

        // when
        $checkResult = XMock::invokeMethod($validator, 'validateVehicleForRetest', [self::VEHICLE_ID, self::TEST_VTS_ID]);

        // then
        $this->assertEquals(
            [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_AT_A_DIFFERENT_VTS],
            $checkResult
        );
    }

    public function testValidateVehicleForRetest_twoTests_vtsAandB_cancelledAtA_notEligibleAtB()
    {
        //Given
        $cancelledTest = $this->mockMotTest($this->currentDate, MotTestStatusName::ABORTED);
        $cancelledTest->getVehicleTestingStation()->setId(5);

        $this
            ->motTestRepository
            ->expects($this->at(0))
            ->method("findLastNormalTest")
            ->willReturn($cancelledTest);

        $this
            ->motTestRepository
            ->expects($this->at(1))
            ->method("findLastNormalTest")
            ->willReturn(null);

        $this
            ->motTestRepository
            ->expects($this->never())
            ->method("countNotCancelledTests");

        $validator = $this->createRetestEligibilityValidator();

        // when
        $checkResult = XMock::invokeMethod($validator, 'validateVehicleForRetest', [self::VEHICLE_ID, self::TEST_VTS_ID]);

        // then
        $this->assertEquals(
            [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED],
            $checkResult
        );
    }

    private function mockMotTest(\DateTime $completedDate, $status = null)
    {
        $site = new Site();
        $contactDetail = (new ContactDetail())
            ->setAddress(
                (new Address())
                    ->setTown("England")
            );
        $site
            ->setId(self::TEST_VTS_ID)
            ->setContact($contactDetail, (new SiteContactType())->setCode(SiteContactTypeCode::BUSINESS));

        $site->setNonWorkingDayCountry($this->exampleNonWorkingDayCountry());

        $vehicleClass = new VehicleClass();
        $vehicleClass->setId(1);
        $vehicleClass->setCode("1");

        $motTest = new MotTest();
        $motTest
            ->setId(4)
            ->setCompletedDate($completedDate)
            ->setStatus(
                $this->mockMotTestStatus($status ?: MotTestStatusName::FAILED)
            )
            ->setVehicleTestingStation($site)
            ->setVehicleClass($vehicleClass)
        ;

        return $motTest;
    }

    private function setupMotTestRepositoryMockReturnsLastNormalMotTest(MockObj $motTestRepository, $motTest)
    {
        $motTestRepository->expects($this->any())
            ->method("findLastNormalTest")
            ->withAnyParameters()
            ->willReturn($motTest);
    }

    private function setupMotTestRepositoryMockFindRetestForNormalTest(MockObj $motTestRepository, $retest)
    {
        $motTestRepository->expects($this->any())
            ->method("findRetestForMotTest")
            ->withAnyParameters()
            ->willReturn($retest);
    }

    public function testCheckVehicleLastTestNotFoundReturnsProperErrorCode()
    {
        $validator = $this->createRetestEligibilityValidator();

        // when
        $checkResult = XMock::invokeMethod($validator, 'validateVehicleForRetest', [self::VEHICLE_ID, 99999]);

        // then
        $this->assertEquals(
            [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_NEVER_PERFORMED],
            $checkResult
        );
    }

    public function testCheckVehicleLastTestNotFailedReturnsProperErrorCode()
    {
        //given
        $motTest = $this->mockMotTest($this->currentDate, MotTestStatusName::PASSED);
        $validator = $this->createRetestEligibilityValidator();
        $this->setupMotTestRepositoryMockReturnsLastNormalMotTest($this->motTestRepository, $motTest);

        // when
        $checkResult = XMock::invokeMethod(
            $validator, 'validateVehicleForRetest', [self::VEHICLE_ID, self::TEST_VTS_ID]
        );

        // then
        $this->assertEquals(
            [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_WAS_NOT_FAILED],
            $checkResult
        );
    }

    private function callValidateVehicleForRetestAtVts($vtsId)
    {
        return XMock::invokeMethod(
            $this->createRetestEligibilityValidator(),
            'validateVehicleForRetest',
            [self::VEHICLE_ID, $vtsId]
        );
    }

    public static function dataProviderTestVerify()
    {
        return [
            [
                [
                    'refDate' => '2014-01-09',
                    'nthDate' => '2014-01-10',
                    'result' => true
                ]
            ],
            [
                [
                    'refDate' => '2014-01-10',
                    'nthDate' => '2014-01-10',
                    'result' => true
                ]
            ],
            [
                [
                    'refDate' => '2014-01-11',
                    'nthDate' => '2014-01-10',
                    'result' => false
                ]
            ],
        ];
    }

    /** @dataProvider dataProviderTestVerify */
    public function testVerify($testCase)
    {
        $refDate = $testCase['refDate'];
        $refDate = DateUtils::toDate($refDate);

        $tenWorkingDayDate = $testCase['nthDate'];
        $result = $testCase['result'];
        $motTestCompletedDate = DateUtils::toDateTime('2014-01-01T19:20:20Z');
        $motTest = $this->mockMotTest($motTestCompletedDate);
        $this->setupMotTestRepositoryMockReturnsLastNormalMotTest($this->motTestRepository, $motTest);
        $this->setCurrentDate($refDate);

        $this->setUpNonWorkingDaysHelper(
            $this->atLeastOnce(),
            DateUtils::cropTime($motTestCompletedDate),
            $tenWorkingDayDate
        );

        // when
        $checkResult = $this->callValidateVehicleForRetestAtVts($motTest->getVehicleTestingStation()->getId());

        $response = $result ? [] :
            [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_MORE_THAN_10_WORKING_DAYS];

        // then
        $this->assertEquals($response, $checkResult);
    }

    public function testCheckVehicleOriginalTestPerformedAtDifferentVts()
    {
        // given
        $this->setupMotTestRepositoryMockReturnsLastNormalMotTest($this->motTestRepository, $this->mockMotTest);
        $validator = $this->createRetestEligibilityValidator();
        $differentVtsId = self::TEST_VTS_ID + 1;

        // when
        $checkResult = XMock::invokeMethod(
            $validator, 'validateVehicleForRetest', [self::VEHICLE_ID, $differentVtsId]
        );

        // then
        $this->assertEquals(
            [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_PERFORMED_AT_A_DIFFERENT_VTS],
            $checkResult
        );
    }

    public function testCheckVehicleOriginalTestCancelled()
    {
        //given
        $motTest = $this->mockMotTest($this->currentDate, MotTestStatusName::ABORTED);
        $validator = $this->createRetestEligibilityValidator();
        $this->setupMotTestRepositoryMockReturnsLastNormalMotTest($this->motTestRepository, $motTest);

        // when
        $checkResult = XMock::invokeMethod(
            $validator, 'validateVehicleForRetest', [self::VEHICLE_ID, self::TEST_VTS_ID]
        );

        // then
        $this->assertEquals(
            [RetestEligibilityCheckCode::RETEST_REJECTED_ORIGINAL_CANCELLED],
            $checkResult
        );
    }

    public function testCheckVehicleRetestAlreadyRegistered()
    {
        //given
        $retest = clone $this->mockMotTest;
        $validator = $this->createRetestEligibilityValidator();
        $this->setupMotTestRepositoryMockReturnsLastNormalMotTest($this->motTestRepository, $this->mockMotTest);
        $this->setupMotTestRepositoryMockFindRetestForNormalTest($this->motTestRepository, $retest);

        // when
        $checkResult = XMock::invokeMethod(
            $validator, 'validateVehicleForRetest', [self::VEHICLE_ID, self::TEST_VTS_ID]
        );

        // then
        $this->assertEquals(
            [RetestEligibilityCheckCode::RETEST_REJECTED_ALREADY_REGISTERED],
            $checkResult
        );
    }

    public function testCheckEligibilityForRetestReturnsException()
    {
        $this->setupMotTestRepositoryMockReturnsLastNormalMotTest($this->motTestRepository, $this->mockMotTest);

        $this->setExpectedException(BadRequestException::class, 'Vehicle is not eligible for a retest');

        $this->createRetestEligibilityValidator()->checkEligibilityForRetest(self::VEHICLE_ID, 99999);
    }

    public function testCheckEligibilityForRetestReturnsExceptionWhenSiteDoesNotHaveNoNonWorkingDayCountryDefined()
    {
        $motTest = $this->mockMotTest($this->currentDate, null, null);
        $motTest->getVehicleTestingStation()->setNonWorkingDayCountry(null);
        $this->setupMotTestRepositoryMockReturnsLastNormalMotTest($this->motTestRepository, $motTest);

        $this->setExpectedException(NotFoundException::class, "Vts country required");

        $this
            ->createRetestEligibilityValidator()
            ->checkEligibilityForRetest(self::VEHICLE_ID, 99999);
    }

    private function mockMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn($name);

        return $status;
    }

    /**
     * @param $isWithinPeriod
     * @param $expects
     * @return RetestEligibilityValidator
     */
    private function createRetestEligibilityValidator()
    {
        return (new RetestEligibilityValidator(
            $this->nonWorkingDaysHelper,
            $this->motTestRepository,
            $this->mysteryShopperHelper,
            $this->specialNoticeService
        ))->setDateTimeHolder($this->dateTimeHolder);
    }

    private function setCurrentDate(\DateTime $date)
    {
        $this->dateTimeHolder = new TestDateTimeHolder($date);
    }

    private function setUpNonWorkingDaysHelper($expects, $completionDate, $returnedDate)
    {
        $this->nonWorkingDaysHelper = XMock::of(NonWorkingDaysHelper::class);
        $this->nonWorkingDaysHelper->expects($expects)->method('calculateNthWorkingDayAfter')
            ->with($completionDate, 10, $this->exampleNonWorkingDayCountry()->getCountry()->getCode())
            ->willReturn($returnedDate);
    }

    private function setupMotTestRepositoryMock()
    {
        return XMock::of(MotTestRepository::class);
    }
}
