<?php

namespace DvsaMotTestTest\Adapter;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Adapter\MotTestServiceResponseAdapter;
use PHPUnit_Framework_MockObject_MockObject;

class MotTestServiceResponseAdapterTest extends \PHPUnit_Framework_TestCase
{
    /** @var MotTest | PHPUnit_Framework_MockObject_MockObject */
    private $mockMotTest;

    protected function setUp()
    {
        $this->mockMotTest = XMock::of(MotTest::class);
    }

    public function testGetIssuedDateWithValidIssueDate()
    {
        $expectedIssuedDate = '2017-01-01';
        $this->mockMotTest
            ->expects($this->any())
            ->method('getIssuedDate')
            ->willReturn($expectedIssuedDate);

        $responseAdapter = new MotTestServiceResponseAdapter($this->mockMotTest);
        $actualIssuedDate = $responseAdapter->getIssuedDate();

        $this->assertEquals($expectedIssuedDate, $actualIssuedDate);
    }

    public function testGetIssuedDateWithNullIssueDate()
    {
        $expectedIssuedDate = '2017-01-01';
        $this->mockMotTest
            ->expects($this->any())
            ->method('getIssuedDate')
            ->willReturn(null);
        $this->mockMotTest
            ->expects($this->any())
            ->method('getCompletedDate')
            ->willReturn($expectedIssuedDate);

        $responseAdapter = new MotTestServiceResponseAdapter($this->mockMotTest);
        $actualIssuedDate = $responseAdapter->getIssuedDate();

        $this->assertEquals($expectedIssuedDate, $actualIssuedDate);
    }

    public function testGetIssuedDateWithNullIssueDateAndNullCompletedDate()
    {
        $expectedIssuedDate = '2017-01-01';
        $this->mockMotTest
            ->expects($this->any())
            ->method('getIssuedDate')
            ->willReturn(null);
        $this->mockMotTest
            ->expects($this->any())
            ->method('getCompletedDate')
            ->willReturn(null);
        $this->mockMotTest
            ->expects($this->any())
            ->method('getStartedDate')
            ->willReturn($expectedIssuedDate);

        $responseAdapter = new MotTestServiceResponseAdapter($this->mockMotTest);
        $actualIssuedDate = $responseAdapter->getIssuedDate();

        $this->assertEquals($expectedIssuedDate, $actualIssuedDate);
    }

    public function testGetReasonsForRejectionExcludingMarkedAsRepairedWorksForFailures()
    {
        $reasonsForRejection = new \stdClass();
        $reasonsForRejection->FAIL = [
            0 => $this->getDefectByTypeAndMarkAsRepairedValue('FAIL', false),
            1 => $this->getDefectByTypeAndMarkAsRepairedValue('FAIL', false),
            2 => $this->getDefectByTypeAndMarkAsRepairedValue('FAIL', true),
            3 => $this->getDefectByTypeAndMarkAsRepairedValue('FAIL', true),
        ];
        $this->mockMotTest
            ->expects($this->any())
            ->method('getReasonsForRejection')
            ->willReturn($reasonsForRejection);

        $responseAdapter = new MotTestServiceResponseAdapter($this->mockMotTest);
        $result = $responseAdapter->getReasonsForRejectionExcludingRepairedDefects();

        $this->assertEquals(2, count($result->FAIL), "Expected 2 FAIL defects after 'Marked as Repaired' defects were removed");
    }

    public function testGetReasonsForRejectionExcludingMarkedAsRepairedWorksForPrs()
    {
        $reasonsForRejection = new \stdClass();
        $reasonsForRejection->PRS = [
            0 => $this->getDefectByTypeAndMarkAsRepairedValue('PRS', false),
            1 => $this->getDefectByTypeAndMarkAsRepairedValue('PRS', false),
            2 => $this->getDefectByTypeAndMarkAsRepairedValue('PRS', false),
            3 => $this->getDefectByTypeAndMarkAsRepairedValue('PRS', false),
        ];
        $this->mockMotTest
            ->expects($this->any())
            ->method('getReasonsForRejection')
            ->willReturn($reasonsForRejection);

        $responseAdapter = new MotTestServiceResponseAdapter($this->mockMotTest);
        $result = $responseAdapter->getReasonsForRejectionExcludingRepairedDefects();

        $this->assertEquals(4, count($result->PRS), "Expected 4 PRS defects after 'Marked as Repaired' defects were removed");
    }

    public function testGetReasonsForRejectionExcludingMarkedAsRepairedWorksForAdvisory()
    {
        $reasonsForRejection = new \stdClass();
        $reasonsForRejection->PRS = [
            0 => $this->getDefectByTypeAndMarkAsRepairedValue('ADVISORY', true),
            1 => $this->getDefectByTypeAndMarkAsRepairedValue('ADVISORY', true),
            2 => $this->getDefectByTypeAndMarkAsRepairedValue('ADVISORY', true),
            3 => $this->getDefectByTypeAndMarkAsRepairedValue('ADVISORY', true),
        ];
        $this->mockMotTest
            ->expects($this->any())
            ->method('getReasonsForRejection')
            ->willReturn($reasonsForRejection);

        $responseAdapter = new MotTestServiceResponseAdapter($this->mockMotTest);
        $result = $responseAdapter->getReasonsForRejectionExcludingRepairedDefects();

        $this->assertEquals(0, count($result->ADVISORY), "Expected 0 ADVISORY defects after 'Marked as Repaired' defects were removed");
    }

    private function getDefectByTypeAndMarkAsRepairedValue($type, $markAsRepaired)
    {
        $defect = new \stdClass();
        $defect->type = $type;
        $defect->markedAsRepaired = $markAsRepaired;

        return $defect;
    }
}
