<?php

namespace DvsaMotApi\Service;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\EnforcementDecision;
use DvsaEntities\Entity\EnforcementDecisionCategory;
use DvsaEntities\Entity\EnforcementDecisionOutcome;
use DvsaEntities\Entity\EnforcementDecisionScore;
use DvsaEntities\Entity\EnforcementMotTestResult;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApiTest\Service\AbstractMotTestServiceTest;
use SebastianBergmann\Exporter\Exception;

/**
 * Class EnforcementMotTestResultServiceTest.
 */
class EnforcementMotTestResultServiceTest extends AbstractMotTestServiceTest
{
    const TEST_NUMBER = '1';
    const VALID_USER = 'valid-user';
    const INVALID_USER = 'invalid-user';

    protected function getTestCreateData()
    {
        $data = [
            'mappedRfrs' => [
                2055 => [
                    'score' => 1,
                    'decision' => 1,
                    'category' => 1,
                    'justification' => 'test',
                ],
                2056 => [
                    'score' => 2,
                    'decision' => 2,
                    'category' => 2,
                    'justification' => 'test 2',
                ],
            ],
            'caseOutcome' => 1,
            'finalJustification' => 'Final Justification Comment',
            'reinspectionMotTestNumber' => 42,
        ];

        return $data;
    }

    protected function getExpectedData()
    {
        $expectedData = [
            'decisions' => [
                new EnforcementDecision(),
                new EnforcementDecision(),
                new EnforcementDecision(),
            ],
            'categories' => [
                new EnforcementDecisionCategory(),
                new EnforcementDecisionCategory(),
                new EnforcementDecisionCategory(),
                new EnforcementDecisionCategory(),
            ],
            'outcomes' => [
                new EnforcementDecisionOutcome(),
                new EnforcementDecisionOutcome(),
                new EnforcementDecisionOutcome(),
                new EnforcementDecisionOutcome(),
            ],
            'scores' => [
                new EnforcementDecisionScore(),
                new EnforcementDecisionScore(),
                new EnforcementDecisionScore(),
                new EnforcementDecisionScore(),
            ],
            'mappedRfrs' => [
                new MotTestReasonForRejection(),
                new MotTestReasonForRejection(),
            ],
        ];
        $motTestType = (new MotTestType())->setCode(MotTestTypeCode::NORMAL_TEST);
        $expectedData['decisions'][0]->setId(1)->setDecision('Not applicable')->setPosition(1);
        $expectedData['decisions'][1]->setId(2)->setDecision('Defect missed')->setPosition(2);
        $expectedData['decisions'][2]->setId(3)->setDecision('Incorrect decision')->setPosition(3);
        $expectedData['categories'][0]->setId(1)->setCategory('Not applicable')->setPosition(1);
        $expectedData['categories'][1]->setId(2)->setCategory('Immediate')->setPosition(2);
        $expectedData['mappedRfrs'][0]->setId(2055)->setReasonForRejection((new ReasonForRejection())->setRfrId(42));
        $expectedData['mappedRfrs'][1]->setId(2056)->setReasonForRejection((new ReasonForRejection())->setRfrId(42));
        $expectedData['scores'][0]->setId(1);
        $expectedData['scores'][1]->setId(2);
        $expectedData['mappedRfrs'][0]->setMotTest(new MotTest());
        $tt = new MotTestType();
        $tt->setCode($motTestType);
        $expectedData['mappedRfrs'][0]->getMotTest()->setMotTestType($tt);
        $tt2 = new MotTestType();
        $tt2->setCode($motTestType);
        $expectedData['mappedRfrs'][1]->setMotTest(new MotTest());
        $expectedData['mappedRfrs'][1]->getMotTest()->setMotTestType($tt2);

        return $expectedData;
    }

    /**
     * Check that assertGrant is called.
     *
     * @expectedException \Exception
     */
    public function testCreateEnforcementMotTestResultThrowsException()
    {
        $data = $this->getTestCreateData();
        $mockHydrator = $this->getMockHydrator();
        $mockEntityManager = $this->getMockEntityManager();
        $mockAuthService = $this->getMockAuthorizationService();
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $mockAuthService->expects($this->once())
            ->method('assertGranted')
            ->will($this->throwException(new \Exception('Auth not granted')));

        $service = new EnforcementMotTestResultService(
            $mockEntityManager,
            $mockHydrator,
            $mockAuthService,
            $mockMotTestMapper
        );

        $service->createEnforcementMotTestResult($data, self::INVALID_USER);
    }

    /**
     * Check that assertGrant is called.
     *
     * @expectedException \Exception
     */
    public function testGetEnforcementMotTestResultThrowsException()
    {
        $mockEntityManager = $this->getMockEntityManager();
        $mockHydrator = $this->getMockHydrator();
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $mockAuthService = $this->getMockAuthorizationService();
        $mockAuthService->expects($this->once())
            ->method('assertGranted')
            ->will($this->throwException(new \Exception('Auth not granted')));

        $service = new EnforcementMotTestResultService(
            $mockEntityManager,
            $mockHydrator,
            $mockAuthService,
            $mockMotTestMapper
        );

        $service->getEnforcementMotTestResultData(self::TEST_NUMBER);
    }

    /**
     * Test we throw an exception when the GET result is not found.
     *
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetEnforcementMotTestResultDataThrowsNotFoundException()
    {
        $mockHydrator = $this->getMockHydrator();
        $mockEntityManager = $this->getMockEntityManager();
        $mockRepository = $this->getMockRepository();
        $mockAuthService = $this->getMockAuthorizationService();
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $expectedResult = null;

        $mockRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => self::TEST_NUMBER])
            ->will($this->returnValue($expectedResult));

        $mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementMotTestResult::class)
            ->will($this->returnValue($mockRepository));

        $service = new EnforcementMotTestResultService(
            $mockEntityManager,
            $mockHydrator,
            $mockAuthService,
            $mockMotTestMapper
        );

        $service->getEnforcementMotTestResultData(self::TEST_NUMBER);
    }

    public function testGetEnforcementMotTestResultData()
    {
        $mockHydrator = $this->getMockHydrator();
        $mockEntityManager = $this->getMockEntityManager();
        $mockRepository = $this->getMockRepository();
        $mockAuthService = $this->getMockAuthorizationService();
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $expectedResult = new EnforcementMotTestResult();
        $expectedResult->setMotTest(new MotTest());
        $expectedResult->setMotTestInspection(new MotTest());

        $mockRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => self::TEST_NUMBER])
            ->will($this->returnValue($expectedResult));

        $mockEntityManager->expects($this->once())
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementMotTestResult::class)
            ->will($this->returnValue($mockRepository));

        $service = new EnforcementMotTestResultService(
            $mockEntityManager,
            $mockHydrator,
            $mockAuthService,
            $mockMotTestMapper
        );

        $result = $service->getEnforcementMotTestResultData(self::TEST_NUMBER);
        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('testDifferences', $result);
        $this->assertArrayHasKey('motTests', $result);
        $this->assertArrayHasKey('enforcementResult', $result);
        $this->assertInternalType('array', $result['enforcementResult']);
    }

    /**
     * Make sure that we get the correct number of validators.
     */
    public function testGetValidatorsCount()
    {
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $service = new EnforcementMotTestResultService(
            $this->getMockEntityManager(),
            $this->getMockHydrator(),
            $this->getMockAuthorizationService(),
            $mockMotTestMapper
        );

        $validators = $service->getValidators(1, []);

        $this->assertCount(7, $validators);
    }

    /**
     * Make sure that we get the correct type of validators.
     */
    public function testGetValidatorsType()
    {
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $service = new EnforcementMotTestResultService(
            $this->getMockEntityManager(),
            $this->getMockHydrator(),
            $this->getMockAuthorizationService(),
            $mockMotTestMapper
        );

        $validators = $service->getValidators(1, []);

        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckDecisionExistsForScore::class,
            $validators[0]
        );
        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckCategoryExistsForScore::class,
            $validators[1]
        );

        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckDecisionsForCategoryNotApplicable::class,
            $validators[2]
        );
        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckCategoryPleaseSelectForDefect::class,
            $validators[3]
        );
        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckCategoryAllowedForDefectNotApplicable::class,
            $validators[4]
        );
        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckScoreForDefectNotApplicable::class,
            $validators[5]
        );
        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckJustificationForScoreDisregard::class,
            $validators[6]
        );
    }

    /**
     * Make sure that we get the correct number of validators.
     */
    public function testGetResultValidatorsCount()
    {
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $service = new EnforcementMotTestResultService(
            $this->getMockEntityManager(),
            $this->getMockHydrator(),
            $this->getMockAuthorizationService(),
            $mockMotTestMapper
        );

        $validators = $service->getResultValidators([], 1);

        $this->assertCount(3, $validators);
    }

    /**
     * Make sure that we get the correct type of validators.
     */
    public function testGetResultValidatorsType()
    {
        $mockMotTestMapper = $this->getMockMotTestMapper();

        $service = new EnforcementMotTestResultService(
            $this->getMockEntityManager(),
            $this->getMockHydrator(),
            $this->getMockAuthorizationService(),
            $mockMotTestMapper
        );

        $validators = $service->getResultValidators([], 1);

        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckAdvisoryWarningHasJustificationAgainstScore::class,
            $validators[0]
        );
        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckDisciplinaryActionHasJustificationAgainstScore::class,
            $validators[1]
        );
        $this->assertInstanceOf(
            \DvsaMotApi\Service\RfrValidator\CheckNoFurtherActionHasJustificationAgainstScore::class,
            $validators[2]
        );
    }

    public function testCreateEnforcementMotTestResult()
    {
        $mockHydrator = $this->getMockHydrator();
        $mockRepository = $this->getMockRepository();
        $mockEntityManager = $this->getMockEntityManager();
        $mockAuthService = $this->getMockAuthorizationService();
        $mockMotTestMapper = $this->getMockMotTestMapper();
        $mockMotTestRepo = $this->getMockRepository(MotTestRepository::class);

        $service = new EnforcementMotTestResultService(
            $mockEntityManager,
            $mockHydrator,
            $mockAuthService,
            $mockMotTestMapper
        );

        $expectedData = $this->getExpectedData();

        $this->setMockRepositoryExpects($mockRepository, $expectedData);
        $this->setMockEntityManagerExpects($mockEntityManager, $mockRepository, $mockMotTestRepo);
        $data = $this->getTestCreateData();

        // the MOT test MUST have a value for the original MOT test object
        $motTestResult = new MotTest();
        $motTestResult->setMotTestIdOriginal(new MotTest());

        $mockMotTestRepo
            ->expects($this->once())
            ->method('getMotTestByNumber')
            ->will($this->returnValue($motTestResult));

        $result = $service->createEnforcementMotTestResult($data, self::VALID_USER);

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('id', $result);
    }

    /**
     * @param $mockRepository
     * @param $expectedData
     */
    protected function setMockRepositoryExpects(&$mockRepository, $expectedData)
    {
        $mockRepository->expects($this->at(0))
            ->method('findBy')
            ->with(['id' => [2055, 2056]])
            ->will($this->returnValue($expectedData['mappedRfrs']));

        $mockRepository
            ->expects($this->at(1))
            ->method('findAll')
            ->will($this->returnValue($expectedData['scores']));

        $mockRepository
            ->expects($this->at(2))
            ->method('findAll')
            ->will($this->returnValue($expectedData['decisions']));

        $mockRepository
            ->expects($this->at(3))
            ->method('findAll')
            ->will($this->returnValue($expectedData['categories']));

        $mockRepository
            ->expects($this->at(4))
            ->method('findBy')
            ->with(['id' => [2055, 2056]])
            ->will($this->returnValue($expectedData['mappedRfrs']));

        $mockRepository
            ->expects($this->at(5))
            ->method('findOneBy')
            ->with(['username' => self::VALID_USER])
            ->will($this->returnValue(new Person()));

        $mockRepository
            ->expects($this->at(6))
            ->method('find')
            ->with(1)
            ->will($this->returnValue(new EnforcementDecisionOutcome()));

        $mockRepository
            ->expects($this->at(7))
            ->method('findOneBy')
            ->with(['rfrId' => 42])
            ->will($this->returnValue(new ReasonForRejection()));

        $mockRepository
            ->expects($this->at(8))
            ->method('findOneBy')
            ->with(['rfrId' => 42])
            ->will($this->returnValue(new ReasonForRejection()));
    }

    /**
     * @param $mockEntityManager
     * @param $mockRepository
     * @param $mockMotTestRepo
     */
    protected function setMockEntityManagerExpects(&$mockEntityManager, $mockRepository, $mockMotTestRepo)
    {
        $mockEntityManager->expects($this->at(0))
            ->method('getRepository')
            ->with(MotTestReasonForRejection::class)
            ->will($this->returnValue($mockRepository));

        $mockEntityManager->expects($this->at(1))
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementDecisionScore::class)
            ->will($this->returnValue($mockRepository));

        $mockEntityManager->expects($this->at(2))
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementDecision::class)
            ->will($this->returnValue($mockRepository));

        $mockEntityManager->expects($this->at(3))
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementDecisionCategory::class)
            ->will($this->returnValue($mockRepository));

        $mockEntityManager->expects($this->at(4))
            ->method('getRepository')
            ->with(MotTestReasonForRejection::class)
            ->will($this->returnValue($mockRepository));

        $mockEntityManager->expects($this->at(5))
            ->method('getRepository')
            ->with(MotTest::class)
            ->will($this->returnValue($mockMotTestRepo));

        $mockEntityManager->expects($this->at(6))
            ->method('getRepository')
            ->with(Person::class)
            ->will($this->returnValue($mockRepository));

        $mockEntityManager->expects($this->at(7))
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\EnforcementDecisionOutcome::class)
            ->will($this->returnValue($mockRepository));

        // sequence 8 is the persist() for the result - no need to mock

        $mockEntityManager->expects($this->at(9))
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\ReasonForRejection::class)
            ->will($this->returnValue($mockRepository));

        // sequence 9 is the persist() for difference row

        $mockEntityManager->expects($this->at(11))
            ->method('getRepository')
            ->with(\DvsaEntities\Entity\ReasonForRejection::class)
            ->will($this->returnValue($mockRepository));

        // sequence 12 is the persist() for the difference row
    }
}
