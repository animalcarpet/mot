<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace EventTest\Factory\Service;

use DvsaCommonTest\TestUtils\XMock;
use Event\Factory\Service\EventStepServiceFactory;
use Event\Service\EventSessionService;
use Event\Service\EventStepService;
use Event\Step\CompletedStep;
use Event\Step\OutcomeStep;
use Event\Step\RecordStep;
use Event\Step\SummaryStep;
use Zend\ServiceManager\ServiceManager;

/**
 * Class EventStepServiceFactoryTest.
 *
 * @group event
 */
class EventStepServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new EventStepServiceFactory();

        $serviceManager = new ServiceManager();
        $serviceManager->setService(EventSessionService::class, XMock::of(EventSessionService::class));

        $factoryResult = $factory->createService($serviceManager);

        $this->assertInstanceOf(EventStepService::class, $factoryResult);
    }

    public function testCreateSteps()
    {
        $factory = new EventStepServiceFactory();
        $session = XMock::of(EventSessionService::class);
        $steps = $factory->createSteps($session);

        $this->assertInstanceOf(RecordStep::class, $steps[0]);
        $this->assertInstanceOf(OutcomeStep::class, $steps[1]);
        $this->assertInstanceOf(SummaryStep::class, $steps[2]);
        $this->assertInstanceOf(CompletedStep::class, $steps[3]);
    }
}
