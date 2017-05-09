<?php

namespace Core\Service;

use Core\Step\Step;
use DvsaCommonTest\TestUtils\XMock;

/**
 * Class StepServiceTest.
 *
 * @group step
 */
class StepServiceTest extends \PHPUnit_Framework_TestCase
{
    const FIRST = 'first';

    /**
     * @var StepService
     */
    private $stepService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    private $stepInterfaceStackMock;

    /**
     * @var array
     */
    private $data = [
        self::FIRST,
        'second',
        'third',
    ];

    /**
     * Setup function for Service.
     */
    public function setUp()
    {
        $this->stepService = new StepService();

        foreach ($this->data as $value) {
            $mock = $this->createStepMock($value);
            $this->stepInterfaceStackMock[] = $mock;

            $this->stepService->add($mock);
        }
    }

    /**
     * Ensure that getRoutes returns an array of routes in key=>value format.
     *
     * @group step
     *
     * @throws \Exception
     */
    public function testGetRoutes()
    {
        $stepOne = XMock::of(Step::class);
        $stepTwo = XMock::of(Step::class);

        $stepOne->expects($this->any())->method('getId')->willReturn('ONE');
        $stepOne->expects($this->any())->method('route')->willReturn('example/one');
        $stepTwo->expects($this->any())->method('getId')->willReturn('TWO');
        $stepTwo->expects($this->any())->method('route')->willReturn('example/two');

        $this->stepService = new StepService();
        $this->stepService->add($stepOne);
        $this->stepService->add($stepTwo);

        $this->assertEquals(
            [
                'ONE' => 'example/one',
                'TWO' => 'example/two',
            ],
            $this->stepService->getRoutes()
        );
    }

    public function testConstructor()
    {
        $stepService = new StepService($this->stepInterfaceStackMock);
        $this->assertInstanceOf(StepService::class, $stepService);

        $expected = current($this->stepInterfaceStackMock);
        $actual = $stepService->current();

        $this->assertSame($expected, $actual);
    }

    public function testNext()
    {
        // Testing to see if the pointer has been incremented by next
        $expected = next($this->stepInterfaceStackMock);

        $actual = $this->stepService->next();
        $this->assertInstanceOf(Step::class, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testNext_False()
    {
        $this->stepService->next();
        $this->stepService->next();
        $actual = $this->stepService->next();
        $this->assertFalse($actual);
    }

    public function testPrevious()
    {
        $this->stepService->next();
        $actual = $this->stepService->previous();
        $expected = $this->stepInterfaceStackMock[0];
        $this->assertInstanceOf(Step::class, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testPrevious_False()
    {
        $this->assertFalse($this->stepService->previous());
    }

    public function testCurrent()
    {
        $actual = $this->stepService->current();
        $expected = current($this->stepInterfaceStackMock);

        $this->assertSame($expected, $actual);
    }

    public function testRewind()
    {
        $this->stepService->rewind();
        $actualClass = $this->stepService->current();

        $this->assertSame($this->stepInterfaceStackMock[0], $actualClass);
    }

    public function testGetById()
    {
        $actual = $this->stepService->getById(self::FIRST);
        $expected = $this->stepInterfaceStackMock[0];

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetById_Exception()
    {
        $this->stepService->getById('doesNotExist');
    }

    public function testAdd()
    {
        $mockStep = $this->createStepMock(__METHOD__);
        $actual = $this->stepService->add($mockStep);

        $expected = 3;

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     */
    public function testAdd_Exception()
    {
        $mockStep = $this->createStepMock(self::FIRST);
        $this->stepService->add($mockStep);
    }

    public function testFirst()
    {
        $actual = $this->stepService->first();
        $expected = $this->stepInterfaceStackMock[0];
        $this->assertInstanceOf(Step::class, $actual);
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests first when no steps have been added.
     *
     * @expectedException \Exception
     */
    public function testFirst_Exception()
    {
        $stepService = new StepService();
        $stepService->first();
    }

    public function testLast()
    {
        $actual = $this->stepService->last();
        $index = count($this->stepInterfaceStackMock) - 1;
        $expected = $this->stepInterfaceStackMock[$index];
        $this->assertInstanceOf(Step::class, $actual);
        $this->assertSame($expected, $actual);
    }

    public function testIsLast()
    {
        $this->assertFalse($this->stepService->isLast());
        $this->stepService->next();
        $this->stepService->next();
        $this->assertTrue($this->stepService->isLast());
    }

    public function testSetActiveById()
    {
        $this->stepService->next();
        $this->stepService->setActiveById(self::FIRST);

        $actual = $this->stepService->current();
        $expected = $this->stepService->getById(self::FIRST);

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     */
    public function testSetActiveById_Exception()
    {
        $this->stepService->setActiveById('notAStep');
    }

    public function testSetActiveByStep()
    {
        $this->stepService->next();

        $stepMock = $this->createStepMock(self::FIRST);

        $this->stepService->setActiveByStep($stepMock);

        $actual = $this->stepService->current();
        $expected = $this->stepService->getById(self::FIRST);

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Exception
     */
    public function testSetActiveByStep_Exception()
    {
        $stepMock = $this->createStepMock('iDoNotExist');
        $this->stepService->setActiveByStep($stepMock);
    }

    public function testCount()
    {
        $numberOfSteps = $this->stepService->count();
        $this->assertSame(3, $numberOfSteps);
    }

    public function testGetPosition()
    {
        $this->stepService->next();
        $currentStepNumber = $this->stepService->getPosition();
        $this->assertSame(2, $currentStepNumber);
    }

    /**
     * Tests current when no steps have been added.
     *
     * @expectedException \Exception
     */
    public function testCurrent_Exception()
    {
        $stepService = new StepService();
        $stepService->current();
    }

    public function testGetIterator()
    {
        $count = 0;
        foreach ($this->stepService as $step) {
            $expected = $this->stepInterfaceStackMock[$count];
            $this->assertSame($expected, $step);
            ++$count;
        }
    }

    /**
     * @param string $value
     *
     * @throws \Exception
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createStepMock($value)
    {
        $mock = XMock::of(Step::class, ['getId']);
        $mock->expects($this->any())
            ->method('getId')
            ->willReturn($value);

        return $mock;
    }
}
