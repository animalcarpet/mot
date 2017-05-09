<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntitiesTest\Entity;

use DateInterval;
use DateTime;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommonTest\TestUtils\XMock;
use DvsaDocument\Entity\Document;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestCancelled;
use DvsaEntities\Entity\MotTestComplaintRef;
use DvsaEntities\Entity\MotTestReasonForCancel;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;

/**
 * Class MotTestTest.
 */
class MotTestTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $motTest = $this->createMotTest();

        $this->assertNull($motTest->getId(), '"id" should initially be null');
        $this->assertNull($motTest->getTester(), '"nominatedTester" should initially be null');
        $this->assertNull($motTest->getVehicle(), '"vehicle" should initially be null');
        $this->assertNull($motTest->getVehicleTestingStation(), '"vehicleTestingStation" should initially be null');
        $this->assertNull($motTest->getPrimaryColour(), '"primaryColour" should initially be null');
        $this->assertNull($motTest->getSecondaryColour(), '"secondaryColour" should initially be null');
        $this->assertNull($motTest->getHasRegistration(), '"hasRegistration" should initially be null');
        $this->assertEmpty($motTest->getBrakeTestResultClass3AndAbove(), '"brakeTestResult" should initially be empty');
        $this->assertNull($motTest->getIssuedDate(), '"issuedDate" should initially be null');
        $this->assertNull($motTest->getMotTestCancelled(), '"motTestCancelled" should initially be null');
        $this->assertNull(
            $motTest->getReasonForTerminationComment(),
            '"reasonForTerminationComment" should initially be null'
        );
        $this->assertNull($motTest->getExpiryDate(), '"expiryDate" should initially be null');
        $this->assertEquals(
            MotTestStatusName::ACTIVE, $motTest->getStatus(),
            '"status" should initially be active'
        );

        $this->assertEmpty(
            $motTest->getMotTestReasonForRejections()->toArray(),
            '"motTestReasonForRejections" should initially be empty'
        );
        $this->assertNull($motTest->getModel(), 'Model should initially be null');
    }

    public function testSettersAndGetters()
    {
        $number = '1234567';
        $brakeTests12 = new BrakeTestResultClass12();
        $brakeTests3up = new BrakeTestResultClass3AndAbove();
        $complaintRef = (new MotTestComplaintRef())->setComplaintRef('33333');
        $completedDate = new \DateTime();
        $countryOfReg = new CountryOfRegistration();
        $document = new Document();
        $expiryDate = new \DateTime();
        $fuelType = new FuelType();
        $fullPartialRetest = $this->createMotTest();
        $hasReg = true;
        $isPriv = true;
        $issuedDate = new \DateTime();
        $itemsNotTestedComment = new Comment();
        $make = new Make();
        $model = new Model();
        $model->setMake($make);
        $primaryColour = new Colour();
        $secondaryColour = new Colour();
        $odometerValue = 666;
        $prsMotTest = $this->createMotTest();
        $vin = '12345678901234567';
        $reg = 'FCB1234';
        $vts = new Site();
        $vehClass = new VehicleClass();

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model);
        $modelDetail->setVehicleClass($vehClass);
        $modelDetail->setFuelType($fuelType);

        $vehicle = new Vehicle();
        $vehicle->setCountryOfRegistration($countryOfReg);
        $vehicle->setVin($vin);
        $vehicle->setRegistration($reg);
        $vehicle->setColour($primaryColour);
        $vehicle->setSecondaryColour($secondaryColour);
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setVersion(1);

        $status = $this->createMotTestPassedStatus();
        $startedDate = new \DateTime();
        $tester = new Person();
        $motTestOriginal = $this->createMotTest();
        $reasonForTerminationComment = 'comment for termination';
        $reasonForCancel = new MotTestReasonForCancel();
        $comment = (new Comment())->setComment($reasonForTerminationComment);
        $motTestCancelled = new MotTestCancelled();
        $motTestCancelled->setComment($comment)
            ->setMotTestReasonForCancel($reasonForCancel);
        $partialReinspectionComment = new Comment();
        $onePersonTest = 1;
        $onePersonReinspection = 1;

        $mtt = new MotTestType();
        $mtt->setCode(MotTestTypeCode::NORMAL_TEST);

        $mt = $this->createMotTest()
            ->setNumber($number)
            ->setBrakeTestResultClass12($brakeTests12)
            ->setBrakeTestResultClass3AndAbove($brakeTests3up)
            ->setComplaintRef($complaintRef)
            ->setCompletedDate($completedDate)
            ->setDocument($document)
            ->setExpiryDate($expiryDate)
            ->setHasRegistration($hasReg)
            ->setIssuedDate($issuedDate)
            ->setOdometerValue($odometerValue)
            ->setOdometerUnit(OdometerUnit::KILOMETERS)
            ->setOdometerResultType(OdometerReadingResultType::NOT_READABLE)
            ->setPrsMotTest($prsMotTest)
            ->setVehicleTestingStation($vts)
            ->setVehicle($vehicle)
            ->setVehicleVersion($vehicle->getVersion())
            ->setStatus($status)
            ->setStartedDate($startedDate)
            ->setTester($tester)
            ->setMotTestIdOriginal($motTestOriginal)
            ->setMotTestCancelled($motTestCancelled)
            ->setMotTestType($mtt);

        $this->assertEquals($number, $mt->getNumber());
        $this->assertEquals($brakeTests12, $mt->getBrakeTestResultClass12());
        $this->assertEquals($brakeTests3up, $mt->getBrakeTestResultClass3AndAbove());
        $this->assertEquals($complaintRef, $mt->getComplaintRef());
        $this->assertEquals($completedDate, $mt->getCompletedDate());
        $this->assertEquals($countryOfReg, $mt->getCountryOfRegistration());
        $this->assertEquals($document, $mt->getDocument());
        $this->assertEquals($expiryDate, $mt->getExpiryDate());
        $this->assertEquals($fuelType, $mt->getFuelType());
        $this->assertEquals($hasReg, $mt->getHasRegistration());
        $this->assertEquals($issuedDate, $mt->getIssuedDate());
        $this->assertEquals($make, $mt->getMake());
        $this->assertEquals($model, $mt->getModel());
        $this->assertEquals($primaryColour, $mt->getPrimaryColour());
        $this->assertEquals($secondaryColour, $mt->getSecondaryColour());
        $this->assertEquals($odometerValue, $mt->getOdometerValue());
        $this->assertEquals(OdometerUnit::KILOMETERS, $mt->getOdometerUnit());
        $this->assertEquals(OdometerReadingResultType::NOT_READABLE, $mt->getOdometerResultType());
        $this->assertEquals($prsMotTest, $mt->getPrsMotTest());
        $this->assertEquals($vin, $mt->getVin());
        $this->assertEquals($reg, $mt->getRegistration());
        $this->assertEquals($vts, $mt->getVehicleTestingStation());
        $this->assertEquals($vehClass, $mt->getVehicleClass());
        $this->assertEquals($vehicle, $mt->getVehicle());
        $this->assertEquals($status->getName(), $mt->getStatus());
        $this->assertEquals($startedDate, $mt->getStartedDate());
        $this->assertEquals($tester, $mt->getTester());
        $this->assertEquals($motTestOriginal, $mt->getMotTestIdOriginal());
        $this->assertEquals($reasonForCancel, $mt->getMotTestCancelled()->getMotTestReasonForCancel());
        $this->assertEquals($reasonForTerminationComment, $mt->getReasonForTerminationComment());
        $this->assertEquals($mtt, $mt->getMotTestType());
    }

    public function testAddReasonsForRejection()
    {
        $mt = $this->createMotTest();
        $reason = new MotTestReasonForRejection();
        $mt->addMotTestReasonForRejection($reason);
        $this->assertEquals($reason, $mt->getMotTestReasonForRejections()[0]);
    }

    public function testAddBrakeTestClassHistory()
    {
        $mt = $this->createMotTest();
        $brakeTests12old = new BrakeTestResultClass12();
        $brakeTests12 = new BrakeTestResultClass12();
        $brakeTests3upOld = new BrakeTestResultClass3AndAbove();
        $brakeTests3up = new BrakeTestResultClass3AndAbove();
        $mt->setBrakeTestResultClass12($brakeTests12old);
        $mt->setBrakeTestResultClass12($brakeTests12);
        $mt->setBrakeTestResultClass3AndAbove($brakeTests3upOld);
        $mt->setBrakeTestResultClass3AndAbove($brakeTests3up);
        $this->assertEquals($brakeTests12, $mt->getBrakeTestResultClass12());
        $this->assertEquals($brakeTests3up, $mt->getBrakeTestResultClass3AndAbove());
        $this->assertEquals($mt->getBrakeTestCount(), 4);
    }

    public function testIsActive()
    {
        $mt = $this->createMotTest();
        $mt->setStatus($this->createMotTestActiveStatus());
        $this->assertTrue($mt->isActive());
    }

    public function testIsFailed()
    {
        $mt = $this->createMotTest();
        $mt->setStatus($this->createMotTestFailedStatus());
        $this->assertTrue($mt->isFailed());
    }

    public function testIsPassed()
    {
        $mt = $this->createMotTest();
        $mt->setStatus($this->createMotTestPassedStatus());
        $this->assertTrue($mt->isPassed());
    }

    public function testIsExpired()
    {
        $mt = $this->createMotTest();
        $mt->setExpiryDate((new DateTime('now'))->sub(new DateInterval('P1D')));
        $this->assertTrue($mt->isExpired());
    }

    public function testIsPassedOrFailed()
    {
        $mt = $this->createMotTest();
        $mt->setStatus($this->createMotTestPassedStatus());
        $this->assertTrue($mt->isPassedOrFailed());
    }

    public function testRemoveMotTestReasonForRejectionById()
    {
        $mt = $this->createMotTest();
        $reason1 = (new MotTestReasonForRejection())->setId(1);
        $reason2 = (new MotTestReasonForRejection())->setId(2);
        $reason3 = (new MotTestReasonForRejection())->setId(3);

        $mt->addMotTestReasonForRejection($reason1);
        $mt->addMotTestReasonForRejection($reason2);
        $mt->addMotTestReasonForRejection($reason3);

        $mt->removeMotTestReasonForRejectionById(2);

        $reasons = $mt->getMotTestReasonForRejections();
        $this->assertCount(2, $reasons);

        $reasonFilter = function ($id) use ($reasons) {
            return array_filter(
                $reasons->toArray(),
                function (MotTestReasonForRejection $e) use ($id) {
                    return $e->getId() === $id;
                }
            );
        };

        $this->assertCount(0, $reasonFilter(2));
        $this->assertCount(1, $reasonFilter(1));
        $this->assertCount(1, $reasonFilter(3));
    }

    public function testHasRfrsOfType()
    {
        $mt = $this->createMotTest();
        $reason1 = (new MotTestReasonForRejection())->setId(1)->setType(
            (new ReasonForRejectionType())->setReasonForRejectionType(ReasonForRejectionTypeName::FAIL)
        );
        $reason2 = (new MotTestReasonForRejection())->setId(2)->setType(
            (new ReasonForRejectionType())->setReasonForRejectionType(ReasonForRejectionTypeName::PRS)
        );

        $mt->addMotTestReasonForRejection($reason1);
        $mt->addMotTestReasonForRejection($reason2);

        $this->assertTrue($mt->hasRfrsOfType(ReasonForRejectionTypeName::PRS));
        $this->assertTrue($mt->hasRfrsOfType(ReasonForRejectionTypeName::FAIL));
        $this->assertFalse($mt->hasRfrsOfType('advisory'));
    }

    public function testExtractRfrs()
    {
        $hydrator = XMock::of(\DoctrineModule\Stdlib\Hydrator\DoctrineObject::class);
        $mtt = new MotTestType();
        $mtt->setCode(MotTestTypeCode::TARGETED_REINSPECTION);
        $mt = $this->createMotTest();
        $mt->setMotTestType($mtt);
        $reason1 = (new MotTestReasonForRejection())->setId(1)
            ->setType((new ReasonForRejectionType())->setReasonForRejectionType('fail'));
        $reason2 = (new MotTestReasonForRejection())->setId(2)
            ->setType((new ReasonForRejectionType())->setReasonForRejectionType('prs'));
        $mt->addMotTestReasonForRejection($reason1);
        $mt->addMotTestReasonForRejection($reason2);
        $this->assertCount(2, $mt->extractRfrs($hydrator));
        foreach ($mt->extractRfrs($hydrator) as $rfr) {
            $this->assertArrayHasKey('testType', $rfr);
            $this->assertEquals(MotTestTypeCode::TARGETED_REINSPECTION, $rfr['testType']);
        }
    }

    public function testHasBrakeTestResults()
    {
        $mt = $this->createMotTest();
        $mt->setBrakeTestResultClass12(new BrakeTestResultClass12());
        $this->assertTrue($mt->hasBrakeTestResults());
    }

    public function testGetBrakeTestGeneralPass_class12_generalPassTrue_shouldReturnTrue()
    {
        $brakeTestsClass12 = new BrakeTestResultClass12();
        $brakeTestsClass12->setGeneralPass(true);
        $mt = $this->createMotTest();
        $mt->setBrakeTestResultClass12($brakeTestsClass12);
        $this->assertTrue($mt->getBrakeTestGeneralPass());
    }

    public function testGetBrakeTestGeneralPass_class3up_generalPassTrue_shouldReturnTrue()
    {
        $brakeTestsClass3up = new BrakeTestResultClass3AndAbove();
        $brakeTestsClass3up->setGeneralPass(true);
        $mt = $this->createMotTest();
        $mt->setBrakeTestResultClass3AndAbove($brakeTestsClass3up);
        $this->assertTrue($mt->getBrakeTestGeneralPass());
    }

    public function testGetBrakeTestGeneralPass_noResultsSet_shouldReturnNull()
    {
        $brakeTestsClass3up = new BrakeTestResultClass3AndAbove();
        $brakeTestsClass3up->setGeneralPass(true);
        $mt = $this->createMotTest();
        $this->assertNull($mt->getBrakeTestGeneralPass());
    }

    public function testClone()
    {
        $mt = $this->createMotTest();
        $reason1 = (new MotTestReasonForRejection())->setId(1);
        $mt->addMotTestReasonForRejection($reason1);
        $brakeTests12 = new BrakeTestResultClass12();
        $brakeTests3up = new BrakeTestResultClass3AndAbove();
        $mt->setBrakeTestResultClass12($brakeTests12);
        $mt->setBrakeTestResultClass3AndAbove($brakeTests3up);

        $mtClone = clone $mt;

        $this->assertNotNull($mtClone->getMotTestReasonForRejections());
        $this->assertNotEmpty($mtClone->getBrakeTestResultClass12());
        $this->assertNotEmpty($mt->getBrakeTestResultClass3AndAbove());
    }

    private function createMotTest()
    {
        $motTest = new MotTest();
        $motTest->setStatus($this->createMotTestActiveStatus());

        return $motTest;
    }

    private function createMotTestActiveStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::ACTIVE);
    }

    private function createMotTestPassedStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::PASSED);
    }

    private function createMotTestFailedStatus()
    {
        return $this->createMotTestStatus(MotTestStatusName::FAILED);
    }

    private function createMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        return $status;
    }
}
