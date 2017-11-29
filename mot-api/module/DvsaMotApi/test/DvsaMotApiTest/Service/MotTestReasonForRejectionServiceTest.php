<?php

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\DataValidationException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommon\Constants\ReasonForRejection as ReasonForRejectionConstants;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestReasonForRejectionComment;
use DvsaEntities\Entity\MotTestReasonForRejectionDescription;
use DvsaEntities\Entity\MotTestReasonForRejectionLocation;
use DvsaEntities\Entity\MotTestReasonForRejectionMarkedAsRepaired;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Entity\RfrDeficiencyCategory;
use DvsaEntities\Repository\MotTestReasonForRejectionLocationRepository;
use DvsaEntities\Repository\MotTestReasonForRejectionRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\RfrRepository;
use DvsaMotApi\Service\Helper\BrakeTestResultsHelper;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\Validator\MotTestValidator;

use DvsaMotApiTest\Service\AbstractMotTestServiceTest;
use DvsaMotApiTest\Service\InMemoryRepositories\InMemoryReasonForRejectionTypeRepository;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class MotTestReasonForRejectionServiceTest extends AbstractMotTestServiceTest
{
    const MOT_TEST_NUMBER = '123456789012';

    /** @var MotTestValidator|MockObj */
    protected $motTestValidator;

    /** @var AuthorisationServiceInterface|MockObj */
    protected $authService;

    /** @var ApiPerformMotTestAssertion|MockObj */
    private $performMotTestAssertion;

    /** @var MotTestRepository|MockObj */
    private $motTestRepository;

    /** @var EntityManager|MockObj */
    private $entityManager;

    /** @var RfrRepository|MockObj */
    private $rfrRepository;

    /** @var MotTestReasonForRejectionRepository|MockObj */
    private $motTestReasonForRejectionRepository;

    /** @var MotTestReasonForRejectionLocationRepository|MockObj */
    private $motTestReasonForRejectionLocationRepository;

    /** @var InMemoryReasonForRejectionTypeRepository|MockObj */
    private $reasonForRejectionTypeRepository;

    /** @var BrakeTestResultsHelper|MockObj */
    private $brakeTestResultsHelper;

    /** @var MotTestReasonForRejectionService */
    private $motTestReasonForRejectionService;

    public function setUp()
    {
        $this->setupMocksForMotTestReasonForRejectionService();
        $this->setupTestReasonForRejectionTypeRepository(ReasonForRejectionTypeName::FAIL);
    }

    public function testWhenGetDefectCantFindRfr_ThenExceptionIsThrown()
    {
        $testDefectId = 1;

        $this->rfrRepository->expects($this->once())
            ->method('get')
            ->with($testDefectId)
            ->willReturn(null);

        $this->expectException(NotFoundException::class);

        $this->createService();
        $this->motTestReasonForRejectionService->getDefect($testDefectId);
    }

    public function testWhenGetADefectCalled_ThenExpectedReasonForRejectionIsReturned()
    {
        $testDefectId = 1;
        $expectedReasonForRejection = new ReasonForRejection();

        $this->rfrRepository->expects($this->once())
            ->method('get')
            ->with($testDefectId)
            ->willReturn($expectedReasonForRejection);

        $this->createService();
        $returnedReasonForRejection = $this->motTestReasonForRejectionService->getDefect($testDefectId);

        $this->assertEquals(
            $expectedReasonForRejection,
            $returnedReasonForRejection
        );
    }

    public function testWhenAddingAReasonForRejection_ButNotGrantedPerformMotTest_ThenExceptionThrown()
    {
        $this->performMotTestAssertion->expects($this->once())
            ->method('assertGranted')
            ->willThrowException(new \Exception());

        $this->expectException(Exception::class);
        $this->createService();

        $this->motTestReasonForRejectionService->addReasonForRejection(new MotTest(), []);
    }

    public function testWhenAddingAReasonForRejection_ButTestCantBeUpdated_ThenExceptionThrown()
    {
        $this->motTestValidator->expects($this->once())
            ->method('assertCanBeUpdated')
            ->willThrowException(new \Exception());

        $this->expectException(Exception::class);
        $this->createService();

        $this->motTestReasonForRejectionService->addReasonForRejection(new MotTest(), []);
    }

    public function authFailureDataProvider()
    {
        return [
            [PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED, ReasonForRejectionConstants::AUDIENCE_VEHICLE_EXAMINER_CODE],
            [PermissionInSystem::TESTER_RFR_ITEMS_NOT_TESTED, ReasonForRejectionConstants::AUDIENCE_TESTER_CODE],
        ];
    }

    /**
     * @dataProvider authFailureDataProvider
     *
     * @param string $authGrantedPermissionToFail
     * @param string $authGrantedPermissionToPass
     */
    public function testWhenThereAreUserPermissionRestrictionsOnRfr_ThenAnExceptionIsThrownWhenNotMet(
        $authGrantedPermissionToFail,
        $audience)
    {
        $testDefectId = 1;
        $reasonForRejection = new ReasonForRejection();
        $rfrDeficiencyCategory = new RfrDeficiencyCategory();
        $rfrDeficiencyCategory->setCode(RfrDeficiencyCategoryCode::DANGEROUS);
        $reasonForRejection->setRfrDeficiencyCategory($rfrDeficiencyCategory);
        $this->rfrRepository->expects($this->at(0))
            ->method('get')
            ->with($testDefectId)
            ->willReturn($reasonForRejection);

        $motTestReasonForRejection = new ReasonForRejection();
        $motTestReasonForRejection->setAudience($audience);
        $this->rfrRepository->expects($this->at(1))
            ->method('get')
            ->willReturn($motTestReasonForRejection);

        $this->authService->expects($this->once())
            ->method('assertGranted')
            ->with($authGrantedPermissionToFail)
            ->willThrowException(new \Exception());

        $this->expectException(Exception::class);

        $this->createService();
        $this->motTestReasonForRejectionService
            ->addReasonForRejection(
                $this->getNormalMotTest(),
                ['rfrId' => $testDefectId, 'type' => ReasonForRejectionTypeName::FAIL]
            );
    }

    public function testWhenBrakePerformanceNotTestedRfrIsChosenToBeAdded_ThenBrakeTestResultsAreCleared()
    {
        $testDefectId = 8566;
        $this->setUpRfrRepositoryMock($testDefectId, RfrDeficiencyCategoryCode::DANGEROUS);

        $motTest = $this->getTrainingMotTest(1);

        $this->brakeTestResultsHelper->expects($this->once())
            ->method('deleteAllBrakeTestResults')
            ->with($motTest);

        $firstGeneratedRfr = new ReasonForRejection();
        $secondGeneratedRfr = new ReasonForRejection();
        $generatedRfRs = array();
        $generatedRfRs[] = $firstGeneratedRfr;
        $generatedRfRs[] = $secondGeneratedRfr;

        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('findBy')
            ->with(['generated' => true, 'motTestId' => 1])
            ->willReturn($generatedRfRs);

        $this->entityManager->expects($this->at(0))
            ->method('remove')
            ->with($firstGeneratedRfr);

        $this->entityManager->expects($this->at(1))
            ->method('remove')
            ->with($secondGeneratedRfr);

        $this->createService();
        $this->motTestReasonForRejectionService
            ->addReasonForRejection(
                $motTest,
                ['rfrId' => $testDefectId, 'type' => ReasonForRejectionTypeName::FAIL]
            );
    }

    public function testWhenMotTestReasonForRejectionPassesValidation_ThenCommentAndDescriptionArePersisted()
    {
        $motTest = $this->getTrainingMotTest(1);

        $this->motTestValidator->expects($this->once())
            ->method('validateMotTestReasonForRejection')
            ->willReturn(true);

        $this->entityManager->method('persist')
            ->withConsecutive($this->isInstanceOf(MotTestReasonForRejection::class),
                $this->isInstanceOf(MotTestReasonForRejectionComment::class),
                $this->isInstanceOf(MotTestReasonForRejectionDescription::class));

        $this->createService();
        $rfr = $this->motTestReasonForRejectionService
            ->addReasonForRejection(
                $motTest,
                ['rfrId' => -1, 'type' => ReasonForRejectionTypeName::FAIL, 'comment' => 'some comment']
            );

        $this->assertEquals(ReasonForRejectionTypeName::FAIL, $rfr->getType()->getReasonForRejectionType());
    }

    public function deficiencyCategoriesToFailTypeDataProvider()
    {
        //RfrDeficiencyCategory  -  RfrTypeFromFrontend  -  RfrTypeMappedIntoMotTest
        return [
            [RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, ReasonForRejectionTypeName::FAIL, ReasonForRejectionTypeName::FAIL],
            [RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, ReasonForRejectionTypeName::ADVISORY, ReasonForRejectionTypeName::ADVISORY],
            [RfrDeficiencyCategoryCode::MINOR, ReasonForRejectionTypeName::FAIL, ReasonForRejectionTypeName::ADVISORY],
            [RfrDeficiencyCategoryCode::DANGEROUS, ReasonForRejectionTypeName::FAIL, ReasonForRejectionTypeName::FAIL],
            [RfrDeficiencyCategoryCode::MAJOR, ReasonForRejectionTypeName::FAIL, ReasonForRejectionTypeName::FAIL],
        ];
    }

    /**
     * @dataProvider deficiencyCategoriesToFailTypeDataProvider
     *
     * @param $deficiencyCategory
     * @param $typePassedFromFrontend
     * @param $expectedTypeAddedToMotTest
     */
    public function testWhenRfrIsAdded_ThenThisIsAddedToTheMotTestRfrAsCorrectlyMappedType(
        $deficiencyCategory,
        $typePassedFromFrontend,
        $expectedTypeAddedToMotTest)
    {
        $testDefectId = 1;
        $this->setupTestReasonForRejectionTypeRepository($expectedTypeAddedToMotTest);
        $this->setUpRfrRepositoryMock($testDefectId, $deficiencyCategory);

        $this->createService();
        $this->motTestReasonForRejectionService
            ->addReasonForRejection(
                $this->getTrainingMotTest(1),
                ['rfrId' => $testDefectId, 'type' => $typePassedFromFrontend]
            );

        $this->assertEquals(
            $expectedTypeAddedToMotTest,
            $this->reasonForRejectionTypeRepository->getLastTypeQueried()
        );
    }

    public function deficiencyCategoriesPassedAsFailTypeWhichMapToFailProvider()
    {
        return [
            [RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, false],
            [RfrDeficiencyCategoryCode::DANGEROUS, true],
            [RfrDeficiencyCategoryCode::MAJOR, false],
        ];
    }

    /**
     * @dataProvider deficiencyCategoriesPassedAsFailTypeWhichMapToFailProvider
     *
     * @param $deficiencyCategory
     * @param $expectedIsDangerous
     */
    public function testWhenDangerousNotSetFromFrontend_ButIsFailType_ThenItIsOnlySetInMotTestForDangerousRfrs(
        $deficiencyCategory,
        $expectedIsDangerous)
    {
        $testDefectId = 1;
        $this->setupTestReasonForRejectionTypeRepository(ReasonForRejectionTypeName::FAIL);
        $this->setUpRfrRepositoryMock($testDefectId, $deficiencyCategory);

        $this->createService();
        $rfr = $this->motTestReasonForRejectionService
            ->addReasonForRejection(
                $this->getTrainingMotTest(1),
                ['rfrId' => $testDefectId, 'type' => ReasonForRejectionTypeName::FAIL]
            );

        $this->assertEquals(
            ReasonForRejectionTypeName::FAIL,
            $this->reasonForRejectionTypeRepository->getLastTypeQueried()
        );

        $this->assertEquals($expectedIsDangerous, $rfr->getFailureDangerous());
    }

    public function testWhenAPostEuMajorRfrIsAddedAsDangerous_ThenExceptionThrown()
    {
        $testDefectId = 1;
        $this->setupTestReasonForRejectionTypeRepository(ReasonForRejectionTypeName::FAIL);
        $this->setUpRfrRepositoryMock($testDefectId, RfrDeficiencyCategoryCode::MAJOR);

        $this->expectException(DataValidationException::class);

        $this->createService();
        $this->motTestReasonForRejectionService
            ->addReasonForRejection(
                $this->getTrainingMotTest(1),
                [
                    'rfrId' => $testDefectId,
                    'failureDangerous' => true,
                    'type' => ReasonForRejectionTypeName::FAIL
                ]
            );
    }

    public function testWhenRemoveReasonForRejectionIsCalled_ThenItIsCorrectlyRemoved()
    {
        $rfrToDelete = new MotTestReasonForRejection();

        $this->entityManager->expects($this->once())
            ->method('remove')
            ->with($rfrToDelete);

        $this->createService();
        $this->motTestReasonForRejectionService->removeReasonForRejection($rfrToDelete);
    }

    public function testSuccessfullyPersistReasonForRejectionMarkedAsRepairedRecord()
    {
        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(MotTestReasonForRejectionMarkedAsRepaired::class));

        $this->createService();
        $this->motTestReasonForRejectionService
            ->createReasonForRejectionMarkedAsRepairedRecord(new MotTestReasonForRejection());
    }

    public function testUndoMarkReasonForRejectionAsRepairedUsingBrakeTestPerformanceNotTestedCallsClearBrakeTestResults()
    {
        $data = [
            'rfrId' => ReasonForRejectionConstants::CLASS_3457_BRAKE_PERFORMANCE_NOT_TESTED_RFR_ID,
            'type' => 'FAIL',
        ];
        $motTest = self::getTestMotTestEntity();
        $motTest->setNumber('123456');
        $reasonForRejection = (new ReasonForRejection())
            ->setRfrId($data['rfrId']);

        $mockMotTestRfR = XMock::of(MotTestReasonForRejection::class);
        $mockMotTestRfR->method('getMotTest')
            ->willReturn($motTest);
        $mockMotTestRfR->method('getCanBeDeleted')
            ->willReturn(true);
        $mockMotTestRfR->method('getReasonForRejection')
            ->willReturn($reasonForRejection);

        $this->motTestRepository->method('getMotTestByNumber')
            ->willReturn($motTest);

        $this->motTestReasonForRejectionRepository->method('find')
            ->willReturn($mockMotTestRfR);
        $this->motTestReasonForRejectionRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($emptyArray = []);

        $this->rfrRepository->expects($this->once())
            ->method('get')
            ->willReturn($reasonForRejection);

        $this->createService();
        $this->motTestReasonForRejectionService
            ->undoMarkReasonForRejectionAsRepaired((int) $motTest->getNumber(), $data['rfrId']);
    }

    public function testDeleteReasonForRejectionByIdThrowsNotFoundExceptionForInvalidMotTest()
    {
        $rfrId = 1;

        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $this->setExpectedException(
            NotFoundException::class,
            sprintf('Unable to fetch an MotTestReasonForRejection with ID "%s"', $rfrId)
        );

        $this->createService();
        $this->motTestReasonForRejectionService
            ->deleteReasonForRejectionById(self::MOT_TEST_NUMBER, $rfrId);
    }

    public function testDeleteReasonForRejectionByIdThrowsNotFoundExceptionForInvalidRfr()
    {
        $motTestRfrId = 666;

        $motTest = (new MotTest())->setMotTestType((new MotTestType())
            ->setCode(MotTestTypeCode::NORMAL_TEST));

        $motRfrAdvisory = new MotTestReasonForRejection();
        $type = new ReasonForRejectionType();
        $type->setReasonForRejectionType(ReasonForRejectionTypeName::ADVISORY);
        $motRfrAdvisory->setType($type);
        $motRfrAdvisory->setMotTest($motTest);

        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->willReturn($motRfrAdvisory);

        $this->setExpectedException(
            NotFoundException::class,
            'Match for Reason for Rejection on Selected Mot Test not found'
        );

        $this->createService();
        $this->motTestReasonForRejectionService
            ->deleteReasonForRejectionById(self::MOT_TEST_NUMBER, $motTestRfrId);
    }

    public function testDeleteReasonForRejectionByIdThrowsBadRequestExceptionForInvalidRfr()
    {
        $motTestRfrId = 1;

        $motTest = self::getTestMotTestEntity();
        XMock::mockClassField($motTest, 'number', self::MOT_TEST_NUMBER);

        $motRfrAdvisory = new MotTestReasonForRejection();
        $type = new ReasonForRejectionType();
        $type->setReasonForRejectionType(ReasonForRejectionTypeName::ADVISORY);
        $motRfrAdvisory->setType($type);
        $motRfrAdvisory->setMotTest($motTest);
        $motRfrAdvisory->setGenerated(true);

        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->willReturn($motRfrAdvisory);

        $this->setExpectedException(
            BadRequestException::class,
            'This Reason for Rejection type cannot be removed or repaired'
        );

        $this->createService();
        $this->motTestReasonForRejectionService
            ->deleteReasonForRejectionById(self::MOT_TEST_NUMBER, $motTestRfrId);
    }

    public function testDeleteReasonForRejectionByIdOk()
    {
        $motTestNumber = 2001;
        $rfrId = 754;

        $motTest = self::getTestMotTestEntity();
        XMock::mockClassField($motTest, 'number', (string) $motTestNumber);

        $motRfrFail = new MotTestReasonForRejection();
        $type = new ReasonForRejectionType();
        $type->setReasonForRejectionType(ReasonForRejectionTypeName::FAIL);
        $motRfrFail->setType($type);
        $motRfrFail->setMotTest($motTest);

        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->will($this->returnValue($motRfrFail));

        $this->entityManager->expects($this->at(0))
            ->method('remove')
            ->will($this->returnValue(null));

        $this->entityManager->expects($this->at(1))
            ->method('flush');

        $this->createService();
        $this->motTestReasonForRejectionService->deleteReasonForRejectionById($motTestNumber, $rfrId);
    }

    public function testMarkReasonForRejectionAsRepaired()
    {
        $data = [
            'rfrId' => ReasonForRejectionConstants::CLASS_3457_BRAKE_PERFORMANCE_NOT_TESTED_RFR_ID,
            'type' => 'FAIL',
        ];
        $motTest = self::getTestMotTestEntity();
        $motTest->setNumber('123456');
        $reasonForRejection = (new ReasonForRejection())
            ->setRfrId($data['rfrId']);

        $mockMotTestRfR = XMock::of(MotTestReasonForRejection::class);
        $mockMotTestRfR->method('getMotTest')
            ->willReturn($motTest);
        $mockMotTestRfR->method('getCanBeDeleted')
            ->willReturn(true);
        $mockMotTestRfR->method('getReasonForRejection')
            ->willReturn($reasonForRejection);

        $this->motTestRepository->method('getMotTestByNumber')
            ->willReturn($motTest);

        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->willReturn($mockMotTestRfR);

        $this->rfrRepository->expects($this->once())
            ->method('get')
            ->willReturn($reasonForRejection);

        $this->createService();
        $this->motTestReasonForRejectionService
            ->markReasonForRejectionAsRepaired((int) $motTest->getNumber(), $data['rfrId']);
    }

    public function testWhenEditRfRInvoked_AndCorrectPermissionsAreMet_TheSuccessfulEditOfReasonForRejection()
    {
        $testDefectId = 100;
        $motTestRfrId = 1;

        $motTestReasonForRejection = new MotTestReasonForRejection();
        $motTest = $this->getNormalMotTest();
        $motTestReasonForRejection->setMotTest($motTest);
        $type = new ReasonForRejectionType();
        $type->setReasonForRejectionType(ReasonForRejectionTypeName::FAIL);
        $motTestReasonForRejection->setType($type);
        $motTestReasonForRejection->setFailureDangerous(true);
        $motTestReasonForRejection->setMotTestReasonForRejectionComment(new MotTestReasonForRejectionComment());

        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->with($motTestRfrId)
            ->willReturn($motTestReasonForRejection);

        $this->motTestValidator->expects($this->once())
            ->method('assertCanBeUpdated')
            ->with($motTest);

        $motTestReasonForRejectionLocation = new MotTestReasonForRejectionLocation();
        $this->motTestReasonForRejectionLocationRepository->expects($this->once())
            ->method('getLocation')
            ->willReturn($motTestReasonForRejectionLocation);

        $this->motTestValidator->expects($this->once())
            ->method('validateMotTestReasonForRejection')
            ->with($motTestReasonForRejection)
            ->willReturn(true);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($motTestReasonForRejection);
        
        $this->createService();
        $this->motTestReasonForRejectionService
            ->editReasonForRejection(
                $motTestRfrId,
                ['rfrId' => $testDefectId, 'type' => ReasonForRejectionTypeName::FAIL]
            );
    }

    public function testWhenNotPermittedToPerformDemoTest_TehnEditTestThrowsException()
    {
        $motTestRfrId = 1;

        $motTestReasonForRejection = new MotTestReasonForRejection();
        $motTest = $this->getTrainingMotTest($motTestRfrId);
        $motTestReasonForRejection->setMotTest($motTest);
        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->with($motTestRfrId)
            ->willReturn($motTestReasonForRejection);

        $this->authService->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_DEMO_TEST_PERFORM)
            ->willThrowException(new \Exception());

        $this->expectException(Exception::class);

        $this->createService();
        $this->motTestReasonForRejectionService
            ->editReasonForRejection(
                $motTestRfrId,
                ['rfrId' => $motTestRfrId, 'type' => ReasonForRejectionTypeName::FAIL]
            );
    }

    public function testWhenNotPermittedToPerformNormalTest_ThenEditTestThrowsException()
    {
        $motTestRfrId = 1;

        $motTestReasonForRejection = new MotTestReasonForRejection();
        $motTest = $this->getNormalMotTest();
        $motTestReasonForRejection->setMotTest($motTest);
        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->with($motTestRfrId)
            ->willReturn($motTestReasonForRejection);

        $this->authService->expects($this->once())
            ->method('assertGranted')
            ->with(PermissionInSystem::MOT_TEST_PERFORM)
            ->willThrowException(new \Exception());

        $this->expectException(Exception::class);

        $this->createService();
        $this->motTestReasonForRejectionService
            ->editReasonForRejection(
                $motTestRfrId,
                ['rfrId' => $motTestRfrId, 'type' => ReasonForRejectionTypeName::FAIL]
            );
    }

    public function testWhenEditRfRInvoked_AndCorrectPermissionsAreMet_ButValidationFails_ThenNoSave()
    {
        $testDefectId = 100;
        $motTestRfrId = 1;

        $motTestReasonForRejection = new MotTestReasonForRejection();
        $motTest = $this->getNormalMotTest();
        $motTestReasonForRejection->setMotTest($motTest);
        $type = new ReasonForRejectionType();
        $type->setReasonForRejectionType(ReasonForRejectionTypeName::FAIL);
        $motTestReasonForRejection->setType($type);
        $motTestReasonForRejection->setFailureDangerous(true);
        $motTestReasonForRejection->setMotTestReasonForRejectionComment(new MotTestReasonForRejectionComment());

        $this->motTestReasonForRejectionRepository->expects($this->once())
            ->method('find')
            ->with($motTestRfrId)
            ->willReturn($motTestReasonForRejection);

        $this->motTestValidator->expects($this->once())
            ->method('assertCanBeUpdated')
            ->with($motTest);

        $motTestReasonForRejectionLocation = new MotTestReasonForRejectionLocation();
        $this->motTestReasonForRejectionLocationRepository->expects($this->once())
            ->method('getLocation')
            ->willReturn($motTestReasonForRejectionLocation);

        $this->motTestValidator->expects($this->once())
            ->method('validateMotTestReasonForRejection')
            ->with($motTestReasonForRejection)
            ->willReturn(false);

        $this->motTestReasonForRejectionRepository->expects($this->never())
            ->method('save');

        $this->createService();
        $this->motTestReasonForRejectionService
            ->editReasonForRejection(
                $motTestRfrId,
                ['rfrId' => $testDefectId, 'type' => ReasonForRejectionTypeName::FAIL]
            );
    }

    private function setUpRfrRepositoryMock($testDefectId, $rfrDeficiencyCategoryCode) {
        $reasonForRejection = new ReasonForRejection();
        $rfrDeficiencyCategory = new RfrDeficiencyCategory();
        $rfrDeficiencyCategory->setCode($rfrDeficiencyCategoryCode);
        $reasonForRejection->setRfrDeficiencyCategory($rfrDeficiencyCategory);
        $reasonForRejection->setRfrId($testDefectId);
        $this->rfrRepository->expects($this->once())
            ->method('get')
            ->with($testDefectId)
            ->willReturn($reasonForRejection);
    }

    private function setupTestReasonForRejectionTypeRepository($reasonForRejectionTypeName)
    {
        $reasonForRejectionType = new ReasonForRejectionType();
        $reasonForRejectionType->setReasonForRejectionType($reasonForRejectionTypeName);

        $this->reasonForRejectionTypeRepository
            = new InMemoryReasonForRejectionTypeRepository([
            $reasonForRejectionTypeName => $reasonForRejectionType,
        ]);
    }

    private function getTrainingMotTest(int $id)
    {
        $motTest = new MotTest();
        $motTestType = new MotTestType();
        $motTestType->setCode(MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);
        $motTest->setMotTestType($motTestType);
        $motTest->setId($id);
        return $motTest;
    }

    private function getNormalMotTest()
    {
        $motTest = new MotTest();
        $motTestType = new MotTestType();
        $motTestType->setCode(MotTestTypeCode::NORMAL_TEST);
        $motTest->setMotTestType($motTestType);
        return $motTest;
    }

    private function setupMocksForMotTestReasonForRejectionService()
    {
        $this->motTestValidator = $this->getMockRepository(MotTestValidator::class);
        $this->authService = $this->getMockRepository(AuthorisationServiceInterface::class);
        $this->performMotTestAssertion = $this->getMockRepository(ApiPerformMotTestAssertion::class);
        $this->motTestRepository = $this->getMockRepository(MotTestRepository::class);
        $this->entityManager = $this->getMockRepository(EntityManager::class);
        $this->rfrRepository = $this->getMockRepository(RfrRepository::class);
        $this->motTestReasonForRejectionRepository
            = $this->getMockRepository(MotTestReasonForRejectionRepository::class);
        $this->motTestReasonForRejectionLocationRepository
            = $this->getMockRepository(MotTestReasonForRejectionLocationRepository::class);
        $this->brakeTestResultsHelper = $this->getMockRepository(BrakeTestResultsHelper::class);
    }

    private function createService()
    {
        $this->motTestReasonForRejectionService = new MotTestReasonForRejectionService(
            $this->entityManager,
            $this->authService,
            $this->motTestValidator,
            $this->performMotTestAssertion,
            $this->motTestRepository,
            $this->rfrRepository,
            $this->motTestReasonForRejectionRepository,
            $this->motTestReasonForRejectionLocationRepository,
            $this->reasonForRejectionTypeRepository,
            $this->brakeTestResultsHelper
        );
    }

}