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
use DvsaCommon\Enum\VehicleClassCode;
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
use DvsaMotApi\Service\Validator\MotTestValidator;
use DvsaMotApiTest\Factory\MotTestObjectsFactory;
use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use UserFacade\UserFacadeLocal;

/**
 * Class MotTestValidatorTest
 */
class MotTestValidatorTest extends PHPUnit_Framework_TestCase
{
    /** @var MotTestValidator $motTestValidator */
    private $motTestValidator;
    private $censorServiceMock;

    /** @var  AuthorisationServiceInterface $motAuthorizationService */
    private $motAuthorizationService;
    private $userFacadeMock;

    /** @var  MotIdentityProviderInterface $motIdentityProvider */
    private $motIdentityProvider;

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

        $this->userFacadeMock = XMock::of(UserFacadeLocal::class);

        $this->motTestValidator = new MotTestValidator(
            $this->censorServiceMock,
            $this->motAuthorizationService,
            $this->motIdentityProvider
        );
        parent::setUp();
    }

    public function testValidateNewMotTest()
    {
        $vtsRole = $this->newAuthForTesting();

        $vehicleTestingStation = $this->getTestVts($vtsRole);

        $nominatedTester = new Person();
        $nominatedTester->addVehicleTestingStation($vehicleTestingStation);
        $this->addAuthsForClasses($nominatedTester, ['4']);

        $vehicle = new Vehicle();
        $vehicle->setVehicleClass(new VehicleClass(VehicleClassCode::CLASS_4));

        $motTest = $this->setupMotTest($nominatedTester, $vehicle, $vehicleTestingStation);
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Nominated Tester not found
     */
    public function testValidateNewMotTestThrowsNotFoundErrorForNullNominatedTester()
    {
        $this->markTestSkipped("SDM User -> Person + Contact details");
        $motTest = $this->setupMotTest(null, new Vehicle(), new Site());
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Vehicle not found
     */
    public function testValidateNewMotTestThrowsNotFoundErrorForNullVehicle()
    {
        $this->markTestSkipped("SDM User -> Person + Contact details");
        $motTest = $this->setupMotTest(new Person(), null, new Site());
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage Vehicle Testing Station not found
     */
    public function testValidateNewMotTestThrowsNotFoundErrorForNullVts()
    {
        $this->markTestSkipped("SDM User -> Person + Contact details");
        $motTest = $this->setupMotTest(new Person(), new Vehicle(), null);
        $this->motTestValidator->validateNewMotTest($motTest);
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

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\RequiredFieldException
     * @expectedExceptionMessage A required field is missing
     */
    public function testValidateNewMotTestThrowsRequiredFieldExceptionForNullHasRegistration()
    {
        $this->markTestSkipped('RBAC issue');
        $motTest = $this->setupMotTest(new Person(), new Vehicle(), new Site(), 1, 'Blue', null);
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\ForbiddenException
     * @expectedExceptionMessage You are not authorised to test a class 4 vehicle
     */
    public function testValidateNewMotTestThrowsForbiddenExceptionForNominatedTesterWithoutVehicleClassRole()
    {
        $vehicleTestingStation = $this->getTestVts();

        $nominatedTester = new Person();
        $nominatedTester->addVehicleTestingStation($vehicleTestingStation);
        $this->addAuthsForClasses($nominatedTester, ['1']);

        $vehicle = new Vehicle();
        $vehicle->setVehicleClass(new VehicleClass(VehicleClassCode::CLASS_4));

        $motTest = $this->setupMotTest($nominatedTester, $vehicle, $vehicleTestingStation);
        $this->motTestValidator->validateNewMotTest($motTest);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\ForbiddenException
     * @expectedExceptionMessage Your Site is not authorised to test class 4 vehicles
     */
    public function testValidateNewMotTestThrowsForbiddenExceptionForVtsWithoutVehicleClassRole()
    {
        $vehicleTestingStation = $this->getTestVts($this->newAuthForTesting('1'));

        $nominatedTester = new Person();
        $nominatedTester->addVehicleTestingStation($vehicleTestingStation);
        $this->addAuthsForClasses($nominatedTester, ['4']);

        $vehicle = new Vehicle();
        $vehicle->setVehicleClass(new VehicleClass(VehicleClassCode::CLASS_4));

        $motTest = $this->setupMotTest($nominatedTester, $vehicle, $vehicleTestingStation);
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

    /**
     * Test is not slots and user is attempting an MOT test that does not consume slots, then exception not thrown
     */
    public function testHasNotSlotsTestDoesNotConsumeSlots()
    {
        $vtsRole = $this->newAuthForTesting();

        $vehicleTestingStation = $this->getTestVts($vtsRole, 1, 0);

        $motTest = $this->setupMotTest(new Person(), new Vehicle(), $vehicleTestingStation, false);

        XMock::invokeMethod($this->motTestValidator, 'checkMotTestTesterHasSlotsToPerformMotTest'. [$motTest]);
    }

    /**
     * Tests if has slots and and user is attempting an MOT test that does consume slots, then exception not thrown
     */
    public function testHasSlotsTestDoesNotConsumeSlots()
    {
        $vehicleTestingStation = $this->getTestVts();

        $motTest = $this->setupMotTest(new Person, new Vehicle(), $vehicleTestingStation, true);

        XMock::invokeMethod($this->motTestValidator, 'checkMotTestTesterHasSlotsToPerformMotTest'. [$motTest]);
    }

    public function testNewMotTestNotThrowsBadRequestExceptionNotLinkedToVtsForVehicleExaminer()
    {
        $this->setIsVehicleExaminer(true);

        $site = new Site();
        $site->setId(1);

        $motTest = $this->setupMotTest(new Person(), new Vehicle(), $site, false);

        $this->motTestValidator->validateNewMotTest($motTest);
    }

    public function testNewMotTestNotThrowsForbiddenExceptionWithoutVehicleClassRoleForVehicleExaminer()
    {
        $site = $this->getTestVts();

        $vehicle = new Vehicle();
        $vehicle->setVehicleClass(new VehicleClass(VehicleClassCode::CLASS_4));

        $motTest = $this->setupMotTest(new Person(), $vehicle, $site, false);

        $this->setIsVehicleExaminer(true);

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

    protected function getTestVts(AuthorisationForTestingMotAtSite $role = null, $id = 1, $slots = 10)
    {
        $this->markTestSkipped();
        if (!$role) {
            $role = $this->newAuthForTesting();
        }
        return (new Site())
            ->setId($id)
            ->addAuthorisationsForTestingMotAtSite($role)
            ->setOrganisation(
                (new Organisation())->setSlotBalance($slots)
            );
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
