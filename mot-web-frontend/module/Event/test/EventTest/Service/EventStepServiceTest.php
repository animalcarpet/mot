<?php

namespace EventTest\Service;

use DvsaCommonTest\TestUtils\XMock;
use Event\Service\EventStepService;
use Event\Step\RecordStep;

/**
 * Class EventStepServiceTest.
 *
 * @group event
 */
class EventStepServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $service = new EventStepService([]);
        $this->assertInstanceOf(EventStepService::class, $service);
    }

    public function testInjectParamsIntoSteps()
    {
        $step = XMock::of(RecordStep::class, ['getId']);

        $service = new EventStepService([$step]);
        $service->injectParamsIntoSteps(__METHOD__, 101);

        $this->assertEquals(__METHOD__, $step->getEntityType());
        $this->assertEquals(101, $step->getEntityId());
    }
}
