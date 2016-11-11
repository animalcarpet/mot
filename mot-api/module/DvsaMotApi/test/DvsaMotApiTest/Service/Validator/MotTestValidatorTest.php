<?php
namespace DvsaMotApiTest\Service\Validator;

use CensorApi\Service\CensorService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\ReasonForRejection as ReasonForRejectionConstants;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSite;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntitiesTest\Entity\SiteTest;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Service\Validator\MotTestValidator;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use PHPUnit_Framework_TestCase;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\AuthenticationServiceInterface;

/**
 * Class MotTestValidatorTest
 */
class MotTestValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @var MotTestValidator $motTestValidator */
    private $motTestValidator;

    /**
     * @var CensorService $censorServiceMock
     */
    private $censorServiceMock;

    /** @var AuthorisationServiceInterface $motAuthorizationService */
    private $motAuthorizationService;

    /** @var AuthenticationService $motIdentityProvider */
    private $motIdentityProvider;

    /** @var SpecialNoticeService $specialNoticeService */
    private $specialNoticeService;

    /** @var FeatureToggles $featureToggles */
    private $featureToggles;

    const PROFANITY_DETECTED = true;
    const PROFANITY_NOT_DETECTED = false;

    const LOGGED_IN_USER_ID = 123;

    public function setUp()
    {
        $this->censorServiceMock = XMock::of(CensorService::class);
        $this->motIdentityProvider = XMock::of(\Zend\Authentication\AuthenticationService::class);
        $this->motAuthorizationService = XMock::of(\DvsaAuthorisation\Service\AuthorisationServiceInterface::class);

        $this->motIdentityProvider->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue(new MotIdentity(self::LOGGED_IN_USER_ID, null)));

        $this->specialNoticeService = XMock::of(SpecialNoticeService::class);

        $this->featureToggles = XMock::of(FeatureToggles::class);

        $this->motTestValidator = new MotTestValidator(
            $this->censorServiceMock,
            $this->motAuthorizationService,
            $this->motIdentityProvider,
            $this->specialNoticeService,
            $this->featureToggles
        );
        parent::setUp();
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testValidateNewMotTestThrowsRequiredFieldExceptionForNullPrimaryColour()
    {
        $motTest = $this->setupMotTest(new Person(), new Vehicle(), new Site(), true, null);
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testValidateNewMotTestThrowsRequiredFieldExceptionForNullSecondaryColour()
    {
        $motTest = $this->setupMotTest(new Person(), new Vehicle(), new Site(), true, 'B', true, null);
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    public function testValidateMotTestReasonForRejectionPassesWithValidRfr()
    {
        $rfr = (new MotTestReasonForRejection())
            ->setReasonForRejection(
                (new ReasonForRejection())
                    ->addVehicleClass(
                        (new VehicleClass(VehicleClassCode::CLASS_4))
                    )
            )->setMotTest(
                (new MotTest())
                    ->setVehicleClass(
                        (new VehicleClass(VehicleClassCode::CLASS_4))
                    )
            );
        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    public function testValidateMotTestReasonForRejectionPassesWithCustomRfr()
    {
        $rfr = (new MotTestReasonForRejection())
            ->setCustomDescription('Manual advisory description');

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Either RFR Id or description has to be provided
     */
    public function testValidateMotTestReasonForRejectionThrowsExceptionWhenCustomRfrHasNoDescription()
    {
        $rfr = new MotTestReasonForRejection();

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Maximum length of description is
     */
    public function testValidateMotTestReasonForRejectionThrowsExceptionWhenCommentIsToLong()
    {
        $rfr = new MotTestReasonForRejection();
        $rfr->setReasonForRejection(new ReasonForRejection());
        $rfr->setComment(str_repeat("X", ReasonForRejectionConstants::MAX_DESCRIPTION_LENGTH + 1));

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Maximum length of description is
     */
    public function testValidateMotTestReasonForRejectionThrowsExceptionWhenCustomDescriptionIsToLong()
    {
        $rfr = new MotTestReasonForRejection();
        $rfr->setReasonForRejection(new ReasonForRejection());
        $rfr->setCustomDescription(str_repeat("X", ReasonForRejectionConstants::MAX_DESCRIPTION_LENGTH + 1));

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Profanity has been detected in the description of RFR
     */
    public function testValidateMotTestReasonForRejectionProfanityDetected()
    {
        $textUnderProfanityTest = "badword";

        $rfr = new MotTestReasonForRejection();
        $rfr->setCustomDescription($textUnderProfanityTest);
        $this->profanityCheckResult($textUnderProfanityTest, self::PROFANITY_DETECTED);
        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Original RFR cannot be changed
     */
    public function test_validateMotTestReasonForRejection_throws_an_exception_for_original_Rfrs()
    {
        $rfr = (new MotTestReasonForRejection())
            ->setReasonForRejection(new ReasonForRejection())
            ->setOnOriginalTest(true);

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    public function test_validateMotTestReasonForRejection_passes_with_null_end_date()
    {
        $rfr = (new MotTestReasonForRejection())
            ->setReasonForRejection(
                (new ReasonForRejection())
                    ->setEndDate(null)
                    ->addVehicleClass((new VehicleClass(VehicleClassCode::CLASS_4)))
            )
            ->setMotTest((new MotTest())->setVehicleClass((new VehicleClass(VehicleClassCode::CLASS_4))));

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    public function test_validateMotTestReasonForRejection_passes_with_end_date_in_future()
    {
        $rfr = (new MotTestReasonForRejection())
            ->setReasonForRejection(
                (new ReasonForRejection())
                    ->setEndDate(DateUtils::today()->modify('+1 day'))
                    ->addVehicleClass((new VehicleClass(VehicleClassCode::CLASS_4)))
            )
            ->setMotTest((new MotTest())->setVehicleClass((new VehicleClass(VehicleClassCode::CLASS_4))));

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage End-dated RFR can not be added
     */
    public function test_validateMotTestReasonForRejection_throws_an_exception_for_an_end_dated_Rfr()
    {
        $rfr = (new MotTestReasonForRejection())
            ->setReasonForRejection((new ReasonForRejection())->setEndDate(DateUtils::toDate('2000-06-15')));

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage This RFR cannot be added to a vehicle of this class
     */
    public function test_validateMotTestReasonForRejection_throws_an_exception_for_adding_wrong_class_rfr_to_vehicle()
    {
        $rfr = (new MotTestReasonForRejection())
            ->setReasonForRejection(
                (new ReasonForRejection())
                    ->addVehicleClass(
                        (new VehicleClass(VehicleClassCode::CLASS_1))
                    )
            )->setMotTest(
                (new MotTest())
                    ->setVehicleClass(
                        (new VehicleClass(VehicleClassCode::CLASS_4))
                    )
            );

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    public function testAssertCanBeUpdatedDoesNotThrowAnExceptionForTestsInProgress()
    {
        $this->setIsGrantedAtSite(true);

        $motTest = (new MotTest())
            ->setStatus($this->createMotTestStatus(MotTestStatusName::ACTIVE))
            ->setTester((new Person())->setId(self::LOGGED_IN_USER_ID))
            ->setVehicleTestingStation((new Site())->setId(1));
        $this->motTestValidator->assertCanBeUpdated($motTest);
    }

    // TODO: refactor when status enum is ready (to tests all statuses)
    public function testAssertCanBeUpdatedThrowsAnExceptionForFinishedTests()
    {
        $this->setExpectedException(ForbiddenException::class);

        $motTest = (new MotTest())
            ->setStatus($this->createMotTestStatus(MotTestStatusName::ABORTED))
            ->setTester((new Person())->setId(self::LOGGED_IN_USER_ID))
            ->setVehicleTestingStation((new Site())->setId(1));
        $this->motTestValidator->assertCanBeUpdated($motTest);
    }

    public function test_assertCanBeUpdated_throws_an_exception_for_tester_that_is_not_the_test_creator()
    {
        $this->setExpectedException(ForbiddenException::class);
        $motTest = (new MotTest())
            ->setStatus($this->createMotTestStatus(MotTestStatusName::ABORTED))
            ->setTester((new Person())->setId(self::LOGGED_IN_USER_ID + 999));
        $this->motTestValidator->assertCanBeUpdated($motTest);
    }

    public function testNewMotTestNotThrowsBadRequestExceptionNotLinkedToVtsForVehicleExaminer()
    {
        $this->setIsVehicleExaminer(true);

        $site = new Site();
        $site->setId(1);

        $motTest = $this->setupMotTest(new Person(), new Vehicle(), $site, false);

        $this->motTestValidator->validateNewMotTest($motTest);
    }


    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function testValidateNewMotTestThrowsForbiddenIfTesterNotAcknowledgingTheSpecialNotice()
    {
        $this->specialNoticeService
            ->expects($this->any())
            ->method("countOverdueSpecialNoticesForClass")
            ->willReturn(1);

        $motTest = $this->setupMotTest(new Person(), new Vehicle(), new Site());
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateNewMotTestThrowsBadRequestIfNoVehicleTestingStation()
    {
        $motTest = $this->setupMotTest(new Person(), new Vehicle(), null, false);

        $this->motTestValidator->validateNewMotTest($motTest);
    }

    public function testValidateNewMotTestDoesNotThrowBadRequestIfNoVehicleTestingStationButNonMotTest()
    {
        $this->setIsVehicleExaminer(true);

        $nonMotTestType = (new MotTestType())
            ->setCode(MotTestTypeCode::NON_MOT_TEST)
            ->setIsSlotConsuming(false);

        $motTest = $this->setupMotTest(new Person(), new Vehicle(), null, false);
        $motTest->setMotTestType($nonMotTestType);

        $this->motTestValidator->validateNewMotTest($motTest);
    }

    protected function setupMotTest(
        Person $nominatedTester,
        $vehicle,
        $vehicleTestingStation,
        $isSlotConsuming = true,
        $primaryColourCode = 'B',
        $hasRegistration = 1,
        $secondaryColourCode = 'X'
    ) {
        $motTest = new MotTest();
        $motTest->setTester($nominatedTester)
            ->setMotTestType((new MotTestType())->setCode('UT-CODE')->setIsSlotConsuming($isSlotConsuming))
            ->setVehicle($vehicle)
            ->setVehicleTestingStation($vehicleTestingStation)
            ->setPrimaryColour($primaryColourCode === null ? null : (new Colour())->setCode($primaryColourCode))
            ->setSecondaryColour($secondaryColourCode === null ? null : (new Colour())->setCode($secondaryColourCode))
            ->setFuelType(new FuelType())
            ->setVehicleClass(new VehicleClass('4', '4'))
            ->setHasRegistration($hasRegistration);

        return $motTest;
    }

    protected function profanityCheckResult($textUnderTest, $result)
    {
        $this->censorServiceMock->expects($this->any())
            ->method('containsProfanity')->with($textUnderTest)->will($this->returnValue($result));
    }

    protected function getMockWithDisabledConstructor($mockClass)
    {
        return \DvsaCommonTest\TestUtils\XMock::of($mockClass);
    }

    protected function setupMockForCalls(
        $mock,
        $method,
        $returnValue,
        $with = null,
        $once = false
    ) {
        $times = $once ? $this->once() : $this->any();
        if (!$with) {
            $mock->expects($times)
                ->method($method)
                ->will($this->returnValue($returnValue));
        } else {
            $mock->expects($times)
                ->method($method)
                ->with($with)
                ->will($this->returnValue($returnValue));
        }

        return $mock;
    }

    private function setIsVehicleExaminer($isVehicleExaminer)
    {
        $this->motAuthorizationService->expects($this->any())
            ->method('personHasRole')
            ->will($this->returnValue($isVehicleExaminer));
    }

    private function setIsGrantedAtSite($isGrantedAtSite)
    {
        $this->motAuthorizationService->expects($this->any())
            ->method('isGrantedAtSite')
            ->will($this->returnValue($isGrantedAtSite));
    }

    private function addAuthsForClasses(Person $nominatedTester, $classes)
    {
        foreach ($classes as $vehicleClassCode) {
            MotTestObjectsFactory::addPersonAuthorisationForClass(
                $nominatedTester,
                $vehicleClassCode,
                AuthorisationForTestingMotStatusCode::QUALIFIED
            );
        }
    }

    private function newAuthForTesting($code = '4')
    {
        return SiteTest::newAuthForTesting($code);
    }

    private function createMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn($name);

        return $status;
    }
}
