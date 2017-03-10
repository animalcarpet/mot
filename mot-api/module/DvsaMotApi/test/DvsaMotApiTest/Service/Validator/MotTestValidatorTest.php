<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Service\Validator;

use CensorApi\Service\CensorService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Constants\ReasonForRejection as ReasonForRejectionConstants;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestReasonForRejectionDescription;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
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
        $motTest = $this->setupMotTest(new Person(), new Site(), true, null, null);
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testValidateNewMotTestThrowsRequiredFieldExceptionForNullSecondaryColour()
    {
        $motTest = $this->setupMotTest(new Person(), new Site(), true, true, 'B', null);
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    public function testValidateMotTestReasonForRejectionPassesWithValidRfr()
    {
        $motTest = new MotTest();

        $vehicle = $this->getVehicle();

        $motTest->setVehicle($vehicle);
        $motTest->setVehicleVersion($vehicle->getVersion());


        $rfr = (new MotTestReasonForRejection())
            ->setCustomDescription(new MotTestReasonForRejectionDescription())
            ->setReasonForRejection(
                (new ReasonForRejection())
                    ->addVehicleClass(
                        (new VehicleClass(VehicleClassCode::CLASS_4))
                    )
            )->setMotTest($motTest);
        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    public function testValidateMotTestReasonForRejectionPassesWithCustomRfr()
    {
        $description = new MotTestReasonForRejectionDescription();
        $description->setCustomDescription('Manual advisory description');

        $rfr = (new MotTestReasonForRejection())
            ->setCustomDescription($description);

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage You must give a description
     */
    public function testValidateMotTestReasonForRejectionThrowsExceptionWhenCustomRfrHasNoDescription()
    {
        $rfr = new MotTestReasonForRejection();

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage must be 250 characters or shorter
     */
    public function testValidateMotTestReasonForRejectionThrowsExceptionWhenCommentIsToLong()
    {
        $rfr = new MotTestReasonForRejection();
        $rfr->setReasonForRejection(new ReasonForRejection());
        $rfr->setComment(str_repeat("X", ReasonForRejectionConstants::MAX_DESCRIPTION_LENGTH + 1));
        $rfr->setCustomDescription(new MotTestReasonForRejectionDescription());

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage must be 250 characters or shorter
     */
    public function testValidateMotTestReasonForRejectionThrowsExceptionWhenCustomDescriptionIsToLong()
    {
        $rfr = new MotTestReasonForRejection();
        $rfr->setReasonForRejection(new ReasonForRejection());

        $description = new MotTestReasonForRejectionDescription();
        $description->setCustomDescription(str_repeat("X", ReasonForRejectionConstants::MAX_DESCRIPTION_LENGTH + 1));

        $rfr->setCustomDescription($description);

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Additional information – must not include any swearwords
     */
    public function testValidateMotTestReasonForRejectionProfanityDetected()
    {
        $textUnderProfanityTest = "badword";

        $description = new MotTestReasonForRejectionDescription();
        $description->setCustomDescription($textUnderProfanityTest);

        $rfr = new MotTestReasonForRejection();
        $rfr->setCustomDescription($description);
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
            ->setCustomDescription(new MotTestReasonForRejectionDescription())
            ->setReasonForRejection(new ReasonForRejection())
            ->setOnOriginalTest(true);

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    public function test_validateMotTestReasonForRejection_passes_with_null_end_date()
    {
        $vehicle = $this->getVehicle();

        $rfr = (new MotTestReasonForRejection())
            ->setCustomDescription(new MotTestReasonForRejectionDescription())
            ->setReasonForRejection(
                (new ReasonForRejection())
                    ->setEndDate(null)
                    ->addVehicleClass((new VehicleClass(VehicleClassCode::CLASS_4)))
            )
            ->setMotTest(
                (new MotTest())->setVehicle($vehicle)->setVehicleVersion($vehicle->getVersion())
            );

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    public function test_validateMotTestReasonForRejection_passes_with_end_date_in_future()
    {
        $vehicle = $this->getVehicle();

        $rfr = (new MotTestReasonForRejection())
            ->setCustomDescription(new MotTestReasonForRejectionDescription())
            ->setReasonForRejection(
                (new ReasonForRejection())
                    ->setEndDate(DateUtils::today()->modify('+1 day'))
                    ->addVehicleClass((new VehicleClass(VehicleClassCode::CLASS_4)))
            )
            ->setMotTest(
                (new MotTest())->setVehicle($vehicle)->setVehicleVersion($vehicle->getVersion())
            );

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage End-dated RFR can not be added
     */
    public function test_validateMotTestReasonForRejection_throws_an_exception_for_an_end_dated_Rfr()
    {
        $rfr = (new MotTestReasonForRejection())
            ->setCustomDescription(new MotTestReasonForRejectionDescription())
            ->setReasonForRejection((new ReasonForRejection())->setEndDate(DateUtils::toDate('2000-06-15')));

        $this->motTestValidator->validateMotTestReasonForRejection($rfr);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage This RFR cannot be added to a vehicle of this class
     */
    public function test_validateMotTestReasonForRejection_throws_an_exception_for_adding_wrong_class_rfr_to_vehicle()
    {
        $vehicle = $this->getVehicle();
        
        $rfr = (new MotTestReasonForRejection())
            ->setCustomDescription(new MotTestReasonForRejectionDescription())
            ->setReasonForRejection(
                (new ReasonForRejection())
                    ->addVehicleClass(
                        (new VehicleClass(VehicleClassCode::CLASS_1))
                    )
            )->setMotTest(
                (new MotTest())->setVehicle($vehicle)->setVehicleVersion($vehicle->getVersion())
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

        $motTest = $this->setupMotTest(new Person(), $site, false);

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

        $motTest = $this->setupMotTest(new Person(), new Site());
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateNewMotTestThrowsBadRequestIfNoVehicleTestingStation()
    {
        $motTest = $this->setupMotTest(new Person(), null, null, false);

        $this->motTestValidator->validateNewMotTest($motTest);
    }

    public function testValidateNewMotTestDoesNotThrowBadRequestIfNoVehicleTestingStationButNonMotTest()
    {
        $this->setIsVehicleExaminer(true);

        $nonMotTestType = (new MotTestType())
            ->setCode(MotTestTypeCode::NON_MOT_TEST)
            ->setIsSlotConsuming(false);

        $motTest = $this->setupMotTest(new Person(), new Site(), null, false);
        $motTest->setMotTestType($nonMotTestType);

        $this->motTestValidator->validateNewMotTest($motTest);
    }

    protected function setupMotTest(
        Person $nominatedTester,
        $vehicleTestingStation,
        $isSlotConsuming = true,
        $hasRegistration = 1,
        $primaryColourCode = 'B',
        $secondaryColourCode = 'X',
        $fuelTypeCode = 'P',
        $vehicleClass = VehicleClassCode::CLASS_1
    ) {
        $modelDetail = new ModelDetail();
        $modelDetail->setFuelType((new FuelType())->setCode($fuelTypeCode));
        $modelDetail->setVehicleClass((new VehicleClass())->setCode($vehicleClass));

        $vehicle = new Vehicle();
        $vehicle->setVersion(1);
        $vehicle->setModelDetail($modelDetail);

        if ($primaryColourCode) {
            $vehicle->setColour((new Colour())->setCode($primaryColourCode));
        }

        if ($secondaryColourCode) {
            $vehicle->setSecondaryColour((new Colour())->setCode($secondaryColourCode));
        }

        $motTest = new MotTest();
        $motTest->setTester($nominatedTester)
            ->setMotTestType((new MotTestType())->setCode('UT-CODE')->setIsSlotConsuming($isSlotConsuming))
            ->setVehicle($vehicle)
            ->setVehicleVersion($vehicle->getVersion())
            ->setVehicleTestingStation($vehicleTestingStation)
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

    private function createMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn($name);

        return $status;
    }

    /**
     * @param string $class
     * @return Vehicle
     */
    private function getVehicle($class = VehicleClassCode::CLASS_4){
        $vehicleClass = new VehicleClass($class);
        $modelDetail = new ModelDetail();
        $vehicle = new Vehicle();
        $vehicle->setVersion(1);

        $modelDetail->setVehicleClass($vehicleClass);
        $vehicle->setModelDetail($modelDetail);

        return $vehicle;
    }
}
