<?php

namespace DvsaMotApiTest\Service;

use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonTest\TestUtils\MockHandler;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\OdometerReading;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\TestItemSelector;
use DvsaEntitiesTest\Entity\MotTestReasonForRejectionTest;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\TestItemSelectorService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Test for check class MotTestReasonForRejectionService.
 */
class MotTestReasonForRejectionServiceTest extends AbstractMotTestServiceTest
{
    const MOT_TEST_NUMBER = "123456789012";

    /**
     * @var TestItemSelectorService|MockObj
     */
    private $mockTestItemSelectorService;

    /**
     * @var ApiPerformMotTestAssertion
     */
    private $mockPerformMotTestAssertion;

    public function setUp()
    {
        unset(
            $this->mockEntityManager,
            $this->mockAuthService,
            $this->mockMotTestValidator,
            $this->mockTestItemSelectorService,
            $this->mockPerformMotTestAssertion
        );

        parent::setUp();
    }

    public function testAddReasonForRejectionOk()
    {
        $data = [
            'rfrId' => 3,
            'type' => 'FAIL',
            'locationLateral' => 'nearside',
            'locationLongitudinal' => 'front',
            'locationVertical' => 'top',
            'comment' => 'comment goes here',
            'failureDangerous' => false,
        ];

        $failureText = 'adversely affected by the operation of another lamp';
        $ref = '1.2.1f';
        $selectorName = 'Rear Stop lamp';

        $failureTextCy = 'adversely affected by the operation of another lamp (W)';
        $selectorNameCy = 'Rear Stop lamp (W)';

        $this->addReasonForRejectionOk(
            $data,
            $failureText,
            $ref,
            $selectorName,
            $failureTextCy,
            $selectorNameCy,
            true
        );
    }

    public function testAddManualAdvisoryOk()
    {
        $data = [
            'rfrId' => 0,
            'type' => 'ADVISORY',
            'locationLateral' => 'nearside',
            'locationLongitudinal' => 'front',
            'locationVertical' => 'top',
            'comment' => 'comment goes here',
            'failureDangerous' => false,
        ];

        $failureText = '';
        $ref = '';
        $selectorName = 'Manual Advisory';

        $failureTextCy = '';
        $selectorNameCy = 'Cynghori Llawlyfr';

        $this->addReasonForRejectionOk(
            $data,
            $failureText,
            $ref,
            $selectorName,
            $failureTextCy,
            $selectorNameCy,
            false
        );
    }

    public function testAddReasonForRejectionThrowsRequiredFieldExceptionForMissingFields()
    {
        $data = [];

        $motTest = self::getTestMotTestEntity();
        $motTest
            ->setId(1);

        $this->prepareMocks();
        $service = $this->createService();

        try {
            $service->addReasonForRejection($motTest, $data);
        } catch (RequiredFieldException $expected) {
            $errors = $expected->getErrors();
            $this->assertEquals(2, count($errors));
            $this->assertEquals('rfrId is required', $errors[0]['message']);
            $this->assertEquals('type is required', $errors[1]['message']);
            $this->assertEquals('rfrId', $errors[0]['field']);
            $this->assertEquals('type', $errors[1]['field']);

            return null;
        }

        $this->fail('An expected exception has not been raised.');
    }

    public function testAddReasonForRejectionThrowsNotFoundExceptionForNullReasonForRejection()
    {
        $rfrId = 666;

        $data = [
            'rfrId' => $rfrId,
            'type' => 'FAIL',
        ];

        $motTest = self::getTestMotTestEntity();
        $motTest->setId(1);

        $this->prepareMocks();

        $this->mockEntityManager->expects($this->once())
            ->method('find')
            ->will($this->returnValue(null));

        $this->setExpectedException(
            NotFoundException::class,
            'Reason for Rejection ' . $rfrId . ' not found'
        );

        $service = $this->createService();
        $service->addReasonForRejection($motTest, $data);
    }

    //  FIXME: add editReasonForRejection tests

    public function testDeleteReasonForRejectionByIdThrowsNotFoundExceptionForInvalidMotTest()
    {
        $rfrId = 1;

        $this->prepareMocks();

        $mockEntityManager = new MockHandler($this->mockEntityManager, $this, 0);
        $mockEntityManager->find()->will($this->returnValue(null));

        $this->setExpectedException(
            NotFoundException::class,
            'Reason for Rejection entry not found'
        );

        $service = $this->createService();
        $service->deleteReasonForRejectionById(self::MOT_TEST_NUMBER, $rfrId);
    }

    public function testDeleteReasonForRejectionByIdThrowsNotFoundExceptionForInvalidRfr()
    {
        $motTestRfrId = 666;

        $this->prepareMocks();

        $motTest = (new MotTest())->setMotTestType((new MotTestType())->setCode(MotTestTypeCode::NORMAL_TEST));
        $motRfrAdvisory = MotTestReasonForRejectionTest::getTestMotTestReasonForRejection('ADVISORY');
        $motRfrAdvisory->setMotTest($motTest);

        $mockEntityManager = new MockHandler($this->mockEntityManager, $this, 0);
        $mockEntityManager->find()->will($this->returnValue($motRfrAdvisory));

        $this->setExpectedException(
            NotFoundException::class,
            'Match for Reason for Rejection on Selected Mot Test not found'
        );

        $service = $this->createService();
        $service->deleteReasonForRejectionById(self::MOT_TEST_NUMBER, $motTestRfrId);
    }

    public function testDeleteReasonForRejectionByIdThrowsBadRequestExceptionForInvalidRfr()
    {
        $motTestRfrId = 1;

        $this->prepareMocks();

        $motTest = self::getTestMotTestEntity();
        XMock::mockClassField($motTest, 'number', self::MOT_TEST_NUMBER);

        $motRfrAdvisory = MotTestReasonForRejectionTest::getTestMotTestReasonForRejection('ADVISORY');
        $motRfrAdvisory
            ->setMotTest($motTest)
            ->setGenerated(true);

        $mockEntityManager = new MockHandler($this->mockEntityManager, $this, 0);
        $mockEntityManager->find()->will($this->returnValue($motRfrAdvisory));

        $this->setExpectedException(
            BadRequestException::class,
            'This Reason for Rejection type cannot be deleted'
        );

        $service = $this->createService();
        $service->deleteReasonForRejectionById(self::MOT_TEST_NUMBER, $motTestRfrId);
    }

    public function testDeleteReasonForRejectionByIdOk()
    {
        $motTestNumber = 2001;
        $rfrId = 754;

        $motTest = self::getTestMotTestEntity();
        XMock::mockClassField($motTest, 'number', (string) $motTestNumber);

        $motRfrFail = MotTestReasonForRejectionTest::getTestMotTestReasonForRejection('FAIL');
        $motRfrFail->setMotTest($motTest);

        $this->prepareMocks();

        $this->mockEntityManager->expects($this->at(0))
            ->method('find')
            ->will($this->returnValue($motRfrFail));

        $this->mockEntityManager->expects($this->at(1))
            ->method('remove')
            ->will($this->returnValue(null));

        $this->mockEntityManager->expects($this->at(2))
            ->method('flush');

        $service = $this->createService();
        $service->deleteReasonForRejectionById($motTestNumber, $rfrId);
    }

    private function addReasonForRejectionOk(
        $data,
        $failureText,
        $inspectionManualReference,
        $selectorName,
        $failureTextCy,
        $selectorNameCy,
        $shouldSearchForRfr
    ) {
        $motTestId = 1;

        $failureDangerous = $data['failureDangerous'] === true;

        $motTest = self::getTestMotTestEntity();
        $motTest
            ->setId($motTestId)
            ->setOdometerReading(OdometerReading::create()->setUnit(OdometerUnit::KILOMETERS));

        $this->prepareMocks();

        $mockEntityManagerHandler = new MockHandler($this->mockEntityManager, $this);

        if ($shouldSearchForRfr) {
            $testItemSelector = new TestItemSelector();
            $testItemSelector
                ->setId(1)
                ->setName($selectorName)
                ->setNameCy($selectorNameCy);

            $reasonForRejection = new ReasonForRejection();
            $reasonForRejection
                ->setRfrId($data['rfrId'])
                ->setTestItemSelector($testItemSelector)
                ->setSectionTestItemSelector((new TestItemSelector())->setSectionTestItemSelectorId(3));

            $this->mockTestItemSelectorService->expects($this->once())
                ->method('getReasonForRejectionById')
                ->with($data['rfrId'])
                ->will($this->returnValue($reasonForRejection));

            $mockEntityManagerHandler->next('find')
                ->with(
                    ReasonForRejection::class,
                    ['rfrId' => $data['rfrId']]
                )
                ->will($this->returnValue($reasonForRejection));
        } else {
            $reasonForRejection = null;
        }
        $this->setupMockForSingleCall($this->mockMotTestValidator, 'validateMotTestReasonForRejection', true);

        $mockEntityManagerHandler->next('persist')
            ->with(
                $this->logicalAnd(
                    $this->isInstanceOf(MotTestReasonForRejection::class),
                    $this->attributeEqualTo('motTest', $motTest),
                    $this->attributeEqualTo('reasonForRejection', $reasonForRejection),
                    $this->attributeEqualTo('type', $data['type']),
                    $this->attributeEqualTo('locationLateral', $data['locationLateral']),
                    $this->attributeEqualTo('locationLongitudinal', $data['locationLongitudinal']),
                    $this->attributeEqualTo('locationVertical', $data['locationVertical']),
                    $this->attributeEqualTo('comment', $data['comment']),
                    $this->attributeEqualTo('failureDangerous', $failureDangerous)
                )
            );

        $mockEntityManagerHandler->callNext('flush');

        $service = $this->createService();

        $service->addReasonForRejection($motTest, $data);
    }

    private function prepareMocks()
    {
        $this->mockEntityManager = $this->getMockEntityManager();
        $this->mockAuthService = $this->getMockAuthorizationService();
        $this->mockMotTestValidator = $this->getMockTestValidator();
        $this->mockTestItemSelectorService = XMock::of(TestItemSelectorService::class);
        $this->mockPerformMotTestAssertion = XMock::of(ApiPerformMotTestAssertion::class);
    }

    private function createService()
    {
        return new MotTestReasonForRejectionService(
            $this->mockEntityManager,
            $this->mockAuthService,
            $this->mockMotTestValidator,
            $this->mockTestItemSelectorService,
            $this->mockPerformMotTestAssertion,
            $this->createFeatureToggles(false)
        );
    }

    /**
     * @param $testResultEntryImprovements
     *
     * @return MockObj|FeatureToggles
     */
    private function createFeatureToggles($testResultEntryImprovements)
    {
        $featureToggles = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();

        $featureToggles
            ->method('isEnabled')
            ->with('test_result_entry_improvements')
            ->will($this->returnValue($testResultEntryImprovements));

        return $featureToggles;
    }
}
