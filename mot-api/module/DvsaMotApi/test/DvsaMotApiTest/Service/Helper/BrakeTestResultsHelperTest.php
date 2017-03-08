<?php

namespace DvsaMotApiTest\Service\Helper;

use Doctrine\ORM\EntityManager;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\MotTest;
use DvsaMotApi\Service\Helper\BrakeTestResultsHelper;
use PHPUnit_Framework_TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class BrakeTestResultsHelperTest.
 */
class BrakeTestResultsHelperTest extends PHPUnit_Framework_TestCase
{
    /** @var EntityManager|MockObj */
    private $mockEntityManager;

    /** @var BrakeTestResultClass12|MockObj */
    private $mockBrakeTestResultClass12;

    /** @var BrakeTestResultClass3AndAbove|MockObj */
    private $mockBrakeTestResultClass3AndAbove;

    protected function setUp()
    {
        $this->mockBrakeTestResultClass12 = XMock::of(BrakeTestResultClass12::class);
        $this->mockBrakeTestResultClass3AndAbove = XMock::of(BrakeTestResultClass3AndAbove::class);
        $this->mockEntityManager = XMock::of(EntityManager::class);
    }

    public function testDeleteAllBrakeTestResults()
    {
        $motTest = $this->createMotTest();
        $numberOfBrakeTestResultsToDelete = 2;

        $this->setUpBrakeTestResultsClass12();
        $this->setUpBrakeTestResultsClass3AndAbove();
        $this->setUpMockEntityManager();
        $this->mockEntityManager
            ->expects($invocationRecorder = $this->exactly($numberOfBrakeTestResultsToDelete))
            ->method('remove');

        $brakeTestsHelper = new BrakeTestResultsHelper($this->mockEntityManager);
        $brakeTestsHelper->deleteAllBrakeTestResults($motTest);
        $countOfInvocationsOfRemoveMethod = $invocationRecorder->getInvocationCount();

        $this->assertSame($numberOfBrakeTestResultsToDelete, $countOfInvocationsOfRemoveMethod);
    }

    private function setUpMockEntityManager()
    {
        $this->mockEntityManager = $this
            ->getMockBuilder(EntityManager::class)
            ->setMethods(array('getRepository', 'remove', 'flush'))
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockEntityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->will(
                $this->onConsecutiveCalls(
                    $this->mockBrakeTestResultClass12,
                    $this->mockBrakeTestResultClass3AndAbove
                )
            );
    }

    /**
     * @return MotTest
     */
    private function createMotTest()
    {
        $motTest = new MotTest();
        $motTest->setId(1);

        return $motTest;
    }

    private function setUpBrakeTestResultsClass12()
    {
        $result = [
            (new BrakeTestResultClass12())
                ->setControl1EffortFront(10)
                ->setControl1EffortRear(10)
                ->setControl2EffortFront(10)
                ->setControl2EffortRear(10)
        ];

        $this->mockBrakeTestResultClass12 = $this
            ->getMockBuilder(BrakeTestResultClass12::class)
            ->disableOriginalConstructor()
            ->setMethods(array('findBy'))
            ->getMock();

        $this->mockBrakeTestResultClass12
            ->expects($this->exactly(1))
            ->method('findBy')
            ->will($this->returnValue($result));
    }

    private function setUpBrakeTestResultsClass3AndAbove()
    {
        $result = [
            (new BrakeTestResultClass3AndAbove())
                ->setParkingBrakeEffortOffside(10)
                ->setParkingBrakeEffortNearside(10)
        ];

        $this->mockBrakeTestResultClass3AndAbove = $this
            ->getMockBuilder(BrakeTestResultClass3AndAbove::class)
            ->disableOriginalConstructor()
            ->setMethods(array('findBy'))
            ->getMock();

        $this->mockBrakeTestResultClass3AndAbove
            ->expects($this->exactly(1))
            ->method('findBy')
            ->will($this->returnValue($result));
    }
}
